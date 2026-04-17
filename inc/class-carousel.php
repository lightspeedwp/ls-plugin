<?php
/**
 * Carousel Block
 *
 * Registers and handles the Carousel block with Swiper library integration.
 *
 * @package LS_Plugin
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LS_Plugin_Carousel
 *
 * Handles registration and asset enqueuing for the Carousel blocks.
 */
class LS_Plugin_Carousel {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_swiper_assets' ) );
		add_action( 'wp_footer', array( $this, 'initialise_swiper' ) );
	}

	/**
	 * Register the carousel and carousel-slide blocks.
	 */
	public function register_blocks() {
		// Register carousel block.
		register_block_type( LS_PLUGIN_PLUGIN_DIR . 'build/blocks/carousel' );

		// Register carousel slide block.
		register_block_type( LS_PLUGIN_PLUGIN_DIR . 'build/blocks/carousel-slide' );
	}

	/**
	 * Enqueue Swiper library assets on the front end.
	 */
	public function enqueue_swiper_assets() {
		// Only enqueue if carousel block is present on the page.
		if ( ! has_block( 'ls-plugin/carousel' ) ) {
			return;
		}

		// Use CDN for Swiper library.
		wp_enqueue_style(
			'swiper',
			'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css',
			array(),
			'12.0.3'
		);

		wp_enqueue_script(
			'swiper',
			'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js',
			array(),
			'12.0.3',
			true
		);
	}

	/**
	 * Initialise Swiper instances in the footer.
	 */
	public function initialise_swiper() {
		// Only output if carousel block is present on the page.
		if ( ! has_block( 'ls-plugin/carousel' ) ) {
			return;
		}
		?>
		<script>
		document.addEventListener( 'DOMContentLoaded', function() {
			const carousels = document.querySelectorAll( '.wp-block-ls-plugin-carousel' );
			if ( carousels.length > 0 ) {
				carousels.forEach( function( carousel ) {
					if ( carousel.dataset.swiper ) {
						new Swiper( carousel, JSON.parse( carousel.dataset.swiper ) );
					}
				} );
			}
		} );
		</script>
		<?php
	}
}
