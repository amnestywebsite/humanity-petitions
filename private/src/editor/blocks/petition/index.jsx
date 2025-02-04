import DisplayComponent from './DisplayComponent.jsx';

const { assign } = lodash;
const { registerBlockType } = wp.blocks;
const { RawHTML } = wp.element;
const { __ } = wp.i18n;

const strType = {
  type: 'string',
  default: '',
};

const intType = {
  type: 'int',
  default: 0,
};

const boolTypeTrue = {
  type: 'bool',
  default: true,
};

const boolTypeFalse = {
  type: 'bool',
  default: false,
};

registerBlockType('amnesty/petition', {
  /* translators: [admin] */
  title: __('Petition', 'aip'),
  /* translators: [admin] */
  description: __('Add a petition', 'aip'),
  icon: 'welcome-write-blog',
  category: 'amnesty-core',
  supports: {
    className: true,
    multiple: false,
    inserter: false,
    reusable: false,
  },
  attributes: {
    title: strType,
    subtitle: strType,
    contentTitle: strType,
    content: strType,
    buttonText: strType,
    thankYouUrl: strType,
    thankYouTitle: strType,
    thankYouSubTitle: strType,
    thankYouContent: strType,
    targetCount: intType,
    targetReachedText: strType,
    showContent: boolTypeTrue,
    showNewsletter: boolTypeTrue,
    showTargetReachedText: boolTypeTrue,
    showThankYouContent: boolTypeFalse,
    contentIsExpanded: boolTypeFalse,
    replaceProgressWithMessage: boolTypeFalse,
    newsletterAcceptText: assign({}, strType, {
      /* translators: [admin/front] whether the user wants to sign up to the newsletter when signing a petition */
      default: __('Yes, I agree', 'aip'),
    }),
    newsletterRejectText: assign({}, strType, {
      /* translators: [admin/front] whether the user wants to sign up to the newsletter when signing a petition */
      default: __('No, I do not agree', 'aip'),
    }),
    petitionSource: assign({}, strType, { default: amnestyPetitions.defaultType }),
    iframeUrl: strType,
    iframeHeight: intType,
    passUtmParameters: boolTypeFalse,
    rawHtml: strType,
  },
  edit: DisplayComponent,
  save: () => null,
});
