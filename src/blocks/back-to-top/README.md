# Back to Top Block

A performant, accessible WordPress block that adds scroll-to-top functionality with smooth scrolling for all internal anchor links.

## Features

### Core Functionality
- **Vanilla JavaScript**: No jQuery or external dependencies
- **Smooth Scrolling**: CSS cubic-bezier easing for fluid animations
- **Anchor Link Support**: Automatically enables smooth scrolling for all `#` anchor links
- **Sticky Header Detection**: Automatically calculates and accounts for sticky headers when scrolling

### Accessibility
- **WCAG 2.1 Compliant**: Semantic HTML and proper ARIA attributes
- **Keyboard Navigation**: Full keyboard support with visible focus states
- **Motion Preferences**: Respects `prefers-reduced-motion` with instant scrolling
- **High Contrast Support**: Enhanced outlines in high-contrast mode

### Performance
- **No Libraries**: Zero external dependencies
- **Lazy Script Loading**: Only enqueued on front end when block is used
- **Passive Event Listeners**: Non-blocking scroll event handlers
- **Minimal CSS**: ~1.7 KB minified (0.8 KB GZIP)
- **Minimal JS**: ~1.8 KB minified (0.7 KB GZIP)

### Positioning Modes

#### Scroll Mode (Default)
- Button hidden at page top
- Appears after scrolling 50% viewport height (configurable 0-100%)
- Smooth opacity transition in/out
- Inspector control to adjust scroll threshold

#### Fixed Mode
- Button fixed to bottom-right corner
- Always visible while page is scrolled
- Sticky footer positioning
- Responsive: 2rem on desktop, 1rem on mobile

## Block Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `label` | string | "Back to Top" | Button text |
| `positionMode` | string | "scroll" | "scroll" or "fixed" positioning |
| `scrollThreshold` | number | 50 | Percentage of viewport height to trigger visibility (0-100) |

## Editor Interface

The block includes inspector controls for:
- **Button Label**: Customize button text
- **Position Mode**: Choose between scroll-triggered or fixed positioning
- **Scroll Threshold**: (Only for scroll mode) Set when button appears during scrolling

## Scroll Target

The block scrolls to `.wp-site-blocks` when available, falling back to `document.documentElement` for classic themes.

To customize the scroll target, modify the `getScrollTarget()` function in `view.js`:

```javascript
const getScrollTarget = () => {
	const wpSiteBlocks = document.querySelector( '.wp-site-blocks' );
	return wpSiteBlocks || document.documentElement;
};
```

## Sticky Header Support

The block automatically detects sticky headers using these selectors:
- `header[sticky="true"]`
- `[data-sticky="true"]`
- `.is-sticky`

Add 20px padding below the detected header to prevent content overlap.

To customize header detection, modify `getStickyHeaderOffset()`:

```javascript
const getStickyHeaderOffset = () => {
	const header = document.querySelector( 'header[sticky="true"], [data-sticky="true"], .is-sticky' );
	if ( ! header ) return 0;

	const rect = header.getBoundingClientRect();
	return Math.max( 0, rect.height + 20 );
};
```

## Anchor Link Support

Internal anchor links are automatically enhanced with smooth scrolling:

```html
<!-- Link with anchor -->
<a href="#section-heading">Jump to section</a>

<!-- Target -->
<h2 id="section-heading">Section Heading</h2>
```

The scroll automatically accounts for sticky headers and uses the same easing curve as the back-to-top button.

## CSS Variables (Optional)

While the block has default styling, you can override via CSS custom properties (if extended):

```css
:root {
	--ls-back-to-top-bg-color: #1e293b;
	--ls-back-to-top-text-color: #ffffff;
	--ls-back-to-top-border-radius: 0.375rem;
}
```

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- All modern devices and assistive technologies

## Motion Preferences

The block automatically detects `prefers-reduced-motion`:
- **Instant scrolling**: No animation delay
- **No transitions**: CSS transitions disabled
- **No transforms**: Hover effects simplified

## File Structure

```
src/blocks/back-to-top/
├── block.json          # Block metadata
├── index.js            # Block registration
├── edit.js             # Editor component
├── save.js             # Frontend markup
├── view.js             # Vanilla JS smooth scrolling utility
└── style.scss          # Block styling
```

## Usage Example

Insert the block into your page template or post content:

```html
<!-- wp:ls-plugin/back-to-top {"label":"Back to Top","positionMode":"scroll","scrollThreshold":50} /-->
```

Or use the Gutenberg block inserter and search for "Back to Top".

## Performance Notes

- **Smooth scrolling duration**: 600ms (disabled if `prefers-reduced-motion` is active)
- **Scroll event debouncing**: Not needed—visibility is computed cheaply on each scroll
- **CSS containment**: Block uses `display: block` for fixed mode to enable browser optimizations
- **No layout shifts**: Button positioning prevents cumulative layout shift

## Customization

### Theme Integration

Add to your theme's `functions.php`:

```php
// Customize button styling
add_filter( 'wp_footer', function() {
	echo '<style>
		.wp-block-ls-plugin-back-to-top__link {
			--ls-back-to-top-bg: var(--wp--preset--color--primary);
			--ls-back-to-top-hover-bg: var(--wp--preset--color--secondary);
		}
	</style>';
} );
```

### JavaScript Hooks

Extending the functionality (advanced):

```javascript
// Listen for when button becomes visible/hidden
document.addEventListener( 'click', ( e ) => {
	if ( e.target.matches( '.wp-block-ls-plugin-back-to-top__link' ) ) {
		console.log( 'Back to top clicked' );
	}
} );
```

## Troubleshooting

### Button Not Appearing
- Check that the block position mode is set to "fixed" or that you've scrolled past the threshold
- Verify `.wp-site-blocks` or `document.documentElement` is in the DOM
- Check browser console for JavaScript errors

### Scroll Not Smooth
- Verify `prefers-reduced-motion` is not enabled in OS settings
- Check that internal anchor links have `#id` format matching page elements
- Ensure no conflicting smooth scroll libraries are loaded

### Accessibility Issues
- Use browser dev tools to check ARIA attributes
- Test with keyboard navigation (Tab, Enter)
- Test with screen readers (NVDA, JAWS, VoiceOver)

## Future Enhancements

Possible additions:
- Scroll offset customization via inspector
- Button icon options (arrow, chevron, etc.)
- Custom scroll duration control
- Analytics tracking hooks
- Animation curve customization
