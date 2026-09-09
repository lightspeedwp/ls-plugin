# Changelog

All notable changes to this plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Added a navigation ref auto-resolver: when a `core/navigation` block has no `ref` attribute, it is resolved at render time to the `wp_navigation` post matching a documented slug convention (`header-navigation` by default, filterable via `ls_plugin_nav_ref_slug`), so themes never need a hardcoded, environment-specific post ID. The lookup allows multilingual plugins (WPML, Polylang) to filter it and resolve the translated nav post for the current language.
- Added default term seeding for Portfolio taxonomies (Industries, Software, Project types, and Services) so a fresh plugin install reproduces the approved term set instead of requiring manual term creation.
- Added a Style Switcher block with selectable theme style variations and configurable icon display behaviour.
- Added a Button Icon selector panel for core Button blocks, including left/right positioning and up/down icon options.
- Added a Back to Top option as a `core/button` variation so users inherit native Button styling controls and icon compatibility.
- Added smooth scrolling support for Back to Top button clicks and internal anchor links using vanilla JavaScript.
- Added sticky positioning mode for Back to Top button with scroll-based visibility control.
- Added inspector controls for Back to Top button with position mode selection (Inline/Scroll, Sticky, Fixed) and configurable visibility threshold (0-100%).
- Linkable Group Blocks support for `core/group`, `core/column`, and `core/cover`, including custom URLs and current-post linking from the block toolbar.
- Added plugin-managed SCF Local JSON handling and validation utilities for field groups, post types, and taxonomies.
- Added an SCF field-group schema for repository validation workflows.
- Added SCF JSON definitions for a Portfolio post type, Industry and Service taxonomies, and Portfolio custom fields.
- Added SCF permalink controls on the WordPress Permalinks screen for portfolio archives and related taxonomies.
- Added a new Search Filter block (`ls-plugin/search-filter`) for Query Loop blocks with dynamic rendering and support for custom-field (`meta_key`) searching.
- Added a new Taxonomy Filter block (`ls-plugin/taxonomy-filter`) for Query Loop blocks with dropdown and button display modes, post count display, color customization, and expand/collapse functionality for large term lists.
- Added Carousel block (`ls-plugin/carousel`) with Swiper.js integration, supporting configurable slides to show (1-5), column gap, pagination, navigation arrows, autoplay, loop, speed controls, and responsive breakpoints.
- Added Carousel Slide block (`ls-plugin/carousel-slide`) as a child block with InnerBlocks support for core content blocks, vertical alignment controls, and full block supports (border, color, spacing).
- Added Swiper library (v12.0.3) bundled locally in the plugin assets folder for offline carousel functionality.
- Added server-side carousel asset management using render_block filter for reliable conditional loading of Swiper CSS, JS, and initialization scripts.
- Added a `lightspeed` SVG icon collection (`LS_Plugin\Icons`) using the WordPress 7.1 icon registration API (`wp_register_icon_collection()` / `wp_register_icon()`), auto-registering every SVG dropped into `assets/icons/lightspeed/` for use in the block editor and via `wp_get_icon()`.
- Added 93 icons to the `lightspeed` collection, sourced from [Phosphor Icons](https://phosphoricons.com/) (MIT licensed), covering navigation, search, contact, social, content/meta, media, tour/commerce, and site-chrome use cases.

### Changed
- Changed Back to Top implementation from a standalone custom block to a `core/button` variation.
- Changed Back to Top frontend targeting to use a dedicated wrapper class (`is-back-to-top`) for reliable JS and CSS behaviour.
- Changed Back to Top visibility to always display (removed scroll-threshold hide/show behaviour).
- Changed linkable block source assets to load from `src/` and updated build configuration to match the new asset layout.
- Changed the plugin bootstrap to load SCF JSON configuration, validation, and permalink management classes.
- Changed Search Filter block interactivity handling to use a dedicated view script module with debounced query updates and Query Loop integration hooks.
- Changed Taxonomy Filter block interactivity handling to use dedicated view script module with client-side navigation and per-instance state management for expand/collapse controls.
- Changed all `inc/` classes to be namespaced under `LS_Plugin\`, dropping the `LS_Plugin_` prefix from class names (e.g. `LS_Plugin_Carousel` → `LS_Plugin\Carousel`), and renamed the corresponding files to match (e.g. `class-ls-plugin-icons.php` → `class-icons.php`).

### Deprecated

### Removed
- Removed the standalone Back to Top block source in favour of variation-based implementation.

### Fixed
- Fixed the Portfolio archive (`/portfolio/`) 404ing on the consuming theme: a prior commit had accidentally reintroduced the pre-#18 `ls_plugin_portfolio`/`ls_plugin_portfolio_industry`/`ls_plugin_portfolio_service` CPT/taxonomy naming from a stale local SCF-JSON export, reverting [#18](https://github.com/lightspeedwp/ls-plugin/pull/18)'s deliberate rename to `project`/`project-group`/`project-tag`/`project-type` (which matches LIVE and what `ls-theme` is built against). Restored `scf-json/post_type_portfolio.json` and `scf-json/group_ls_plugin_portfolio_fields.json` to #18's naming (verified byte-for-byte identical) and removed the stale duplicate `taxonomy-portfolio-industry.json`/`taxonomy-portfolio-service.json` files. See [#23](https://github.com/lightspeedwp/ls-plugin/pull/23).
- Fixed the Linkable Blocks stylesheet 404ing on every page load sitewide: `inc/linkable-blocks.php` enqueued `build/css/style-linkable-blocks.css`, a filename that doesn't exist (likely copy-pasted from another block's enqueue code, which does use a `style-` prefix). The actual compiled file is `build/css/linkable-blocks.css`. Corrected the enqueued path to match.
- Fixed editor runtime errors from invalid React component handling in Back to Top editor integration.
- Fixed strict mode error in Back to Top animation loop by replacing `arguments.callee` with a named animation step.
- Fixed Taxonomy Filter full-page refresh behaviour by enabling client-side Query Loop navigation when the block is present.
- Fixed Search Filter full-page refresh behaviour by enabling client-side Query Loop navigation when the block is present.

### Security

---

## [0.1.0] - 2025-01-01

### Added

- Initial plugin structure with block-ready folder layout.
- `plugin-utils.mjs` for plugin validation, schema checks, and security scanning.
- Composer-based PHP quality tooling (PHPCS, PHPCBF, PHP lint).
- `.github/` folder with Copilot instructions, prompts, reports, tasks, and workflows.
- `.agents/` folder with portable skills and agent personas.
- `docs/` folder for end-user documentation.

---

[Unreleased]: https://github.com/lightspeedwp/ls-plugin/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/lightspeedwp/ls-plugin/releases/tag/v0.1.0
