# New Block Prompt

Scaffold a new Gutenberg block for this plugin.

## Instructions

Create a new block with the following details:

- **Block slug**: (replace with your block name, e.g. `my-block`)
- **Block title**: (replace with a human-readable title)
- **Block description**: (replace with a short description)
- **Block category**: (e.g. `text`, `media`, `design`, `theme`, `widgets`)
- **Block type**: static (uses Save component) or dynamic (uses PHP render callback)

## What to create

1. `src/blocks/{{block-slug}}/block.json` — block registration metadata
2. `src/blocks/{{block-slug}}/index.js` — block registration entry point
3. `src/blocks/{{block-slug}}/edit.js` — block editor component
4. `src/blocks/{{block-slug}}/save.js` — block save function (for static blocks)
5. `src/blocks/{{block-slug}}/style.css` — frontend styles
6. For dynamic blocks: PHP render callback registered in `inc/` or the main plugin file

## Rules

- Use `block.json` for all asset declarations — no manual `wp_enqueue_*` for block assets.
- Escape all PHP output in render callbacks.
- Use `{{TEXT_DOMAIN}}` as the text domain for all translated strings.
- Block name must be `{{PLUGIN_SLUG}}/{{block-slug}}`.
- Run `npm run build` after creating source files.
- Register the block in the main plugin file using `register_block_type()`.

## After creation

- Run `npm run schema:validate` to validate `block.json`.
- Run `npm run build` to compile block assets.
- Update `CHANGELOG.md` under `[Unreleased]`.
