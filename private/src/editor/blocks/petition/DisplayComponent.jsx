import { loadBlockStyles, unloadBlockStyles } from './utils';

const { InspectorControls, PlainText } = wp.blockEditor;
const { PanelBody, RadioControl, SelectControl, TextareaControl, TextControl, ToggleControl } =
  wp.components;
const { Component, Fragment } = wp.element;
const { __, _n, _x, sprintf } = wp.i18n;

const sourceOptions = [
  {
    /* translators: [admin] */
    label: __('Iframe', 'aip'),
    value: 'iframe',
  },
];

if (window?.aiSettings?.petitionForm) {
  sourceOptions.unshift({
    /* translators: [admin] */
    label: __('Form', 'aip'),
    value: 'form',
  });
}

export default class DisplayComponent extends Component {
  componentDidMount() {
    const { petitionSource } = this.props.attributes;

    if (petitionSource !== 'form') {
      return;
    }

    loadBlockStyles();
  }

  componentDidUpdate(prevProps) {
    const { petitionSource } = this.props.attributes;

    if (petitionSource === prevProps.petitionSource) {
      return;
    }

    if (petitionSource === 'form') {
      loadBlockStyles();
      return;
    }

    unloadBlockStyles();
  }

  renderControls() {
    const { attributes, setAttributes } = this.props;

    const sourceSelector = (
      <PanelBody title={/* translators: [admin] */ __('Petition Form Source', 'aip')}>
        <SelectControl
          label={/* translators: [admin] */ __('Source', 'aip')}
          options={sourceOptions}
          value={attributes.petitionSource}
          onChange={(petitionSource) => setAttributes({ petitionSource })}
        />
      </PanelBody>
    );

    if (attributes.petitionSource === 'iframe') {
      return <InspectorControls>{sourceSelector}</InspectorControls>;
    }

    return (
      <InspectorControls>
        {sourceSelector}
        <PanelBody title={/* translators: [admin] */ __('Petition Settings', 'aip')}>
          <ToggleControl
            label={/* translators: [admin] */ __('Show petition content', 'aip')}
            checked={attributes.showContent}
            onChange={(showContent) => setAttributes({ showContent })}
          />
          <ToggleControl
            label={/* translators: [admin] */ __('Petition content initially visible', 'aip')}
            help={
              /* translators: [admin] */
              __('Whether the petition content is expanded by default', 'aip')
            }
            checked={attributes.contentIsExpanded}
            onChange={(contentIsExpanded) => setAttributes({ contentIsExpanded })}
          />
        </PanelBody>
        <PanelBody
          title={/* translators: [admin] */ __('Newsletter Settings', 'aip')}
          initialOpen={false}
        >
          <ToggleControl
            label={/* translators: [admin] */ __('Show newsletter signup fields', 'aip')}
            checked={attributes.showNewsletter}
            onChange={(showNewsletter) => setAttributes({ showNewsletter })}
          />
          {attributes.showNewsletter && (
            <>
              <TextControl
                label={/* translators: [admin] */ __('Label for acceptance', 'aip')}
                help={
                  /* translators: [admin] */
                  __('Label text for newsletter signup acceptance', 'aip')
                }
                placeholder={
                  /* translators: [admin/front] whether the user wants to sign up to the newsletter when signing a petition */
                  __('Yes, I agree', 'aip')
                }
                value={attributes.newsletterAcceptText}
                onChange={(newsletterAcceptText) => setAttributes({ newsletterAcceptText })}
              />
              <TextControl
                label={/* translators: [admin] */ __('Label for rejection', 'aip')}
                help={
                  /* translators: [admin] */
                  __('Label text for newsletter signup rejection', 'aip')
                }
                placeholder={
                  /* translators: [admin/front] whether the user wants to sign up to the newsletter when signing a petition */
                  __('No, I do not agree', 'aip')
                }
                value={attributes.newsletterRejectText}
                onChange={(newsletterRejectText) => setAttributes({ newsletterRejectText })}
              />
            </>
          )}
        </PanelBody>
        <PanelBody
          title={/* translators: [admin] */ __('Thank You Settings', 'aip')}
          initialOpen={false}
        >
          <TextControl
            help={
              /* translators: [admin] */
              __('Override the global redirect URL for this petition, if desired', 'aip')
            }
            label={/* translators: [admin] */ __('Thank You URL', 'aip')}
            placeholder={amnestyPetitions.redirect}
            value={attributes.thankYouUrl || ''}
            onChange={(thankYouUrl) => setAttributes({ thankYouUrl })}
          />
          <ToggleControl
            label={/* translators: [admin] */ __('Show Thank You Content fields', 'aip')}
            checked={attributes.showThankYouContent}
            onChange={(showThankYouContent) => setAttributes({ showThankYouContent })}
          />
          {attributes.showThankYouContent && (
            <Fragment>
              <TextControl
                label={/* translators: [admin] */ __('Thank You Title', 'aip')}
                help={
                  /* translators: [admin] */
                  __('Override the global Thank You Title for this petition, if desired', 'aip')
                }
                value={attributes.thankYouTitle || ''}
                onChange={(thankYouTitle) => setAttributes({ thankYouTitle })}
              />
              <TextControl
                label={/* translators: [admin] */ __('Thank You Sub-title', 'aip')}
                help={
                  /* translators: [admin] */
                  __('Override the global Thank You Sub-title for this petition, if desired', 'aip')
                }
                value={attributes.thankYouSubTitle || ''}
                onChange={(thankYouSubTitle) => setAttributes({ thankYouSubTitle })}
              />
              <TextareaControl
                label={/* translators: [admin] */ __('Thank You Content', 'aip')}
                help={
                  /* translators: [admin] */
                  __('Override the global Thank You Content for this petition, if desired', 'aip')
                }
                value={attributes.thankYouContent || ''}
                onChange={(thankYouContent) => setAttributes({ thankYouContent })}
              />
            </Fragment>
          )}
        </PanelBody>
        <PanelBody
          title={/* translators: [admin] */ __('Target Settings', 'aip')}
          initialOpen={false}
        >
          <TextControl
            label={/* translators: [admin] */ __('Target signature count', 'aip')}
            type="number"
            min={1000}
            max={250000}
            step={1000}
            value={attributes.targetCount}
            onChange={(count) => setAttributes({ targetCount: parseInt(count || 1000, 10) })}
          />
          <ToggleControl
            label={/* translators: [admin] */ __('Show target reached text', 'aip')}
            help={
              /* translators: [admin] */
              __('Show additional text on petition when signature target has been reached', 'aip')
            }
            checked={attributes.showTargetReachedText}
            onChange={(showTargetReachedText) => setAttributes({ showTargetReachedText })}
          />
          {attributes.showTargetReachedText && (
            <TextControl
              label={/* translators: [admin] */ __('Target reached text', 'aip')}
              value={attributes.targetReachedText || ''}
              onChange={(targetReachedText) => setAttributes({ targetReachedText })}
            />
          )}
          {attributes.showTargetReachedText && (
            <ToggleControl
              label={/* translators: [admin] */ __('Replace progress indicator', 'aip')}
              help={
                /* translators: [admin] */
                __(
                  'Replace the progress indicator with the above text when the target is reached',
                  'aip',
                )
              }
              checked={attributes.replaceProgressWithMessage}
              onChange={(replaceProgressWithMessage) =>
                setAttributes({ replaceProgressWithMessage })
              }
            />
          )}
        </PanelBody>
      </InspectorControls>
    );
  }

  renderHeader() {
    const { attributes, setAttributes } = this.props;

    return (
      <div className="petition-header">
        <PlainText
          className="petition-title"
          rows="1"
          placeholder={
            /* translators: [front] petitions call to action button */
            __('Act Now', 'aip')
          }
          value={attributes.title}
          onChange={(title) => setAttributes({ title })}
        />
        <PlainText
          className="petition-subtitle"
          rows="2"
          placeholder={/* translators: [admin] */ __('Subtitle', 'aip')}
          value={attributes.subtitle}
          onChange={(subtitle) => setAttributes({ subtitle })}
        />
      </div>
    );
  }

  renderContent() {
    const { attributes, setAttributes } = this.props;

    if (!attributes.showContent) {
      return null;
    }

    let className = 'petition-contentReveal';
    if (attributes.contentIsExpanded) {
      className += ' is-open';
    }

    return (
      <div className={className}>
        <dl>
          <dt>
            <PlainText
              className="petition-contentTitle"
              rows="1"
              placeholder={/* translators: [admin] */ __('See the petition content', 'aip')}
              value={attributes.contentTitle}
              onChange={(contentTitle) => setAttributes({ contentTitle })}
            />
          </dt>
          <dd>
            <PlainText
              className="petition-content"
              rows="4"
              placeholder={/* translators: [admin] */ __('Petition content', 'aip')}
              value={attributes.content}
              onChange={(content) => setAttributes({ content })}
            />
          </dd>
        </dl>
      </div>
    );
  }

  renderForm() {
    const { attributes, setAttributes } = this.props;

    return (
      <div className="petition-form">
        <div className="petition-formFill">
          <input
            type="text"
            placeholder={
              /* translators: [front/admin] input placeholder text */
              __('First name', 'aip')
            }
          />
          <input
            type="text"
            placeholder={
              /* translators: [front/admin] input placeholder text */
              __('Last name', 'aip')
            }
          />
          <input
            type="tel"
            placeholder={
              /* translators: [front/admin] input placeholder text */
              __('Phone', 'aip')
            }
          />
          <input
            type="email"
            placeholder={
              /* translators: [front/admin] input placeholder text */
              __('Email', 'aip')
            }
          />
        </div>
        {attributes.showNewsletter && (
          <div className="petition-formExtra">
            <p>{amnestyPetitions.mailingList}</p>
            <div className="petition-mailerConsent">
              <RadioControl
                // noop
                onChange={() => null}
                options={[
                  { value: 'yes', label: attributes.newsletterAcceptText },
                  { value: 'no', label: attributes.newsletterRejectText },
                ]}
              />
            </div>
          </div>
        )}
        <button className="btn btn--fill" type="submit">
          <PlainText
            className="petition-buttonText"
            rows="1"
            placeholder={/* translators: [admin/front] */ __('Sign the petition', 'aip')}
            value={attributes.buttonText}
            onChange={(buttonText) => setAttributes({ buttonText })}
          />
        </button>
      </div>
    );
  }

  renderSignatures() {
    const { attributes } = this.props;
    const { locale, signatures = 0 } = amnestyPetitions;
    const percentage = Math.max((signatures / attributes.targetCount) * 100, 1);
    const targetReached = parseInt(signatures, 10) === parseInt(attributes.targetCount, 10);
    const showProgress = !targetReached || !attributes.replaceProgressWithMessage;

    return (
      <div className="petition-signatures">
        <p>
          <strong>
            {sprintf(
              /* translators: [front/admin] %s: the current number of signatures */
              _n('%s has signed.', '%s have signed.', parseInt(signatures, 10), 'aip'),
              new Intl.NumberFormat(locale).format(signatures),
            )}
          </strong>
          &nbsp;
          <span>
            {sprintf(
              /* translators: [admin/front] %s: the target number of signatures */
              _x("Let's get to %s", 'Petition target signatory count', 'aip'),
              new Intl.NumberFormat(locale).format(attributes.targetCount),
            )}
          </span>
        </p>
        {showProgress && (
          <div className="petition-progress">
            <div
              className="petition-progressBar"
              role="progressbar"
              aria-valuenow="n"
              aria-valuemin="0"
              aria-valuemax="100"
              style={{
                '--p': `${200 - percentage}%`,
                width: `${percentage}%`,
              }}
            >
              <span className="screen-reader-text">{`${percentage}%`}</span>
            </div>
          </div>
        )}
        {attributes.showTargetReachedText && <div>{attributes.targetReachedText}</div>}
      </div>
    );
  }

  renderIframe() {
    const { attributes, setAttributes } = this.props;

    return (
      <div className="petition-form-iframe">
        <TextControl
          label={/* translators: [admin] */ __('Iframe URL', 'aip')}
          help={/* translators: [admin] */ __('Enter the URL for the iframe', 'aip')}
          placeholder={
            /* translators: [admin] */
            _x(
              'https://join.amnesty.org/page/12345/action/1?locale=en-US',
              'Example petition iframe URL',
              'aip',
            )
          }
          type="url"
          value={attributes.iframeUrl}
          onChange={(iframeUrl) => setAttributes({ iframeUrl })}
        />
        <ToggleControl
          label={/* translators: [admin] */ __('Apply UTM parameters to iframe URI', 'aip')}
          checked={attributes.passUtmParameters}
          onChange={(passUtmParameters) => setAttributes({ passUtmParameters })}
        />
        <TextControl
          label={/* translators: [admin] */ __('Iframe Height', 'aip')}
          help={/* translators: [admin] */ __('Enter the height for the iframe', 'aip')}
          type="number"
          step={10}
          value={attributes.iframeHeight}
          onChange={(iframeHeight) => setAttributes({ iframeHeight })}
        />
        {attributes.iframeUrl && (
          <iframe height={attributes.iframeHeight} src={attributes.iframeUrl} />
        )}
      </div>
    );
  }

  render() {
    const { attributes, className } = this.props;
    const { petitionSource } = attributes;
    const { formEnabled } = amnestyPetitions;

    if (!formEnabled || petitionSource === 'iframe') {
      return (
        <Fragment>
          {this.renderControls()}
          <div className={className}>{this.renderIframe()}</div>
        </Fragment>
      );
    }

    return (
      <Fragment>
        {this.renderControls()}
        <div className={className}>
          {this.renderHeader()}
          {this.renderContent()}
          {this.renderForm()}
          {this.renderSignatures()}
          <hr />
          <div
            className="petition-terms"
            dangerouslySetInnerHTML={{ __html: amnestyPetitions.terms }}
          />
        </div>
      </Fragment>
    );
  }
}
