const { transformStyles, store: blockEditorStore } = wp.blockEditor;
const { SandBox } = wp.components;
const { useSelect } = wp.data;
const { useMemo } = wp.element;
const { __ } = wp.i18n;

// Default styles used to unset some of the styles
// that might be inherited from the editor style.
const DEFAULT_STYLES = `
	html,body,:root {
		margin: 0 !important;
		padding: 0 !important;
		overflow: visible !important;
		min-height: auto !important;
	}
`;

export default function Preview({ content, isSelected }) {
	const settingStyles = useSelect(
		(select) => select(blockEditorStore.name).getSettings().styles,
		[],
	);

	const styles = useMemo(
		() => [
			DEFAULT_STYLES,
			...transformStyles((settingStyles ?? []).filter((style) => style.css)),
		],
		[settingStyles],
	);

	return (
		<>
			<SandBox
				html={content}
				styles={styles}
				title={__('Custom HTML Preview', 'default')}
				tabIndex={-1}
			/>
			{!isSelected && <div className="block-library-html__preview-overlay"></div>}
		</>
	);
}
