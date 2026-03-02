/* blocks/faq/index.js */
(function (wp) {
  if (!wp || !wp.blocks || !wp.element) {
    return;
  }

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var registerBlockType = wp.blocks.registerBlockType;
  var __ = (wp.i18n && wp.i18n.__) ? wp.i18n.__ : function (s) { return s; };
  var be = wp.blockEditor || wp.editor;
  if (!be || !be.useBlockProps) {
    return;
  }

  var useBlockProps = be.useBlockProps;
  var InspectorControls = be.InspectorControls;
  var components = wp.components || {};
  var PanelBody = components.PanelBody;
  var TextControl = components.TextControl;
  var TextareaControl = components.TextareaControl;
  var ToggleControl = components.ToggleControl;
  var Button = components.Button;
  var ToolbarGroup = components.ToolbarGroup;
  var ToolbarButton = components.ToolbarButton;
  var Notice = components.Notice;

  var blockMeta = {
    title: __('FAQ Accordion', 'child'),
    description: __('Collapsible FAQ list with optional intro text.', 'child'),
    category: 'widgets',
    icon: 'editor-help',
    supports: { html: false, inserter: true, align: ['wide', 'full'], anchor: true },
    attributes: {
      title: { type: 'string', default: 'Frequently Asked Questions' },
      intro: { type: 'string', default: '' },
      items: {
        type: 'array',
        default: [],
        items: {
          type: 'object',
          properties: {
            id: { type: 'string' },
            question: { type: 'string' },
            answer: { type: 'string' },
            open: { type: 'boolean', default: false }
          }
        }
      }
    }
  };

  function blankItem() {
    return { question: '', answer: '', open: false, id: '' };
  }

  function Edit(props) {
    var attributes = props.attributes || {};
    var setAttributes = props.setAttributes;
    var items = Array.isArray(attributes.items) ? attributes.items : [];
    var blockProps = useBlockProps({ className: 'faq-editor' });

    function setItems(next) {
      setAttributes({ items: next });
    }

    function updateItem(index, patch) {
      var next = items.slice();
      next[index] = Object.assign({}, blankItem(), next[index] || {}, patch || {});
      setItems(next);
    }

    function removeItem(index) {
      setItems(items.filter(function (_, i) { return i !== index; }));
    }

    function moveItem(from, to) {
      if (to < 0 || to >= items.length) {
        return;
      }
      var next = items.slice();
      var item = next.splice(from, 1)[0];
      next.splice(to, 0, item);
      setItems(next);
    }

    function addItem() {
      var next = items.slice();
      next.push(blankItem());
      setItems(next);
    }

    return el(Fragment, null,
      el(InspectorControls, null,
        el(PanelBody, { title: __('FAQ settings', 'child'), initialOpen: true },
          el('p', null, __('Provide a heading and optional intro, then add each question below.', 'child'))
        )
      ),
      el('div', blockProps,
        el('div', { className: 'faq-editor__header' },
          el('strong', null, blockMeta.title),
          el('p', null, __('Build your FAQ accordion by adding questions and answers.', 'child'))
        ),
        TextControl && el(TextControl, {
          label: __('Heading', 'child'),
          value: attributes.title || '',
          onChange: function (value) { setAttributes({ title: value }); }
        }),
        TextareaControl && el(TextareaControl, {
          label: __('Intro (optional)', 'child'),
          help: __('Plain text or limited inline HTML (strong, em, a).', 'child'),
          value: attributes.intro || '',
          onChange: function (value) { setAttributes({ intro: value }); }
        }),
        (!items || items.length === 0) && Notice && el(Notice, { status: 'info', isDismissible: false },
          __('No FAQ items yet. Add your first question to begin.', 'child')
        ),
        (items || []).map(function (item, index) {
          var data = item || {};
          return el('div', { key: index, className: 'faq-editor__item' },
            el('div', { className: 'faq-editor__item-toolbar' },
              el('span', { className: 'faq-editor__item-label' }, __('Question', 'child') + ' ' + (index + 1)),
              ToolbarGroup && el(ToolbarGroup, null,
                ToolbarButton && el(ToolbarButton, {
                  icon: 'arrow-up-alt2',
                  label: __('Move up', 'child'),
                  onClick: function () { moveItem(index, index - 1); },
                  disabled: index === 0
                }),
                ToolbarButton && el(ToolbarButton, {
                  icon: 'arrow-down-alt2',
                  label: __('Move down', 'child'),
                  onClick: function () { moveItem(index, index + 1); },
                  disabled: index === items.length - 1
                }),
                ToolbarButton && el(ToolbarButton, {
                  icon: 'trash',
                  label: __('Remove item', 'child'),
                  onClick: function () { removeItem(index); },
                  className: 'is-destructive'
                })
              )
            ),
            TextControl && el(TextControl, {
              label: __('Optional item ID', 'child'),
              help: __('Used for anchor links. Leave blank to auto-generate.', 'child'),
              value: data.id || '',
              onChange: function (value) { updateItem(index, { id: value }); }
            }),
            TextControl && el(TextControl, {
              label: __('Question', 'child'),
              value: data.question || '',
              onChange: function (value) { updateItem(index, { question: value }); }
            }),
            TextareaControl && el(TextareaControl, {
              label: __('Answer', 'child'),
              help: __('Plain text or limited inline HTML (strong, em, a).', 'child'),
              value: data.answer || '',
              onChange: function (value) { updateItem(index, { answer: value }); }
            }),
            ToggleControl && el(ToggleControl, {
              label: __('Open by default', 'child'),
              checked: !!data.open,
              onChange: function (value) { updateItem(index, { open: !!value }); }
            })
          );
        }),
        Button && el(Button, {
          variant: 'primary',
          onClick: addItem
        }, __('Add FAQ item', 'child'))
      )
    );
  }

  registerBlockType('child/faq', Object.assign({}, blockMeta, {
    edit: Edit,
    save: function () { return null; }
  }));
})(window.wp);
