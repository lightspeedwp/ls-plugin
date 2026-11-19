# Changelog

All notable changes to this plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Added Sentry error reporting (LSA-255). This plugin is now the single owner of the Sentry PHP SDK (`sentry/sentry`), so nothing is reported twice, and the theme needs no Sentry code: the plugin also tags events with the active theme (`theme` and `theme.version`, set on `after_setup_theme`), so switching the theme off never stops error reporting. The setup matches the Sentry module in `sd-enhancements-2026`, so agency sites report alike. `inc/class-sentry.php` starts Sentry from `ls-plugin.php` only when a `SENTRY_DSN` constant (for example in `wp-config.php`) or environment variable is set and the SDK is installed in `vendor/`; without both it does nothing, so local sites and CI are unaffected. The DSN is never stored in the repository. Events carry the environment (from `WP_ENVIRONMENT_TYPE`), the release `ls-plugin@<version>` and the tags `component: ls-plugin` and `plugin.version`. Minimal data is sent, using the SDK's `data_collection` settings: no user details or IP address, no request or response bodies, no cookies, no auth headers or nonces, no stack frame variables (function arguments), and no password, token, key, nonce or email values in the request URL, the query string or the `Referer` header. Notices and deprecations (`E_NOTICE`, `E_USER_NOTICE`, `E_DEPRECATED`, `E_USER_DEPRECATED`) are ignored. `composer.json` now sets `config.platform.php` to 8.0.30 so dependencies stay installable on the plugin's minimum PHP version. See [#32](https://github.com/lightspeedwp/ls-plugin/pull/32).
- Added Google Analytics 4 event tracking, written in code and sent through the `gtag()` function Site Kit already loads (no Google Tag Manager). It sends `consultation_submit`, `contact_submit`, `website_brief_submit` and `newsletter_subscribe` when the matching Gravity Forms form is submitted, `consultation_cta_click` (with the CTA location) when a consultation CTA is clicked, and `portfolio_read` once per case-study view after about 30 seconds with the tab visible or half the page scrolled. No form field values are sent. Nothing is sent when `gtag()` is not available (for example for logged-in users, whom Site Kit does not track), and consent is left to Site Kit's consent mode settings. Forms are matched by title, so it works on every environment. Return `false` from the `ls_plugin_analytics_events_enabled` filter to switch it off. The events are listed in `docs/analytics-events.md`. See LSA-235 and [#33](https://github.com/lightspeedwp/ls-plugin/pull/33).
- Added a front-end search filter that keeps posts set to "noindex" in Yoast SEO out of the site search results, in the main search query and in Query Loop blocks that are searching. Posts with no noindex setting stay searchable, nothing is unpublished, and the admin and REST searches are untouched. Return `false` from the `ls_plugin_search_exclude_noindex` filter to switch it off. See [#29](https://github.com/lightspeedwp/ls-plugin/pull/29).
- Added a Lefthook `pre-commit` hook (`lefthook.yml`) with two jobs. `php-check` runs `php -l` and PHP_CodeSniffer on staged PHP files. `build` runs `npm run build` when files in `src/` are staged and stages the rebuilt `build/`, so source and built output are committed together. The hook is skipped during merges and rebases, and it stops the commit when `src/` has unstaged or untracked files or when `git commit <file>` is used with source files. `npm install` installs the hook.
- Added `phpcs.xml.dist`, the PHP_CodeSniffer ruleset for the WordPress Coding Standards. It checks PHP files only and skips `vendor/`, `node_modules/` and `build/`. Formatting sniffs that the existing code breaks are switched off and a few findings are warnings, so existing code passes. Security sniffs stay on everywhere, with two documented false positives suppressed inline.
- Added `.github/reports/lefthook-setup.md` with install steps, everyday commands, how to bypass the hook, known limits and verification results.
- Added a **Settings → LightSpeed** page (via Secure Custom Fields) with a "Post Types" tab that lists every post type found in `scf-json/` as a checkbox. Plugin post types, including Portfolio, are now disabled by default and must be enabled there after activation; rewrite rules are flushed on save. Taxonomies defined in `scf-json/` follow their post type, and the Portfolio slug fields on the Permalinks screen only show while Portfolio is enabled. See LS-4288.
- Added 8 more icons to the `lightspeed` icon collection — `circle`, `coins`, `hard-drives`, `chart-bar`, `users-three`, `paper-plane-tilt`, `git-fork`, `chat-circle-dots` — found missing while cross-checking design mockups against the existing set. See [#26](https://github.com/lightspeedwp/ls-plugin/pull/26).
- Added 5 remaining icons to the `lightspeed` icon collection — `notepad`, `clipboard-text`, `github`, `rocket-launch`, `buildings` — closing out LS-3229's "Missing icons" tracking list.
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
- Changed the `phpcs` and `phpcbf` Composer scripts to use `phpcs.xml.dist` instead of `--standard=WordPress .`.
- Changed `.gitignore` to ignore `lefthook-local.yml` and `.lefthook-local.yml` for personal hook overrides.
- Changed the `phpcs:ignore` comment in `inc/class-permalinks.php` to name the correct sniff (`ValidatedSanitizedInput`), and added inline `phpcs:ignore` comments for two known false positives in `src/blocks/search-filter/render.php`. The built copy in `build/` is updated to match.
- Changed the Portfolio post type (`project`) to be disabled by default. Existing sites must enable it under **Settings → LightSpeed → Post Types** after updating, or Portfolio content, its taxonomies (Industry & Software, Service, Project types) and its permalink settings will be hidden. See LS-4288.
- Changed Back to Top implementation from a standalone custom block to a `core/button` variation.
- Changed Back to Top frontend targeting to use a dedicated wrapper class (`is-back-to-top`) for reliable JS and CSS behaviour.
- Changed Back to Top visibility to always display (removed scroll-threshold hide/show behaviour).
- Changed linkable block source assets to load from `src/` and updated build configuration to match the new asset layout.
- Changed the plugin bootstrap to load SCF JSON configuration, validation, and permalink management classes.
- Changed Search Filter block interactivity handling to use a dedicated view script module with debounced query updates and Query Loop integration hooks.
- Changed Taxonomy Filter block interactivity handling to use dedicated view script module with client-side navigation and per-instance state management for expand/collapse controls.
- Changed all `inc/` classes to be namespaced under `LS_Plugin\`, dropping the `LS_Plugin_` prefix from class names (e.g. `LS_Plugin_Carousel` → `LS_Plugin\Carousel`), and renamed the corresponding files to match (e.g. `class-ls-plugin-icons.php` → `class-icons.php`).
- Changed the lint setup so `npm run lint` passes in CI: Stylelint now checks only `src/**/*.{css,scss}` and ignores the compiled `style.css` and `.map` files via `.stylelintignore`, the `src/` JavaScript and SCSS were auto-fixed or corrected to meet the lint rules, and `build/` is rebuilt to match. See [#30](https://github.com/lightspeedwp/ls-plugin/pull/30).

### Deprecated

### Removed
- Removed the standalone Back to Top block source in favour of variation-based implementation.

### Fixed
- Fixed `composer run phpcs` running out of memory. The default run also read JavaScript and CSS files, including the minified Swiper bundle. It now checks PHP files only.
- Fixed the `lightspeed/dot` icon (`assets/icons/lightspeed/dot.svg`) rendering too small: its circle only had a `r="28"` radius in a `256×256` viewBox (~22% coverage), leaving excessive transparent padding. Enlarged the circle to fill the viewBox, drawn as a `path` (`d="M0,128A128,128,0,1,0,256,128A128,128,0,1,0,0,128Z"`) rather than a `<circle>` element, since `WP_Icons_Registry`'s `wp_kses()` sanitizer only allows `svg`, `path`, and `polygon` tags and silently strips `circle`/`rect`. Matches the intended eyebrow-bullet proportion used across the footer, homepage, blog, work, 404, and Services page. See [#25](https://github.com/lightspeedwp/ls-plugin/pull/25). (LS-4168)
- Fixed the Portfolio archive (`/portfolio/`) 404ing on the consuming theme: a prior commit had accidentally reintroduced the pre-#18 `ls_plugin_portfolio`/`ls_plugin_portfolio_industry`/`ls_plugin_portfolio_service` CPT/taxonomy naming from a stale local SCF-JSON export, reverting [#18](https://github.com/lightspeedwp/ls-plugin/pull/18)'s deliberate rename to `project`/`project-group`/`project-tag`/`project-type` (which matches LIVE and what `ls-theme` is built against). Restored `scf-json/post_type_portfolio.json` and `scf-json/group_ls_plugin_portfolio_fields.json` to #18's naming (verified byte-for-byte identical) and removed the stale duplicate `taxonomy-portfolio-industry.json`/`taxonomy-portfolio-service.json` files. See [#23](https://github.com/lightspeedwp/ls-plugin/pull/23).
- Fixed the Portfolio archive restoration above requiring a manual Permalinks resave to take effect: `inc/class-permalinks.php` now flushes rewrite rules once, automatically, keyed to a versioned option (`ls_plugin_portfolio_rewrite_flushed_version`), so any environment that had cached rewrite rules under the reverted `ls_plugin_portfolio` registration self-heals on its next request instead of silently continuing to 404.
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
