( function ( wp ) {
	'use strict';

	if (
		! wp ||
		! wp.hooks ||
		! wp.blockEditor ||
		! wp.components ||
		! wp.data ||
		! wp.element
	) {
		return;
	}

	const addFilter = wp.hooks.addFilter;
	const __ = wp.i18n.__;
	const createElement = wp.element.createElement;
	const Fragment = wp.element.Fragment;
	const useState = wp.element.useState;
	const useSelect = wp.data.useSelect;
	const BlockControls = wp.blockEditor.BlockControls;
	const InspectorControls = wp.blockEditor.InspectorControls;
	const LinkControl = wp.blockEditor.__experimentalLinkControl;
	const Button = wp.components.Button;
	const PanelBody = wp.components.PanelBody;
	const Popover = wp.components.Popover;
	const ToolbarButton = wp.components.ToolbarButton;
	// `window.lsPluginLinkableBlocks` is injected by the plugin outside this file.
	// Expected shape: { supportedBlocks: string[] }, where each item is a block name
	// such as `core/group` or `ls-plugin/example`.
	const supportedBlocks =
		window.lsPluginLinkableBlocks &&
		Array.isArray( window.lsPluginLinkableBlocks.supportedBlocks )
			? window.lsPluginLinkableBlocks.supportedBlocks
			: [];

	if ( ! LinkControl ) {
		return;
	}

	function isSupportedBlock( blockName ) {
		return supportedBlocks.indexOf( blockName ) !== -1;
	}

	function mergeClassNames( currentClassName, nextClassName ) {
		return [ currentClassName, nextClassName ]
			.filter( Boolean )
			.join( ' ' );
	}

	function clearLinkAttributes() {
		return {
			href: undefined,
			linkDestination: undefined,
			linkTarget: undefined,
		};
	}

	function getCustomLinkAttributes( nextValue ) {
		const nextUrl = nextValue && nextValue.url ? nextValue.url : '';
		const opensInNewTab = !! ( nextValue && nextValue.opensInNewTab );

		return {
			href: nextUrl || undefined,
			linkDestination: nextUrl ? 'custom' : undefined,
			linkTarget: opensInNewTab ? '_blank' : undefined,
		};
	}

	function getContextualLinkAttributes( destination ) {
		return {
			href: undefined,
			linkDestination: destination,
			linkTarget: undefined,
		};
	}

	function getLinkControlValue( attributes ) {
		return {
			url: attributes.href,
			opensInNewTab: attributes.linkTarget === '_blank',
		};
	}

	function getCustomLinkControl( attributes, setAttributes, key ) {
		return createElement( LinkControl, {
			key,
			value: getLinkControlValue( attributes ),
			onChange( nextValue ) {
				setAttributes( getCustomLinkAttributes( nextValue ) );
			},
			onRemove() {
				setAttributes( clearLinkAttributes() );
			},
		} );
	}

	function getLinkOptionButton(
		label,
		icon,
		destination,
		setAttributes,
		key
	) {
		return createElement(
			Button,
			{
				key,
				className: 'ls-plugin-linkable-blocks__option',
				icon,
				onClick() {
					setAttributes( getContextualLinkAttributes( destination ) );
				},
			},
			label
		);
	}

	function getLinkOptionsMenu( setAttributes, isInsideTermsQuery, key ) {
		const optionChildren = [
			getLinkOptionButton(
				__( 'Link to current post', 'ls-plugin' ),
				'admin-links',
				'post',
				setAttributes,
				'post-link-option'
			),
		];

		if ( isInsideTermsQuery ) {
			optionChildren.push(
				getLinkOptionButton(
					__( 'Link to current term', 'ls-plugin' ),
					'tag',
					'term',
					setAttributes,
					'term-link-option'
				)
			);
		}

		return createElement(
			'div',
			{
				key,
				className: 'ls-plugin-linkable-blocks__menu',
			},
			optionChildren
		);
	}

	function addLinkAttributes( settings ) {
		if ( ! isSupportedBlock( settings.name ) ) {
			return settings;
		}

		return Object.assign( {}, settings, {
			attributes: Object.assign( {}, settings.attributes, {
				href: {
					type: 'string',
				},
				linkDestination: {
					type: 'string',
				},
				linkTarget: {
					type: 'string',
				},
			} ),
		} );
	}

	function getSelectionPanel( attributes, setAttributes ) {
		const destination = attributes.linkDestination;
		const label =
			destination === 'term'
				? __( 'Linked to current term', 'ls-plugin' )
				: __( 'Linked to current post', 'ls-plugin' );

		return createElement(
			'div',
			{ className: 'ls-plugin-linkable-blocks__selection' },
			createElement(
				'div',
				{ className: 'ls-plugin-linkable-blocks__selection-label' },
				label
			),
			createElement( Button, {
				icon: 'no-alt',
				label: __( 'Remove link', 'ls-plugin' ),
				onClick() {
					setAttributes( clearLinkAttributes() );
				},
			} )
		);
	}

	function getLinkSettingsPanel(
		attributes,
		setAttributes,
		isInsideTermsQuery
	) {
		const href = attributes.href;
		const linkDestination = attributes.linkDestination;

		return createElement(
			PanelBody,
			{
				className: 'ls-plugin-linkable-blocks__panel',
				title: __( 'Link settings', 'ls-plugin' ),
				initialOpen: true,
			},
			linkDestination !== 'post' && linkDestination !== 'term'
				? getCustomLinkControl(
						attributes,
						setAttributes,
						'inspector-custom-link-control'
				  )
				: null,
			! href && ! linkDestination
				? getLinkOptionsMenu(
						setAttributes,
						isInsideTermsQuery,
						'inspector-link-options'
				  )
				: null,
			linkDestination === 'post' || linkDestination === 'term'
				? getSelectionPanel( attributes, setAttributes )
				: null
		);
	}

	function withLinkControls( BlockEdit ) {
		return function ( props ) {
			if ( ! isSupportedBlock( props.name ) ) {
				return createElement( BlockEdit, props );
			}

			const attributes = props.attributes || {};
			const href = attributes.href;
			const linkDestination = attributes.linkDestination;
			const editorState = useState( false );
			const isEditingURL = editorState[ 0 ];
			const setIsEditingURL = editorState[ 1 ];
			const anchorState = useState( null );
			const popoverAnchor = anchorState[ 0 ];
			const setPopoverAnchor = anchorState[ 1 ];

			const isInsideTermsQuery = useSelect(
				function ( select ) {
					const blockEditorSelect = select( 'core/block-editor' );

					if (
						! blockEditorSelect ||
						! blockEditorSelect.getBlockParentsByBlockName
					) {
						return false;
					}

					const parentIds =
						blockEditorSelect.getBlockParentsByBlockName(
							props.clientId,
							'core/terms-query'
						);

					return Array.isArray( parentIds ) && parentIds.length > 0;
				},
				[ props.clientId ]
			);

			const controls = [
				createElement( ToolbarButton, {
					key: 'toolbar-button',
					ref: setPopoverAnchor,
					name: 'link',
					icon: 'admin-links',
					title: __( 'Link', 'ls-plugin' ),
					onClick() {
						setIsEditingURL( true );
					},
					isActive:
						!! href ||
						linkDestination === 'post' ||
						linkDestination === 'term' ||
						isEditingURL,
				} ),
			];

			if ( isEditingURL ) {
				const popoverChildren = [];
				const hasContextualLink =
					linkDestination === 'post' || linkDestination === 'term';
				const hasLinkValue = !! href || hasContextualLink;

				if ( ! hasContextualLink ) {
					popoverChildren.push(
						getCustomLinkControl(
							attributes,
							props.setAttributes,
							'toolbar-custom-link-control'
						)
					);
				}

				if ( ! hasLinkValue ) {
					popoverChildren.push(
						getLinkOptionsMenu(
							props.setAttributes,
							isInsideTermsQuery,
							'toolbar-link-options'
						)
					);
				}

				if ( hasContextualLink ) {
					popoverChildren.push(
						getSelectionPanel( attributes, props.setAttributes )
					);
				}

				controls.push(
					createElement(
						Popover,
						{
							key: 'link-popover',
							anchor: popoverAnchor,
							onClose() {
								setIsEditingURL( false );
							},
							placement: 'bottom',
							focusOnMount: true,
							offset: 12,
							variant: 'alternate',
							className: 'ls-plugin-linkable-blocks__popover',
						},
						popoverChildren
					)
				);
			}

			return createElement(
				Fragment,
				null,
				createElement( BlockEdit, props ),
				createElement( BlockControls, { group: 'other' }, controls ),
				createElement(
					InspectorControls,
					null,
					getLinkSettingsPanel(
						attributes,
						props.setAttributes,
						isInsideTermsQuery
					)
				)
			);
		};
	}

	function withEditorClasses( BlockListBlock ) {
		return function ( props ) {
			if ( ! isSupportedBlock( props.name ) ) {
				return createElement( BlockListBlock, props );
			}

			const attributes = props.attributes || {};
			const hasLinkedDestination =
				!! attributes.href ||
				attributes.linkDestination === 'post' ||
				attributes.linkDestination === 'term';

			if ( ! hasLinkedDestination ) {
				return createElement( BlockListBlock, props );
			}

			const wrapperProps = Object.assign( {}, props.wrapperProps, {
				className: mergeClassNames(
					mergeClassNames(
						props.wrapperProps && props.wrapperProps.className,
						'is-linked'
					),
					'ls-plugin-linkable-block'
				),
			} );

			return createElement(
				BlockListBlock,
				Object.assign( {}, props, {
					wrapperProps,
				} )
			);
		};
	}

	addFilter(
		'blocks.registerBlockType',
		'ls-plugin/linkable-blocks/attributes',
		addLinkAttributes
	);

	addFilter(
		'editor.BlockEdit',
		'ls-plugin/linkable-blocks/controls',
		withLinkControls
	);

	addFilter(
		'editor.BlockListBlock',
		'ls-plugin/linkable-blocks/classes',
		withEditorClasses
	);
} )( window.wp );
