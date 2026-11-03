/**
 * WordPress dependencies
 */
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

/**
 * Save component for the carousel block.
 *
 * @param {Object} props            Block props.
 * @param {Object} props.attributes Block attributes.
 * @return {Element} Element to render.
 */
export default function save( { attributes } ) {
	// Prepare Swiper configuration.
	const swiperConfig = {
		slidesPerView: attributes.slidesToShow,
		spaceBetween: attributes.columnGap,
		speed: attributes.speed,
	};

	// Add autoplay if enabled.
	if ( attributes.autoplay ) {
		swiperConfig.autoplay = {
			delay: attributes.delay,
		};
		swiperConfig.loop = attributes.loop;
	} else {
		swiperConfig.loop = false;
	}

	// Add pagination if enabled.
	if ( attributes.pagination ) {
		swiperConfig.pagination = {
			el: '.swiper-pagination',
			clickable: true,
		};
	}

	// Add navigation if enabled.
	if ( attributes.navigation ) {
		swiperConfig.navigation = {
			nextEl: '.swiper-button-next',
			prevEl: '.swiper-button-prev',
		};
	}

	// Add breakpoints if configured.
	if ( attributes.breakpoints && attributes.breakpoints.length > 0 ) {
		swiperConfig.breakpoints = attributes.breakpoints.reduce(
			( acc, bp ) => {
				if ( bp.breakpoint && bp.slidesToShow ) {
					acc[ bp.breakpoint ] = {
						slidesPerView: bp.slidesToShow,
					};
				}
				return acc;
			},
			{}
		);
	}

	const blockProps = useBlockProps.save( {
		className: 'swiper',
		'data-swiper': JSON.stringify( swiperConfig ),
	} );

	return (
		<div { ...blockProps }>
			<div className="swiper-wrapper">
				<InnerBlocks.Content />
			</div>
			{ attributes.pagination && (
				<div className="swiper-pagination"></div>
			) }
			{ attributes.navigation && (
				<>
					<div className="swiper-button-next"></div>
					<div className="swiper-button-prev"></div>
				</>
			) }
		</div>
	);
}
