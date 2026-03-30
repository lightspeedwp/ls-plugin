---
applyTo: "**/{blocks,src/blocks}/**"
---

# Blocks Instructions

## Block structure

Each block lives in its own directory under `src/blocks/{{block-name}}/`.
Built assets are output to `blocks/{{block-name}}/`.

Typical block directory:

```
src/blocks/my-block/
├── block.json        Block registration metadata
├── edit.js           Block editor component
├── index.js          Block registration entry point
├── editor.css        Editor-only styles (optional)
└── style.css         Frontend styles (optional)
```

## block.json

Every block must have a `block.json` file with:

- `$schema`: reference the WordPress block schema
- `apiVersion`: use `3`
- `name`: `{{PLUGIN_SLUG}}/block-name`
- `title`, `description`, `category`, `icon`
- `textdomain`: `{{TEXT_DOMAIN}}`
- `editorScript`, `style`, `viewScript` as appropriate
- `supports` and `attributes` declarations

Example:

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "{{PLUGIN_SLUG}}/my-block",
  "title": "My Block",
  "category": "text",
  "icon": "block-default",
  "description": "A short block description.",
  "textdomain": "{{TEXT_DOMAIN}}",
  "editorScript": "file:./index.js",
  "style": "file:./style.css",
  "supports": {
    "html": false,
    "color": { "background": true, "text": true }
  }
}
```

## Registration in PHP

Register blocks in the main plugin file or a dedicated `inc/` file:

```php
register_block_type( {{NAMESPACE}}_PLUGIN_DIR . 'blocks/my-block' );
```

Do not manually register scripts and styles when `block.json` handles it.

## PHP-rendered blocks

For `render_callback` blocks:

```php
function {{PLUGIN_SLUG}}_render_my_block( $attributes, $content, $block ) {
    $title = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : '';
    return '<div class="wp-block-{{PLUGIN_SLUG}}-my-block">' . esc_html( $title ) . '</div>';
}
```

Always escape output. Always sanitise attributes before use.

## Editor vs frontend assets

- Editor CSS: `editorStyle` in `block.json`
- Frontend CSS: `style` in `block.json`
- Editor JS: `editorScript` in `block.json`
- Frontend JS (interactive): `viewScript` in `block.json`

## Build tooling

Use `@wordpress/scripts` for building block assets:

```bash
npm run build    # Production build
npm run start    # Development watch
```

Block source: `src/blocks/`
Block output: `blocks/`
