const { registerBlockStyle, unregisterBlockStyle } = wp.blocks;
const { __ } = wp.i18n;

export const loadBlockStyles = () => {
  if (!amnestyPetitions.formEnabled) {
    return;
  }

  registerBlockStyle('amnesty/petition', {
    /* translators: [admin/front] text alignment */
    label: __('Default', 'aip'),
    name: 'default',
    isDefault: true,
  });

  registerBlockStyle('amnesty/petition', {
    /* translators: [admin] */
    label: __('Dark grey background', 'aip'),
    name: 'dark-grey',
    isDefault: false,
  });
};

export const unloadBlockStyles = () => {
  unregisterBlockStyle('amnesty/petition', 'default');
  unregisterBlockStyle('amnesty/petition', 'light-grey');
  unregisterBlockStyle('amnesty/petition', 'dark-grey');
};
