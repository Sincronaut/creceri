/* /blocks/card-block/index.js */
(function (wp) {
  if (!wp || !wp.blocks) {
    return;
  }

  const { createElement: el, Fragment } = wp.element;
  const { __ } = wp.i18n || { __: (text) => text };
  const { registerBlockType } = wp.blocks;
  const blockEditor = wp.blockEditor || wp.editor;

  if (!blockEditor) {
    return;
  }

  const {
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
    RichText,
    useBlockProps,
  } = blockEditor;

  const {
    PanelBody,
    TextControl,
    TextareaControl,
    ToggleControl,
    Button,
  } = wp.components;

  const createImage = (data) => {
    const base = { src: '', alt: '' };
    if (!data || typeof data !== 'object') {
      return base;
    }
    return {
      src: data.src || '',
      alt: data.alt || '',
    };
  };

  const createItem = (data) => {
    const base = {
      title: '',
      text: '',
      url: '',
      active: false,
      bgColor: '',
      image: createImage(),
    };

    if (!data || typeof data !== 'object') {
      return base;
    }

    return {
      title: data.title || '',
      text: data.text || '',
      url: data.url || '',
      active: !!data.active,
      bgColor: data.bgColor || '',
      image: createImage(data.image),
    };
  };

  registerBlockType('child/card-block', {
    title: __('Card Block', 'child'),
    description: __(
      'Card grid with optional images, links, and a highlighted card.',
      'child'
    ),
    icon: 'screenoptions',
    category: 'widgets',
    supports: {
      anchor: true,
      align: ['wide', 'full'],
      spacing: { margin: true, padding: true },
    },
    edit({ attributes, setAttributes }) {
      const title = attributes.title || '';
      const intro = attributes.intro || '';
      const items = Array.isArray(attributes.items)
        ? attributes.items.map(createItem)
        : [];

      const blockProps = useBlockProps({
        className: 'card-block-editor',
      });

      const setTitle = (value) => setAttributes({ title: value });
      const setIntro = (value) => setAttributes({ intro: value });

      const setItems = (nextItems) => {
        setAttributes({
          items: nextItems.map(createItem),
        });
      };

      const updateItem = (index, changes) => {
        const next = items.map((item, i) => {
          if (i !== index) {
            return item;
          }
          return createItem({
            ...item,
            ...changes,
            image: changes.hasOwnProperty('image')
              ? createImage(changes.image)
              : item.image,
          });
        });
        setItems(next);
      };

      const updateItemImage = (index, media) => {
        if (!media) {
          updateItem(index, { image: createImage() });
          return;
        }
        updateItem(index, {
          image: {
            src: media.url || '',
            alt: media.alt || media.title || '',
          },
        });
      };

      const addItem = () => {
        setItems([...items, createItem()]);
      };

      const removeItem = (index) => {
        const next = items.filter((_, i) => i !== index);
        setItems(next);
      };

      return el(
        Fragment,
        null,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Section', 'child'), initialOpen: true },
            el(TextControl, {
              label: __('Section title', 'child'),
              value: title,
              onChange: setTitle,
              placeholder: __('Add title…', 'child'),
            }),
            el(TextareaControl, {
              label: __('Section intro', 'child'),
              value: intro,
              onChange: setIntro,
              placeholder: __('Add intro…', 'child'),
            })
          ),
          el(
            PanelBody,
            { title: __('Cards', 'child'), initialOpen: true },
            el(
              Button,
              {
                isPrimary: true,
                onClick: addItem,
              },
              __('Add card', 'child')
            )
          )
        ),
        el(
          'section',
          blockProps,
          el(
            'div',
            { className: 'card-block-editor__header' },
            el(RichText, {
              tagName: 'h2',
              className: 'card-block-editor__title',
              value: title,
              onChange: setTitle,
              placeholder: __('Section title…', 'child'),
            }),
            el(RichText, {
              tagName: 'p',
              className: 'card-block-editor__intro',
              value: intro,
              onChange: setIntro,
              placeholder: __('Section intro…', 'child'),
            })
          ),
          el(
            'div',
            { className: 'card-block-editor__grid' },
            items.length === 0
              ? el(
                  'div',
                  { className: 'card-block-editor__empty' },
                  __('Add cards to populate this section.', 'child')
                )
              : items.map((item, index) =>
                  el(
                    'div',
                    { key: index, className: 'card-block-editor__card' },
                    el(
                      'div',
                      { className: 'card-block-editor__card-toolbar' },
                      el(
                        Button,
                        {
                          isDestructive: true,
                          isSmall: true,
                          onClick: () => removeItem(index),
                        },
                        __('Remove', 'child')
                      ),
                      el(ToggleControl, {
                        label: __('Mark as featured', 'child'),
                        checked: !!item.active,
                        onChange: (value) =>
                          updateItem(index, { active: value }),
                      })
                    ),
                    el(
                      'div',
                      { className: 'card-block-editor__image' },
                      item.image && item.image.src
                        ? el('img', {
                            src: item.image.src,
                            alt: item.image.alt || '',
                          })
                        : el(
                            'div',
                            {
                              className:
                                'card-block-editor__image-placeholder',
                            },
                            __('No image selected', 'child')
                          ),
                      el(
                        'div',
                        { className: 'card-block-editor__image-actions' },
                        el(
                          MediaUploadCheck,
                          null,
                          el(MediaUpload, {
                            onSelect: (media) => updateItemImage(index, media),
                            allowedTypes: ['image'],
                            render: ({ open }) =>
                              el(
                                Button,
                                { isSecondary: true, onClick: open },
                                item.image && item.image.src
                                  ? __('Replace image', 'child')
                                  : __('Select image', 'child')
                              ),
                          })
                        ),
                        item.image && item.image.src
                          ? el(
                              Button,
                              {
                                isSecondary: true,
                                isSmall: true,
                                onClick: () =>
                                  updateItem(index, {
                                    image: createImage(),
                                  }),
                              },
                              __('Remove image', 'child')
                            )
                          : null
                      )
                    ),
                    el(TextControl, {
                      label: __('Card title', 'child'),
                      value: item.title,
                      onChange: (value) => updateItem(index, { title: value }),
                    }),
                    el(TextareaControl, {
                      label: __('Card text', 'child'),
                      value: item.text,
                      onChange: (value) => updateItem(index, { text: value }),
                    }),
                    el(TextControl, {
                      label: __('Link URL', 'child'),
                      value: item.url,
                      onChange: (value) => updateItem(index, { url: value }),
                    }),
                    el(TextControl, {
                      label: __('Background modifier', 'child'),
                      help: __(
                        'Optional. Adds a `bg-<value>` class on the front-end.',
                        'child'
                      ),
                      value: item.bgColor,
                      onChange: (value) =>
                        updateItem(index, { bgColor: value }),
                    }),
                    el(TextControl, {
                      label: __('Image alt text', 'child'),
                      value: item.image ? item.image.alt || '' : '',
                      onChange: (value) =>
                        updateItem(index, {
                          image: {
                            src: item.image ? item.image.src || '' : '',
                            alt: value,
                          },
                        }),
                    })
                  )
                )
          ),
          el(
            Button,
            {
              className: 'card-block-editor__add',
              isPrimary: true,
              onClick: addItem,
            },
            __('Add card', 'child')
          )
        )
      );
    },
    save() {
      return null;
    },
  });
})(window.wp);
