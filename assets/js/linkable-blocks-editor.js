( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.hooks || ! wp.blockEditor || ! wp.components || ! wp.data || ! wp.element ) {
		return;
	}

	var addFilter = wp.hooks.addFilter;
	var __ = wp.i18n.__;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useSelect = wp.data.useSelect;
	var BlockControls = wp.blockEditor.BlockControls;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var LinkControl = wp.blockEditor.__experimentalLinkControl;
	var Button = wp.components.Button;
	var PanelBody = wp.components.PanelBody;
	var Popover = wp.components.Popover;
	var ToolbarButton = wp.components.ToolbarButton;
	// `window.lsPluginLinkableBlocks` is injected by the plugin outside this file.
	// Expected shape: { supportedBlocks: string[] }, where each item is a block name
	// such as `core/group` or `ls-plugin/example`.
	var supportedBlocks = window.lsPluginLinkableBlocks && Array.isArray( window.lsPluginLinkableBlocks.supportedBlocks )
		? window.lsPluginLinkableBlocks.supportedBlocks
		: [];

	if ( ! LinkControl ) {
		return;
	}

	function isSupportedBlock( blockName ) {
		return supportedBlocks.indexOf( blockName ) !== -1;
	}

	function mergeClassNames( currentClassName, nextClassName ) {
		return [ currentClassName, nextClassName ].filter( Boolean ).join( ' ' );
	}

	function clearLinkAttributes() {
		return {
			href: undefined,
			linkDestination: undefined,
			linkTarget: undefined
		};
	}

	function getCustomLinkAttributes( nextValue ) {
		var nextUrl = nextValue && nextValue.url ? nextValue.url : '';
		var opensInNewTab = !! ( nextValue && nextValue.opensInNewTab );

		return {
			href: nextUrl || undefined,
			linkDestination: nextUrl ? 'custom' : undefined,
			linkTarget: opensInNewTab ? '_blank' : undefined
		};
	}

	function getContextualLinkAttributes( destination ) {
		return {
			href: undefined,
			linkDestination: destination,
			linkTarget: undefined
		};
	}

	function getLinkControlValue( attributes ) {
		return {
			url: attributes.href,
			opensInNewTab: attributes.linkTarget === '_blank'
		};
	}

	function getCustomLinkControl( attributes, setAttributes, key ) {
		return createElement( LinkControl, {
			key: key,
			value: getLinkControlValue( attributes ),
			onChange: function ( nextValue ) {
				setAttributes( getCustomLinkAttributes( nextValue ) );
			},
			onRemove: function () {
				setAttributes( clearLinkAttributes() );
			}
		} );
	}

	function getLinkOptionButton( label, icon, destination, setAttributes, key ) {
		return createElement( Button, {
			key: key,
			className: 'ls-plugin-linkable-blocks__option',
			icon: icon,
			onClick: function () {
				setAttributes( getContextualLinkAttributes( destination ) );
			}
		}, label );
	}

	function getLinkOptionsMenu( setAttributes, isInsideTermsQuery, key ) {
		var optionChildren = [
			getLinkOptionButton(
				__( 'Link to current post', 'ls-plugin' ),
				'admin-links',
				'post',
				setAttributes,
				'post-link-option'
			)
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
				key: key,
				className: 'ls-plugin-linkable-blocks__menu'
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
					type: 'string'
				},
				linkDestination: {
					type: 'string'
				},
				linkTarget: {
					type: 'string'
				}
			} )
		} );
	}

	function getSelectionPanel( attributes, setAttributes ) {
		var destination = attributes.linkDestination;
		var label = destination === 'term'
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
				onClick: function () {
					setAttributes( clearLinkAttributes() );
				}
			} )
		);
	}

	function getLinkSettingsPanel( attributes, setAttributes, isInsideTermsQuery ) {
		var href = attributes.href;
		var linkDestination = attributes.linkDestination;
		var linkTarget = attributes.linkTarget;

		return createElement(
			PanelBody,
			{
				className: 'ls-plugin-linkable-blocks__panel',
				title: __( 'Link settings', 'ls-plugin' ),
				initialOpen: true
			},
			linkDestination !== 'post' && linkDestination !== 'term'
				? getCustomLinkControl( attributes, setAttributes, 'inspector-custom-link-control' )
				: null,
			! href && ! linkDestination
				? getLinkOptionsMenu( setAttributes, isInsideTermsQuery, 'inspector-link-options' )
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

			var attributes = props.attributes || {};
			var href = attributes.href;
			var linkDestination = attributes.linkDestination;
			var linkTarget = attributes.linkTarget;
			var editorState = useState( false );
			var isEditingURL = editorState[ 0 ];
			var setIsEditingURL = editorState[ 1 ];
			var anchorState = useState( null );
			var popoverAnchor = anchorState[ 0 ];
			var setPopoverAnchor = anchorState[ 1 ];

			var isInsideTermsQuery = useSelect(
				function ( select ) {
					var blockEditorSelect = select( 'core/block-editor' );

					if ( ! blockEditorSelect || ! blockEditorSelect.getBlockParentsByBlockName ) {
						return false;
					}

					var parentIds = blockEditorSelect.getBlockParentsByBlockName( props.clientId, 'core/terms-query' );

					return Array.isArray( parentIds ) && parentIds.length > 0;
				},
				[ props.clientId ]
			);

			var controls = [
				createElement( ToolbarButton, {
					key: 'toolbar-button',
					ref: setPopoverAnchor,
					name: 'link',
					icon: 'admin-links',
					title: __( 'Link', 'ls-plugin' ),
					onClick: function () {
						setIsEditingURL( true );
					},
					isActive: !! href || linkDestination === 'post' || linkDestination === 'term' || isEditingURL
				} )
			];

			if ( isEditingURL ) {
				var popoverChildren = [];
				var hasContextualLink = linkDestination === 'post' || linkDestination === 'term';
				var hasLinkValue = !! href || hasContextualLink;

				if ( ! hasContextualLink ) {
					popoverChildren.push(
						getCustomLinkControl( attributes, props.setAttributes, 'toolbar-custom-link-control' )
					);
				}

				if ( ! hasLinkValue ) {
					popoverChildren.push(
						getLinkOptionsMenu( props.setAttributes, isInsideTermsQuery, 'toolbar-link-options' )
					);
				}

				if ( hasContextualLink ) {
					popoverChildren.push(
						getSelectionPanel( attributes, props.setAttributes )
					);
				}

				controls.push(
					createElement( Popover, {
						key: 'link-popover',
						anchor: popoverAnchor,
						onClose: function () {
							setIsEditingURL( false );
						},
						placement: 'bottom',
						focusOnMount: true,
						offset: 12,
						variant: 'alternate',
						className: 'ls-plugin-linkable-blocks__popover'
					}, popoverChildren )
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
					getLinkSettingsPanel( attributes, props.setAttributes, isInsideTermsQuery )
				)
			);
		};
	}

	function withEditorClasses( BlockListBlock ) {
		return function ( props ) {
			if ( ! isSupportedBlock( props.name ) ) {
				return createElement( BlockListBlock, props );
			}

			var attributes = props.attributes || {};
			var hasLinkedDestination = !! attributes.href || attributes.linkDestination === 'post' || attributes.linkDestination === 'term';

			if ( ! hasLinkedDestination ) {
				return createElement( BlockListBlock, props );
			}

			var wrapperProps = Object.assign( {}, props.wrapperProps, {
				className: mergeClassNames(
					mergeClassNames( props.wrapperProps && props.wrapperProps.className, 'is-linked' ),
					'ls-plugin-linkable-block'
				)
			} );

			return createElement( BlockListBlock, Object.assign( {}, props, {
				wrapperProps: wrapperProps
			} ) );
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
}( window.wp ) );