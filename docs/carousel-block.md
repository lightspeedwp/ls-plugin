# Carousel Block

The Carousel block provides a responsive image/content slider using the Swiper library, integrated into ls-plugin following the same pattern as the taxonomy-filter and search-filter blocks.

## Blocks Included

### 1. Carousel (ls-plugin/carousel)
The parent container block that manages the slider settings and contains carousel slide blocks.

**Category:** Media
**Supports:**
- Wide and full alignment
- Spacing (padding and margin)
- Anchor links

**Settings:**
- **Slides to show:** Number of slides visible at once (1-5)
- **Columns gap:** Space between slides in pixels (0-100)
- **Dots navigation:** Show pagination dots below the carousel
- **Arrows navigation:** Show previous/next arrow buttons
- **Autoplay:** Enable automatic slide advancement
  - **Delay:** Time between transitions in milliseconds (500-9999)
  - **Infinite:** Enable infinite loop (requires total slides ≥ slides to show × 2)
- **Speed:** Transition animation duration in milliseconds (100-900)
- **Responsive breakpoints:** Configure up to 3 breakpoints with custom slides-to-show values

### 2. Carousel Slide (ls-plugin/carousel-slide)
Individual slides within the carousel. Can only be added inside a Carousel block.

**Category:** Media
**Parent:** ls-plugin/carousel
**Supports:**
- Border (color, radius, style, width)
- Color and gradients
- Spacing (padding)
- Vertical alignment

**Allowed inner blocks:**
- Paragraph
- Heading
- Image
- Group
- Quote
- Pullquote
- Video
- Buttons
- Button

## Usage

1. Add a **Carousel** block to your page
2. Add **Carousel Slide** blocks inside the carousel (use the + button)
3. Add content (images, text, etc.) inside each slide
4. Configure carousel settings in the block inspector sidebar

## Technical Details

**PHP Class:** `LS_Plugin_Carousel` ([inc/class-carousel.php](inc/class-carousel.php))
**Source Files:** 
- [src/blocks/carousel/](src/blocks/carousel/)
- [src/blocks/carousel-slide/](src/blocks/carousel-slide/)
**Built Files:** 
- [build/blocks/carousel/](build/blocks/carousel/)
- [build/blocks/carousel-slide/](build/blocks/carousel-slide/)

**External Dependencies:**
- Swiper library v12.0.3 (loaded from CDN when carousel block is present on page)

**Registration:**
Blocks are registered in `inc/class-carousel.php` and loaded via `ls_plugin_init()` in the main plugin file.

## Browser Support

Same as Swiper library: all modern browsers including:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- iOS Safari (latest)
- Android Chrome (latest)

## Implementation Pattern

This carousel follows the same implementation pattern as the other ls-plugin blocks:
- Block metadata in `block.json`
- Separate `edit.js` and `save.js` components
- SCSS styles compiled to CSS
- PHP class for registration and asset management
- Webpack build process via `@wordpress/scripts`
- UK English localisation via `ls-plugin` textdomain
