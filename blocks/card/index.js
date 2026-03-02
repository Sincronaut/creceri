/* /blocks/card/index.js */
(function (wp) {
  if (!wp || !wp.blocks) return;

  const el = wp.element.createElement;
  const { registerBlockType } = wp.blocks;
  const { __ } = wp.i18n || { __: (s) => s };
  const be = wp.blockEditor || wp.editor; // compat for older WP
  const {
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
    RichText,
    URLInputButton,
    useBlockProps
  } = be;
  const { PanelBody, TextControl, Button, ToolbarGroup, ToolbarButton, Notice } = wp.components;

  // ---- IMPORTANT: include metadata here so inserter shows even if REST is blocked ----
  const blockMeta = {
    title: __('Card Slider (What’s New)', 'child'),
    description: __('Editable section with title, intro, CTA, and a horizontal card slider.', 'child'),
    category: 'widgets',           // keep it simple/compatible
    icon: 'star-filled',
    keywords: ['card', 'cards', 'slider', 'news', 'what'],
    supports: { html: false, anchor: true, align: ['wide', 'full'], inserter: true },
    attributes: {
      title:   { type: 'string', default: 'What’s New?' },
      intro:   { type: 'string', default: 'Stay up to date with the latest trends, tools, and success stories shaping the digital landscape. Get inspired by fresh perspectives and timely updates from industry leaders.' },
      ctaText: { type: 'string', default: 'Browse All Updates' },
      ctaUrl:  { type: 'string', default: '#all-updates' },
      titleId: { type: 'string' },
      listId:  { type: 'string' },
      cards: {
        type: 'array',
        default: [],
        items: {
          type: 'object',
          properties: {
            title: { type: 'string' },
            text:  { type: 'string' },
            url:   { type: 'string' },
            image: {
              type: 'object',
              properties: {
                src: { type: 'string' },
                alt: { type: 'string' }
              }
            }
          }
        }
      }
    }
  };

  function Edit(props) {
    const { attributes, setAttributes } = props;
    const {
      title   = "What’s New?",
      intro   = "",
      ctaText = "Browse All Updates",
      ctaUrl  = "#all-updates",
      titleId = "",
      listId  = "",
      cards   = []
    } = attributes;

    const blockProps = useBlockProps({ className: 'whats-new-editor' });
    const update = (patch) => setAttributes({ ...attributes, ...patch });

    const updateCard = (i, patch) => {
      const next = Array.isArray(cards) ? cards.slice() : [];
      next[i] = { ...(next[i] || {}), ...patch };
      setAttributes({ cards: next });
    };

    const addCard = () =>
      setAttributes({ cards: [...(cards || []), { title: "", text: "", url: "", image: { src: "", alt: "" } }] });

    const removeCard = (i) => setAttributes({ cards: (cards || []).filter((_, idx) => idx !== i) });

    const moveCard = (from, to) => {
      const list = (cards || []).slice();
      if (to < 0 || to >= list.length) return;
      const [item] = list.splice(from, 1);
      list.splice(to, 0, item);
      setAttributes({ cards: list });
    };

    return el(wp.element.Fragment, null,
      el(InspectorControls, null,
        el(PanelBody, { title: __('Section', 'child'), initialOpen: true },
          el(TextControl, { label: __('Title', 'child'), value: title, onChange: (v) => update({ title: v }) }),
          el(TextControl, { label: __('Intro (plain/limited HTML)', 'child'), help: __('Allowed: a, br, strong, em, span', 'child'), value: intro, onChange: (v) => update({ intro: v }) }),
          el(TextControl, { label: __('CTA Text', 'child'), value: ctaText, onChange: (v) => update({ ctaText: v }) }),
          el(TextControl, { label: __('CTA URL', 'child'), value: ctaUrl, onChange: (v) => update({ ctaUrl: v }) })
        ),
        el(PanelBody, { title: __('Advanced IDs (optional)', 'child'), initialOpen: false },
          el(TextControl, { label: __('Title element ID', 'child'), value: titleId || '', onChange: (v) => update({ titleId: v }) }),
          el(TextControl, { label: __('List element ID', 'child'), value: listId || '', onChange: (v) => update({ listId: v }) })
        )
      ),

      el('div', blockProps,
        el('div', { className: 'wn-editor-head' },
          el('strong', null, blockMeta.title),
          el('div', { className: 'wn-editor-cta' }, __('CTA:', 'child') + ' ' + (ctaText || __('(none)', 'child')) + ' → ' + (ctaUrl || '#'))
        ),

        (!cards || cards.length === 0) && el(Notice, { status: 'info', isDismissible: false },
          __('No cards yet. Click “Add card” below to get started.', 'child')
        ),

        el('ul', { className: 'wn-card-list' },
          (cards || []).map(function (card, index) {
            card = card || {}; const image = card.image || {};
            return el('li', { key: index, className: 'wn-card-item' },
              el('div', { className: 'wn-card-toolbar' },
                el(ToolbarGroup, null,
                  el(ToolbarButton, { icon: 'arrow-up-alt2', label: __('Move up', 'child'), onClick: function(){ moveCard(index, index-1); }, disabled: index === 0 }),
                  el(ToolbarButton, { icon: 'arrow-down-alt2', label: __('Move down', 'child'), onClick: function(){ moveCard(index, index+1); }, disabled: index === (cards.length-1) }),
                  el(ToolbarButton, { icon: 'trash', label: __('Remove', 'child'), onClick: function(){ removeCard(index); }, className: 'is-destructive' })
                )
              ),
              el('div', { className: 'wn-card-fields' },
                el('div', { className: 'wn-card-media' },
                  el(MediaUploadCheck, null,
                    el(MediaUpload, {
                      onSelect: function (media) {
                        var src = (media && (media.url || (media.sizes && media.sizes.full && media.sizes.full.url))) || '';
                        var alt = (media && (media.alt || media.title || '')) || '';
                        updateCard(index, { image: { src: src, alt: alt } });
                      },
                      allowedTypes: ['image'],
                      value: null,
                      render: function (args) {
                        var open = args.open;
                        return el('div', null,
                          image.src
                            ? el('div', { className: 'wn-card-thumb' },
                                el('img', { src: image.src, alt: image.alt || '' }),
                                el(Button, { onClick: open, variant: 'secondary', className: 'wn-card-change' }, __('Change', 'child')),
                                el(Button, { onClick: function(){ updateCard(index, { image: { src: '', alt: '' } }); }, variant: 'tertiary' }, __('Remove', 'child'))
                              )
                            : el(Button, { onClick: open, variant: 'secondary' }, __('Select image', 'child'))
                        );
                      }
                    })
                  ),
                  el(TextControl, { label: __('Image alt', 'child'), value: image.alt || '', onChange: function(v){ updateCard(index, { image: { src: image.src || '', alt: v } }); } })
                ),
                el('div', { className: 'wn-card-text' },
                  el(RichText, { tagName: 'h3', placeholder: __('Card title…', 'child'), value: card.title || '', onChange: function(v){ updateCard(index, { title: v }); }, allowedFormats: ['core/bold','core/italic'] }),
                  el(RichText, { tagName: 'p',  placeholder: __('Short description…', 'child'), value: card.text || '',  onChange: function(v){ updateCard(index, { text: v }); },  allowedFormats: ['core/bold','core/italic','core/link'] }),
                  el('div', { className: 'wn-card-link' },
                    el('label', null, __('Optional link', 'child')),
                    el(URLInputButton, { url: card.url || '', onChange: function(url){ updateCard(index, { url: url || '' }); } })
                  )
                )
              )
            );
          })
        ),

        el('div', { className: 'wn-card-actions' },
          el(Button, { variant: 'primary', onClick: addCard }, __('Add card', 'child'))
        )
      )
    );
  }

  registerBlockType('child/card', {
    ...blockMeta,       // give the editor a title/category/icon even without REST
    edit: Edit,
    save: function () { return null; } // dynamic render via render.php
  });

})(window.wp);
