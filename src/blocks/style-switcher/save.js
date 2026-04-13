import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import { MoonIcon, SunIcon } from './icons.js';

export default function save( { attributes } ) {
	const { size, darkStyleSlug } = attributes;
	const classes = size ? `is-${ size }` : '';
	const inputId = 'ls-plugin-style-switcher-input';
	const selectedDarkStyle = darkStyleSlug || 'dark';

	return (
		<div
			{ ...useBlockProps.save( {
				className: classes,
				'data-style-variation': selectedDarkStyle,
			} ) }
		>
			<label
				className="wp-block-ls-plugin-style-switcher__label"
				htmlFor={ inputId }
			>
				<input
					id={ inputId }
					type="checkbox"
					className="wp-block-ls-plugin-style-switcher__input"
					data-style-variation={ selectedDarkStyle }
					role="switch"
					aria-label={ __(
						'Switch to dark mode, currently light',
						'ls-plugin'
					) }
				/>
				<span className="wp-block-ls-plugin-style-switcher__track">
					<span className="wp-block-ls-plugin-style-switcher__selector">
						<span
							className="wp-block-ls-plugin-style-switcher__icon wp-block-ls-plugin-style-switcher__icon--light"
							aria-hidden="true"
						>
							<SunIcon />
						</span>
						<span
							className="wp-block-ls-plugin-style-switcher__icon wp-block-ls-plugin-style-switcher__icon--dark"
							aria-hidden="true"
						>
							<MoonIcon />
						</span>
					</span>
				</span>
			</label>
		</div>
	);
}
