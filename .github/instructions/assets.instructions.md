---
applyTo: "assets/**,src/**"
---

# Assets Instructions

## Asset organisation

| Folder | Content |
|---|---|
| `assets/css/` | Static (pre-built) CSS files for frontend or admin |
| `assets/js/` | Static (pre-built) JS files not managed by block build |
| `assets/images/` | Plugin images (logos, backgrounds, etc.) |
| `assets/icons/` | SVG or PNG icons |
| `src/blocks/` | Block source files — compiled by `@wordpress/scripts` |
| `src/css/` | Non-block CSS source files |
| `src/js/` | Non-block JS source files |
| `blocks/` | Built block assets — output of `npm run build` |

## Rules

- Do not mix source and built files in the same folder.
- `src/` contains files that need compilation.
- `assets/` contains files that are already production-ready.
- `blocks/` contains built block output from `@wordpress/scripts`.
- Do not commit compiled output from `src/` — use `npm run build` to generate it.

## Enqueuing assets in PHP

Use `wp_enqueue_style()` and `wp_enqueue_script()` with versioning:

```php
wp_enqueue_style(
    '{{PLUGIN_SLUG}}-frontend',
    {{NAMESPACE}}_PLUGIN_URL . 'assets/css/frontend.css',
    [],
    {{NAMESPACE}}_VERSION
);
```

## Block assets

Block assets (editor and frontend CSS/JS) are declared in `block.json` and enqueued automatically by `register_block_type()`.
Do not manually enqueue block scripts — let `block.json` handle it.

## Image and icon guidelines

- Optimise all images before committing.
- Use SVG for icons where possible.
- Do not commit large image files to the repository.
