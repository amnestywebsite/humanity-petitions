const { getBlockTypes, registerBlockType } = wp.blocks;
const { compose } = wp.compose;
const { dispatch, withDispatch } = wp.data;
const { InnerBlocks } = wp.blockEditor;
const { useEffect } = wp.element;
const { __ } = wp.i18n;

const allowedBlocks = () =>
  getBlockTypes()
    .map((b) => b.name)
    .filter((n) => n !== 'amnesty/petition');

const DisplayComponent = () => {
  useEffect(() => {
    dispatch('core/block-editor').setTemplateValidity(true);
  });

  return <InnerBlocks templateLock={false} allowedBlocks={allowedBlocks()} />;
};

const withEnforcedValidity = compose(
  withDispatch((store) => store('core/block-editor').setTemplateValidity(true)),
)(DisplayComponent);

registerBlockType('amnesty/petition-template', {
  /* translators: [admin] */
  title: __('Petition Container', 'aip'),
  /* translators: [admin] */
  description: __('The container for a petition', 'aip'),
  category: 'amnesty-core',
  icon: 'editor-table',
  supports: {
    className: false,
    multiple: false,
    inserter: false,
    reusable: false,
  },
  edit: withEnforcedValidity,
  save() {
    return <InnerBlocks.Content />;
  },
});
