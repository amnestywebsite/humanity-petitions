const { postTypeSlug } = amnestyPetitions;
const { difference, uniq } = lodash;
const { ToggleControl } = wp.components;
const { compose, ifCondition } = wp.compose;
const { withSelect } = wp.data;
const { PluginPostStatusInfo } = wp.editPost;
const { useEffect, useState } = wp.element;
const { __ } = wp.i18n;
const { registerPlugin } = wp.plugins;

/**
 * Toggle the hidden visibility term on the post
 *
 * @param {Boolean} value whether to set hidden or not
 * @param {Object} props the component props
 *
 * @returns {void}
 */
const updateHiddenStatus = (value, props) => {
  const { dispatch, select } = wp.data;

  const { hiddenTerms = [] } = props;
  const { editPost } = dispatch('core/editor');
  const postVisibilities = select('core/editor').getEditedPostAttribute('visibility');

  // removing hidden; diff post visibilities against hidden terms.
  // this allows us to add other visibilities later
  // without affecting this, and vice versa.
  if (!value) {
    const newVisibility = uniq(difference(postVisibilities, hiddenTerms));
    editPost({ visibility: newVisibility });
    return;
  }

  editPost({ visibility: uniq([...postVisibilities, ...hiddenTerms]) });
};

/**
 * Check whether the post has a "hidden" term assignment
 *
 * @param {Object} props the component props
 *
 * @returns {Boolean}
 */
const hasHidden = (props) => {
  const postVisibility = wp.data.select('core/editor').getEditedPostAttribute('visibility');

  if (!postVisibility) {
    return false;
  }

  if (!props?.visibilities) {
    return false;
  }

  const hasHiddenTerm = props.visibilities.filter(
    (visibility) => visibility.slug === 'hidden' && postVisibility.indexOf(visibility.id) !== -1,
  );

  return hasHiddenTerm.length > 0;
};

const ToggleCurationVisibility = (props) => {
  const [isHidden, setIsHidden] = useState(hasHidden(props));

  useEffect(() => setIsHidden(hasHidden(props)));

  if (props.isLoading || !props?.visibilities?.length) {
    return null;
  }

  return (
    <PluginPostStatusInfo className="amnesty-byline">
      <ToggleControl
        label={__('Hide from curation blocks and index pages', 'aip')}
        checked={isHidden}
        onChange={() => {
          const newStatus = !isHidden;
          setIsHidden(newStatus);
          updateHiddenStatus(newStatus, props);
        }}
      />
    </PluginPostStatusInfo>
  );
};

const { getEditedPostAttribute } = wp.data.select('core/editor');

registerPlugin('amnesty-hide-from-curation', {
  render: compose([
    withSelect((select) => {
      const { isResolving } = select('core/data');
      const isLoading = isResolving('core', 'getEntityRecords', ['taxonomy', 'visibility']);
      const visibilities = select('core').getEntityRecords('taxonomy', 'visibility');
      const hiddenTerms = (visibilities || [])
        .filter((visibility) => visibility.slug === 'hidden')
        .map((visibility) => visibility.id);

      return { isLoading, visibilities, hiddenTerms };
    }),
    ifCondition(() => getEditedPostAttribute('type') === postTypeSlug),
  ])(ToggleCurationVisibility),
});
