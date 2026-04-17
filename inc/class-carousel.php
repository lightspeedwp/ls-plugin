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
	 * Track if carousel block has been rendered.
	 *
	 * @var bool
	 */
	private $carousel_rendered = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'render_block', array( $this, 'enqueue_on_block_render' ), 10, 2 );
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
	 * Enqueue Swiper assets when carousel block is rendered.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block         Block data.
	 * @return string Unmodified block content.
	 */
	public function enqueue_on_block_render( $block_content, $block ) {
		if ( 'ls-plugin/carousel' === $block['blockName'] && ! $this->carousel_rendered ) {
			$this->enqueue_swiper_assets();
			$this->carousel_rendered = true;
		}
		return $block_content;
	}

	/**
	 * Enqueue Swiper library assets.
	 */
	private function enqueue_swiper_assets() {
		// Enqueue local Swiper library.
		wp_enqueue_style(
			'ls-plugin-swiper',
			LS_PLUGIN_PLUGIN_URL . 'assets/swiper/swiper-bundle.min.css',
			array(),
			'12.0.3'
		);

		wp_enqueue_script(
			'ls-plugin-swiper',
			LS_PLUGIN_PLUGIN_URL . 'assets/swiper/swiper-bundle.min.js',
			array(),
			'12.0.3',
			true
		);
	}

	/**
	 * Initialise Swiper instances in the footer.
	 */
	public function initialise_swiper() {
		// Only output if carousel block was rendered on the page.
		if ( ! $this->carousel_rendered ) {
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
