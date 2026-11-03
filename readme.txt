=== LightSpeed Site Plugin ===
Contributors: LightSpeed
Tags: blocks, gutenberg, ls-plugin
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

LightSpeed Site Plugin provides custom blocks and site-specific functionality for the LightSpeed website, separate from theme responsibilities.

== Description ==

LightSpeed Site Plugin provides custom blocks and site-specific functionality for the LightSpeed website, separate from theme responsibilities.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen in WordPress.
3. See `docs/` for further usage notes.

== Changelog ==

= 1.0.0 =
* Breaking: plugin classes are now namespaced under `LS_Plugin\`; the old `LS_Plugin_` class names and `class-ls-plugin-*.php` files are gone.
* Breaking: plugin post types, including Portfolio, are disabled by default. Enable them under Settings > LightSpeed > Post Types.
* Breaking: the standalone Back to Top block is removed. Back to Top is now a variation of the core Button block.
* New: Settings > LightSpeed page, Carousel, Search Filter, Taxonomy Filter and Style Switcher blocks, Button Icon and Linkable Blocks, Lightspeed icon collection, and Yoast noindex exclusion from site search.
* Fixed: Portfolio archive 404 and permalinks, the Linkable Blocks stylesheet 404, and several editor and front-end issues.
* Lint and build checks now pass in CI.
* See CHANGELOG.md and docs/migrating-to-1.0.md for the full list and upgrade steps.

= 0.1.0 =
* Initial release.
