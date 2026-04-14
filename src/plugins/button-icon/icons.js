import { Path, SVG } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

function ArrowRightIcon() {
	return (
		<SVG width="20" height="20" viewBox="0 0 24 24" fill="none">
			<Path
				fill="currentColor"
				d="M4 11h12.17l-3.58-3.59L14 6l6 6-6 6-1.41-1.41L16.17 13H4v-2z"
			/>
		</SVG>
	);
}

function ArrowLeftIcon() {
	return (
		<SVG width="20" height="20" viewBox="0 0 24 24" fill="none">
			<Path
				fill="currentColor"
				d="M20 11H7.83l3.58-3.59L10 6l-6 6 6 6 1.41-1.41L7.83 13H20v-2z"
			/>
		</SVG>
	);
}

function ChevronRightIcon() {
	return (
		<SVG width="20" height="20" viewBox="0 0 24 24" fill="none">
			<Path fill="currentColor" d="m10 6 6 6-6 6-1.41-1.41L13.17 12 8.59 7.41 10 6z" />
		</SVG>
	);
}

function ArrowUpIcon() {
	return (
		<SVG width="20" height="20" viewBox="0 0 24 24" fill="none">
			<Path
				fill="currentColor"
				d="M11 20V7.83l-3.59 3.58L6 10l6-6 6 6-1.41 1.41L13 7.83V20h-2z"
			/>
		</SVG>
	);
}

function ArrowDownIcon() {
	return (
		<SVG width="20" height="20" viewBox="0 0 24 24" fill="none">
			<Path
				fill="currentColor"
				d="M11 4v12.17l-3.59-3.58L6 14l6 6 6-6-1.41-1.41L13 16.17V4h-2z"
			/>
		</SVG>
	);
}

function ExternalIcon() {
	return (
		<SVG width="20" height="20" viewBox="0 0 24 24" fill="none">
			<Path
				fill="currentColor"
				d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3zM5 5h6v2H7v10h10v-4h2v6H5V5z"
			/>
		</SVG>
	);
}

export const BUTTON_ICONS = [
	{
		label: __( 'Arrow Right', 'ls-plugin' ),
		value: 'arrow-right',
		char: '→',
		icon: <ArrowRightIcon />,
	},
	{
		label: __( 'Arrow Left', 'ls-plugin' ),
		value: 'arrow-left',
		char: '←',
		icon: <ArrowLeftIcon />,
	},
	{
		label: __( 'Chevron Right', 'ls-plugin' ),
		value: 'chevron-right',
		char: '›',
		icon: <ChevronRightIcon />,
	},
	{
		label: __( 'Arrow Up', 'ls-plugin' ),
		value: 'arrow-up',
		char: '↑',
		icon: <ArrowUpIcon />,
	},
	{
		label: __( 'Arrow Down', 'ls-plugin' ),
		value: 'arrow-down',
		char: '↓',
		icon: <ArrowDownIcon />,
	},
	{
		label: __( 'External Link', 'ls-plugin' ),
		value: 'external',
		char: '↗',
		icon: <ExternalIcon />,
	},
];

export function getButtonIconChar( iconSlug ) {
	const icon = BUTTON_ICONS.find( ( item ) => item.value === iconSlug );
	return icon ? icon.char : '';
}
