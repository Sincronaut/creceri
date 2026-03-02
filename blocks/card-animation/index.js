/* Guides block – Editor (no JSX) */
(function (wp) {
  if (!wp || !wp.blocks) return;

  const { registerBlockType } = wp.blocks;
  const el = wp.element.createElement;
  const { Fragment, useMemo } = wp.element;
  const { __ } = wp.i18n || { __: (s) => s };

  const be = wp.blockEditor || wp.editor;
  const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = be;
  const {
    PanelBody, TextControl, TextareaControl, ToggleControl, Button, Notice
  } = wp.components;

  const makeItem = () => ({
    title: '', text: '', url: '#', active: false, bgColor: '', image: { src: '', alt: '' }
  });

  function ItemEditor({ item, index, onChange, onRemove, onMoveUp, onMoveDown }) {
    const img = item.image || {};
    return el('div', { className: 'gi-item-editor' },
      el('div', { className: 'gi-item-head' },
        el('strong', null, `Card #${index + 1}`),
        el('div', null,
          el(Button, { onClick: onMoveUp, variant: 'tertiary', size: 'small' }, '↑'),
          el(Button, { onClick: onMoveDown, variant: 'tertiary', size: 'small' }, '↓'),
          el(Button, { onClick: onRemove, variant: 'secondary', size: 'small', isDestructive: true }, __('Remove', 'child'))
        )
      ),
      el(TextControl, { label: __('Title', 'child'), value: item.title || '', onChange: (v) => onChange({ ...item, title: v }) }),
      el(TextareaControl, { label: __('Text', 'child'), value: item.text || '', onChange: (v) => onChange({ ...item, text: v }) }),
      el(TextControl, { label: __('URL', 'child'), value: item.url || '', onChange: (v) => onChange({ ...item, url: v }) }),
      el(TextControl, { label: __('Background color (optional)', 'child'), value: item.bgColor || '', onChange: (v) => onChange({ ...item, bgColor: v }) }),
      el(ToggleControl, { label: __('Active by default', 'child'), checked: !!item.active, onChange: (ch) => onChange({ ...item, active: ch }) }),
      el('div', { className: 'gi-media-row' },
        el('div', { className: 'gi-thumb' },
          img.src ? el('img', { src: img.src, alt: img.alt || '' }) : el('div', { className: 'gi-thumb--empty' }, __('No image', 'child'))
        ),
        el(MediaUploadCheck, null,
          el(MediaUpload, {
            allowedTypes: ['image'],
            onSelect: (m) => {
              const src = m?.url || m?.sizes?.full?.url || '';
              const alt = m?.alt || m?.title || '';
              onChange({ ...item, image: { src, alt } });
            },
            render: ({ open }) => el(Button, { onClick: open, variant: 'secondary' }, img.src ? __('Change image', 'child') : __('Select image', 'child'))
          })
        ),
        img.src && el(Button, { onClick: () => onChange({ ...item, image: { src: '', alt: '' } }), variant: 'tertiary' }, __('Remove', 'child'))
      )
    );
  }

  function Edit(props) {
    const { attributes: A, setAttributes } = props;
    const setA = (patch) => setAttributes({ ...A, ...patch });
    const items = Array.isArray(A.items) ? A.items : [];

    const activeCount = items.reduce((n, it) => n + (it.active ? 1 : 0), 0);
    const needsActiveNotice = items.length > 0 && activeCount === 0;

    const blockProps = useBlockProps({ className: 'guides-editor-block' });

    function updateItem(idx, next) {
      const clone = items.slice();
      clone[idx] = next;
      setA({ items: clone });
    }
    function removeItem(idx) {
      const clone = items.slice();
      clone.splice(idx, 1);
      setA({ items: clone });
    }
    function moveItem(idx, dir) {
      const j = idx + dir;
      if (j < 0 || j >= items.length) return;
      const clone = items.slice();
      const tmp = clone[idx]; clone[idx] = clone[j]; clone[j] = tmp;
      setA({ items: clone });
    }
    function addItem() {
      const clone = items.slice();
      clone.push(makeItem());
      setA({ items: clone });
    }

    return el(Fragment, null,
      el(InspectorControls, null,
        el(PanelBody, { title: __('Section', 'child'), initialOpen: true },
          el(TextControl, { label: __('Title', 'child'), value: A.title || '', onChange: (v) => setA({ title: v }) }),
          el(TextareaControl, { label: __('Intro (HTML allowed: br,strong,em,span,a)', 'child'), value: A.intro || '', onChange: (v) => setA({ intro: v }) }),
          el(TextControl, { label: __('Title ID (optional)', 'child'), value: A.titleId || '', onChange: (v) => setA({ titleId: v }) })
        ),
        el(PanelBody, { title: __('Cards', 'child'), initialOpen: true },
          needsActiveNotice && el(Notice, { status: 'warning', isDismissible: false }, __('No card is marked Active; the first card will be active by default.', 'child')),
          items.map((it, idx) => el(ItemEditor, {
            key: idx,
            item: it,
            index: idx,
            onChange: (next) => updateItem(idx, next),
            onRemove: () => removeItem(idx),
            onMoveUp: () => moveItem(idx, -1),
            onMoveDown: () => moveItem(idx, 1)
          })),
          el(Button, { onClick: addItem, variant: 'primary', style: { marginTop: '8px' } }, __('Add card', 'child'))
        )
      ),

      // Lightweight preview
      el('div', blockProps,
        el('section', { className: 'guides' },
          el('div', { className: 'g-wrap' },
            el('header', { className: 'g-head' },
              el('h2', null, A.title || 'Guides & Insights'),
              !!A.intro && el('p', { dangerouslySetInnerHTML: { __html: A.intro } })
            ),
            el('ul', { className: 'g-rail', role: 'list' },
              (items.length ? items : [makeItem()]).slice(0, 5).map((it, i) =>
                el('li', { key: i, className: 'g-item' + (it.active ? ' active' : '') },
                  el('a', { className: 'g-link', href: '#', onClick: (e) => e.preventDefault() },
                    it.image?.src ?
                      el('img', { className: 'g-media', src: it.image.src, alt: it.image?.alt || '' }) :
                      null,
                    el('div', { className: 'g-overlay' },
                      it.title && el('h3', null, it.title),
                      it.text && el('p', null, it.text)
                    )
                  )
                )
              )
            )
          )
        )
      )
    );
  }

  registerBlockType('child/card-animation', {
    apiVersion: 3,
    edit: Edit,
    save: function () { return null; }
  });
})(window.wp);
