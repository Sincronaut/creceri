/* /blocks/blog-content/index.js */
(function (wp) {
  if (!wp || !wp.blocks || !wp.element) {
    return;
  }

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var registerBlockType = wp.blocks.registerBlockType;
  var __ = (wp.i18n && wp.i18n.__) ? wp.i18n.__ : function (s) { return s; };
  var be = wp.blockEditor || wp.editor;
  if (!be) {
    return;
  }

  var useBlockProps = be.useBlockProps;
  var InspectorControls = be.InspectorControls;
  var components = wp.components || {};
  var PanelBody = components.PanelBody;
  var TextControl = components.TextControl;
  var TextareaControl = components.TextareaControl;
  var Button = components.Button;
  var SelectControl = components.SelectControl;
  var ToolbarGroup = components.ToolbarGroup;
  var ToolbarButton = components.ToolbarButton;
  var Notice = components.Notice;

  var blockMeta = {
    title: __('Blog Content Sections', 'child'),
    description: __('Build long-form content with repeatable text and video sections.', 'child'),
    category: 'widgets',
    icon: 'editor-table',
    supports: { html: false, inserter: true, align: ['wide', 'full'] },
    attributes: {
      section: {
        type: 'array',
        default: [],
        items: {
          type: 'object',
          properties: {
            design: { type: 'string', default: 'section_1' },
            id: { type: 'string' },
            title: { type: 'string' },
            intro: { type: 'string' },
            ul: {
              type: 'array',
              default: [],
              items: { type: 'string' }
            },
            subsection: {
              type: 'array',
              default: [],
              items: {
                type: 'object',
                properties: {
                  id: { type: 'string' },
                  sub_title: { type: 'string' },
                  title: { type: 'string' },
                  text: { type: 'string' },
                  ul: {
                    type: 'array',
                    default: [],
                    items: { type: 'string' }
                  }
                }
              }
            },
            link: { type: 'string' }
          }
        }
      }
    }
  };

  function arrayToLines(arr) {
    return Array.isArray(arr) ? arr.join('\n') : '';
  }

  function linesToArray(value) {
    if (!value) {
      return [];
    }
    return value
      .split(/\r?\n/)
      .map(function (line) { return line.trim(); })
      .filter(function (line) { return line.length > 0; });
  }

  function blankSection(design) {
    if (design === 'section_2') {
      return { design: 'section_2', title: '', intro: '', link: '', id: '' };
    }
    return { design: 'section_1', title: '', intro: '', id: '', ul: [], subsection: [] };
  }

  function blankSubsection() {
    return { sub_title: '', title: '', text: '', ul: [], id: '' };
  }

  function Edit(props) {
    var attributes = props.attributes || {};
    var setAttributes = props.setAttributes;
    var sections = Array.isArray(attributes.section) ? attributes.section : [];
    var blockProps = useBlockProps({ className: 'blog-content-editor' });

    function setSections(next) {
      setAttributes({ section: next });
    }

    function updateSection(index, patch) {
      var next = sections.slice();
      var existing = next[index] || {};
      var design = existing.design || 'section_1';
      var base = blankSection(design);
      var updated = Object.assign({}, base, existing, patch || {});
      if (updated.design === 'section_1') {
        if (!Array.isArray(updated.ul)) {
          updated.ul = [];
        }
        if (!Array.isArray(updated.subsection)) {
          updated.subsection = [];
        }
        delete updated.link;
      } else if (updated.design === 'section_2') {
        updated.link = updated.link || '';
        delete updated.ul;
        delete updated.subsection;
      }
      next[index] = updated;
      setSections(next);
    }

    function changeDesign(index, design) {
      var next = sections.slice();
      var previous = next[index] || {};
      next[index] = Object.assign({}, blankSection(design), previous, { design: design });
      setSections(next);
    }

    function moveSection(from, to) {
      if (to < 0 || to >= sections.length) {
        return;
      }
      var next = sections.slice();
      var item = next.splice(from, 1)[0];
      next.splice(to, 0, item);
      setSections(next);
    }

    function removeSection(index) {
      setSections(sections.filter(function (_, i) { return i !== index; }));
    }

    function addSection(design) {
      var next = sections.slice();
      next.push(blankSection(design || 'section_1'));
      setSections(next);
    }

    function updateSubsection(sectionIndex, subIndex, patch) {
      var section = sections[sectionIndex] || blankSection('section_1');
      var subs = Array.isArray(section.subsection) ? section.subsection.slice() : [];
      var existing = subs[subIndex] || {};
      subs[subIndex] = Object.assign({}, blankSubsection(), existing, patch || {});
      updateSection(sectionIndex, { subsection: subs });
    }

    function addSubsection(sectionIndex) {
      var section = sections[sectionIndex] || blankSection('section_1');
      var subs = Array.isArray(section.subsection) ? section.subsection.slice() : [];
      subs.push(blankSubsection());
      updateSection(sectionIndex, { subsection: subs });
    }

    function removeSubsection(sectionIndex, subIndex) {
      var section = sections[sectionIndex] || blankSection('section_1');
      var subs = Array.isArray(section.subsection) ? section.subsection.slice() : [];
      subs.splice(subIndex, 1);
      updateSection(sectionIndex, { subsection: subs });
    }

    function moveSubsection(sectionIndex, from, to) {
      var section = sections[sectionIndex] || blankSection('section_1');
      var subs = Array.isArray(section.subsection) ? section.subsection.slice() : [];
      if (to < 0 || to >= subs.length) {
        return;
      }
      var item = subs.splice(from, 1)[0];
      subs.splice(to, 0, item);
      updateSection(sectionIndex, { subsection: subs });
    }

    return el(Fragment, null,
      el(InspectorControls, null,
        el(PanelBody, { title: __('Helpful tips', 'child'), initialOpen: true },
          el('p', null, __('Use ?Section: Text? for regular content and ?Section: Video? when you only need a video embed.', 'child')),
          el('p', null, __('IDs are optional; leave blank to auto-generate anchors based on the heading.', 'child'))
        )
      ),
      el('div', blockProps,
        el('div', { className: 'blog-content-editor__intro' },
          el('strong', null, blockMeta.title),
          el('p', null, __('Build structured blog outlines by adding sections below.', 'child'))
        ),
        (!sections || sections.length === 0) && Notice && el(Notice, { status: 'info', isDismissible: false },
          __('No sections yet. Add your first section to begin.', 'child')
        ),
        (sections || []).map(function (section, index) {
          var data = section || {};
          var design = data.design || 'section_1';
          var subsections = Array.isArray(data.subsection) ? data.subsection : [];
          var topList = arrayToLines(data.ul);
          return el('div', { key: index, className: 'blog-content-editor__section' },
            el('div', { className: 'blog-content-editor__section-toolbar' },
              el('span', { className: 'blog-content-editor__section-label' }, __('Section', 'child') + ' ' + (index + 1)),
              ToolbarGroup && el(ToolbarGroup, null,
                ToolbarButton && el(ToolbarButton, {
                  icon: 'arrow-up-alt2',
                  label: __('Move up', 'child'),
                  onClick: function () { moveSection(index, index - 1); },
                  disabled: index === 0
                }),
                ToolbarButton && el(ToolbarButton, {
                  icon: 'arrow-down-alt2',
                  label: __('Move down', 'child'),
                  onClick: function () { moveSection(index, index + 1); },
                  disabled: index === sections.length - 1
                }),
                ToolbarButton && el(ToolbarButton, {
                  icon: 'trash',
                  label: __('Remove section', 'child'),
                  onClick: function () { removeSection(index); },
                  className: 'is-destructive'
                })
              )
            ),
            el('div', { className: 'blog-content-editor__section-fields' },
              SelectControl && el(SelectControl, {
                label: __('Section layout', 'child'),
                value: design,
                options: [
                  { label: __('Section: Text with subsections', 'child'), value: 'section_1' },
                  { label: __('Section: Video embed', 'child'), value: 'section_2' }
                ],
                onChange: function (value) { changeDesign(index, value); }
              }),
              TextControl && el(TextControl, {
                label: __('Optional anchor ID', 'child'),
                help: __('Leave blank to auto-generate from the title.', 'child'),
                value: data.id || '',
                onChange: function (value) { updateSection(index, { id: value }); }
              }),
              TextControl && el(TextControl, {
                label: __('Heading', 'child'),
                value: data.title || '',
                onChange: function (value) { updateSection(index, { title: value }); }
              }),
              TextareaControl && el(TextareaControl, {
                label: __('Intro / lead paragraph', 'child'),
                help: __('Plain text or limited inline HTML (strong, em, a).', 'child'),
                value: data.intro || '',
                onChange: function (value) { updateSection(index, { intro: value }); }
              }),
              design === 'section_1' && TextareaControl && el(TextareaControl, {
                label: __('Top-level bullet list (one item per line)', 'child'),
                value: topList,
                rows: 3,
                onChange: function (value) { updateSection(index, { ul: linesToArray(value) }); }
              }),
              design === 'section_2' && TextControl && el(TextControl, {
                label: __('Embed URL (YouTube, Vimeo, etc.)', 'child'),
                value: data.link || '',
                onChange: function (value) { updateSection(index, { link: value }); }
              }),
              design === 'section_1' && el('div', { className: 'blog-content-editor__subsections' },
                el('h4', null, __('Subsections', 'child')),
                subsections.length === 0 && Notice && el(Notice, { status: 'info', isDismissible: false },
                  __('No subsections yet. Add one to break the section into smaller topics.', 'child')
                ),
                subsections.map(function (item, subIndex) {
                  var sub = item || {};
                  return el('div', { key: subIndex, className: 'blog-content-editor__subsection' },
                    el('div', { className: 'blog-content-editor__subsection-toolbar' },
                      el('span', null, __('Subsection', 'child') + ' ' + (subIndex + 1)),
                      ToolbarGroup && el(ToolbarGroup, null,
                        ToolbarButton && el(ToolbarButton, {
                          icon: 'arrow-up-alt2',
                          label: __('Move up', 'child'),
                          onClick: function () { moveSubsection(index, subIndex, subIndex - 1); },
                          disabled: subIndex === 0
                        }),
                        ToolbarButton && el(ToolbarButton, {
                          icon: 'arrow-down-alt2',
                          label: __('Move down', 'child'),
                          onClick: function () { moveSubsection(index, subIndex, subIndex + 1); },
                          disabled: subIndex === subsections.length - 1
                        }),
                        ToolbarButton && el(ToolbarButton, {
                          icon: 'trash',
                          label: __('Remove subsection', 'child'),
                          onClick: function () { removeSubsection(index, subIndex); },
                          className: 'is-destructive'
                        })
                      )
                    ),
                    TextControl && el(TextControl, {
                      label: __('Optional anchor ID', 'child'),
                      value: sub.id || '',
                      onChange: function (value) { updateSubsection(index, subIndex, { id: value }); }
                    }),
                    TextControl && el(TextControl, {
                      label: __('Subheading', 'child'),
                      value: sub.sub_title || sub.title || '',
                      onChange: function (value) { updateSubsection(index, subIndex, { sub_title: value, title: value }); }
                    }),
                    TextareaControl && el(TextareaControl, {
                      label: __('Body copy', 'child'),
                      value: sub.text || '',
                      onChange: function (value) { updateSubsection(index, subIndex, { text: value }); }
                    }),
                    TextareaControl && el(TextareaControl, {
                      label: __('Nested bullet list (one item per line)', 'child'),
                      value: arrayToLines(sub.ul),
                      rows: 3,
                      onChange: function (value) { updateSubsection(index, subIndex, { ul: linesToArray(value) }); }
                    })
                  );
                }),
                Button && el(Button, {
                  variant: 'secondary',
                  onClick: function () { addSubsection(index); }
                }, __('Add subsection', 'child'))
              )
            )
          );
        }),
        el('div', { className: 'blog-content-editor__actions' },
          Button && el(Button, {
            variant: 'primary',
            onClick: function () { addSection('section_1'); }
          }, __('Add text section', 'child')),
          Button && el(Button, {
            variant: 'secondary',
            onClick: function () { addSection('section_2'); }
          }, __('Add video section', 'child'))
        )
      )
    );
  }

  registerBlockType('child/blog-content', Object.assign({}, blockMeta, {
    edit: Edit,
    save: function () { return null; }
  }));

})(window.wp);
