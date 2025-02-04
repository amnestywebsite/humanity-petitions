const { BlockControls, PlainText } = wp.blockEditor;
const { ToolbarButton, ToolbarGroup } = wp.components;
const { useState } = wp.element;
const { __ } = wp.i18n;

import Preview from './Preview.jsx';

export default function HtmlSource({ content, isSelected, setContent, userCanEdit }) {
  const [isPreview, setIsPreview] = useState(!!content || !userCanEdit);

  return (
    <>
			<BlockControls>
				<ToolbarGroup>
					{userCanEdit && (
            <ToolbarButton isPressed={!isPreview} onClick={() => setIsPreview(false)}>
						  HTML
            </ToolbarButton>
          )}
					<ToolbarButton isPressed={isPreview} onClick={() => setIsPreview(true)}>
						{__('Preview', 'default')}
					</ToolbarButton>
				</ToolbarGroup>
			</BlockControls>
      {isPreview && <Preview content={content} isSelected={isSelected} />}
      {!isPreview && (
        <PlainText
          value={content}
          onChange={setContent}
          placeholder={__('Write HTML…', 'default')}
          aria-label="HTML"
        />
      )}
    </>
  );
}
