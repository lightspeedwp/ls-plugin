# Migrating to 1.0.0

Version 1.0.0 contains three breaking changes. Check each one before updating a live site. Take a backup first.

## 1. Classes are namespaced

All classes in `inc/` are now under the `LS_Plugin\` namespace, and the `LS_Plugin_` prefix is dropped. Files are renamed to match. There are no aliases for the old names.

| Before | After |
|---|---|
| `LS_Plugin_Carousel` | `LS_Plugin\Carousel` |
| `LS_Plugin_Icons` | `LS_Plugin\Icons` |
| `inc/class-ls-plugin-icons.php` | `inc/class-icons.php` |

**Who is affected:** a theme, child theme or custom plugin that references an `LS_Plugin_*` class or requires one of the old `class-ls-plugin-*.php` files.

**What to do:**

1. Search your custom code for `LS_Plugin_` and `class-ls-plugin-`.
2. Replace each class with its namespaced name (for example `\LS_Plugin\Carousel`) and update any file paths.
3. Load the site and check for fatal errors in `debug.log`.

## 2. Plugin post types are disabled by default

Plugin post types, including Portfolio, are no longer registered until they are enabled. Their taxonomies (Industry & Software, Service, Project types) follow the post type, and so do the Portfolio permalink settings.

**Who is affected:** any site that uses Portfolio or another post type defined in `scf-json/`. After updating, that content, its taxonomies and its permalink settings are hidden.

**What to do:**

1. Make sure Secure Custom Fields is active.
2. Go to **Settings → LightSpeed → Post Types**.
3. Tick each post type the site uses and save. Rewrite rules are flushed on save.
4. Open the Portfolio archive and a single Portfolio item to confirm they load. If you use custom slugs, check them under the Portfolio permalink settings.

## 3. The Back to Top block is removed

The standalone `ls-plugin/back-to-top` block no longer exists. Back to Top is now a variation of the Button block.

**Who is affected:** pages, patterns or templates that contain the old block. They will show it as missing or invalid in the editor.

**What to do:**

1. Search your content, patterns and template parts for `ls-plugin/back-to-top`.
2. Replace each one with a **Button** block and choose the **Back to Top** variation.
3. Set the position mode (inline, sticky or fixed) in the block sidebar, and check the button on the front end, including with reduced motion enabled.

## After updating

- Clear page, object and CDN caches.
- Check the editor and front end for the carousel, search filter, taxonomy filter, style switcher and Back to Top.

See [CHANGELOG.md](../CHANGELOG.md) for everything else in 1.0.0 and [ADR-001](adr/ADR-001-release-1-0-breaking-changes.md) for the reasoning.
