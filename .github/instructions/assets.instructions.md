---
applyTo: "assets/**,src/**"
---

# Assets Instructions

## Asset organisation

| Folder           | Content                                                |
| ---------------- | ------------------------------------------------------ |
| `assets/css/`    | Static non-source assets only; do not add authored CSS source here |
| `assets/js/`     | Static non-source assets only; do not add authored JS source here |
| `assets/images/` | Plugin images (logos, backgrounds, etc.)               |
| `assets/icons/`  | SVG or PNG icons                                       |
| `src/blocks/`    | Block source files — compiled by `@wordpress/scripts`  |
| `src/css/`       | Non-block CSS source files for the build pipeline      |
| `src/js/`        | Non-block JS source files for the build pipeline       |
| `build/`         | Built CSS/JS assets output by `npm run build`          |

## Rules

- Do not mix source and built files in the same folder.
- Put authored JS and CSS source files in `src/`, not `assets/`.
- `src/` contains files that need compilation.
- `build/` contains the generated files that are enqueued or included by PHP.
- Treat `assets/` as static non-compiled assets only, such as images or icons.
- After changing JS or CSS in `src/`, run `npm run build` to regenerate the `build/` output.
- Do not enqueue or include JS/CSS directly from `src/`.

## WordPress preset syntax

- In JSON property values, use WordPress preset shorthand such as `var(preset|spacing|20)`.
- In CSS, use the CSS custom property form such as `var(--wp--preset--spacing--20)`.

## Enqueuing assets in PHP

Use `wp_enqueue_style()` and `wp_enqueue_script()` with versioning, and point them at built files:

```php
wp_enqueue_style(
    'ls-plugin-frontend',
    LS_PLUGIN_PLUGIN_URL . 'build/css/frontend.css',
    [],
    LS_PLUGIN_VERSION
);
```

When webpack generates `*.asset.php` metadata files, use them for dependencies and versions.

## Block assets

Block assets (editor and frontend CSS/JS) are declared in `block.json` and built into `build/`, then enqueued automatically by `register_block_type()`.
Do not manually enqueue block scripts — let `block.json` handle it.

## Image and icon guidelines

- Optimise all images before committing.
- Use SVG for icons where possible.
- Do not commit large image files to the repository.
