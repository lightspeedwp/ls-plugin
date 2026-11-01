# LightSpeed Icon Collection

SVG source files for the `lightspeed` WordPress 7.1 icon collection, registered by
[`inc/class-icons.php`](../../../inc/class-icons.php) (`LS_Plugin\Icons`). Every `.svg`
file in this directory is auto-registered on `init` as `lightspeed/{filename}`, so
adding, renaming, or removing a file here changes what's available in the block
editor's icon picker and to `wp_get_icon( 'lightspeed/{name}' )` — no PHP changes needed.

## Source

Icons are drawn from [Phosphor Icons](https://phosphoricons.com/)
([github.com/phosphor-icons/core](https://github.com/phosphor-icons/core)), MIT licensed,
copyright (c) Phosphor Icons.

Each file has been reduced to a single `<svg viewBox="0 0 256 256"><path fill="currentColor" d="..."/></svg>`
— the `regular` (or `fill`, for `star-fill.svg`) weight, with `fill="currentColor"` moved
from the `<svg>` root onto the `<path>`. This matches the WordPress 7.1 icon sanitizer,
which only allows `fill` on `<path>`/`<polygon>` elements, not on `<svg>` — putting it on
the outer element would otherwise be stripped, leaving the icon defaulting to solid black
instead of following the surrounding text color.

## Adding more icons

1. Pick an icon from [phosphoricons.com](https://phosphoricons.com/), preferably the
   `regular` weight — Phosphor's fill-based paths pass the WordPress sanitizer cleanly,
   unlike stroke-based icon sets.
2. Save it here as `{icon-name}.svg`, with `fill="currentColor"` on the `<path>`
   (not the `<svg>` root).
3. That's it — `LS_Plugin\Icons` picks it up automatically on the next request.
