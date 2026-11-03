---
title: 'ADR-001: Release 1.0.0 breaking changes'
description: Decision to ship three breaking changes together in the 1.0.0 major release
file_type: documentation
date: 2026-11-02T00:00:00.000Z
adr_status: accepted
status: active
authors:
  - LightSpeed Team
---

# ADR-001: Release 1.0.0 breaking changes

## Status

✅ **Accepted** — Released in 1.0.0 (Linear LSA-181)

## Context

ls-plugin 1.0.0 is the first release from `main` and ships together with the ls-theme full site redesign. `main` still holds the original starter template (version 0.1.0), so every site that moves to 1.0.0 upgrades across all the work on `develop`. Three changes on `develop` are not backwards compatible with earlier pre-release builds:

1. Classes in `inc/` were renamed from `LS_Plugin_*` to the `LS_Plugin\` namespace.
2. Plugin post types (including Portfolio) became opt-in instead of always registered.
3. The standalone `ls-plugin/back-to-top` block was replaced by a `core/button` variation.

## Decision

Ship all three as breaking changes in a single major release, `1.0.0`, instead of spreading them across several releases or adding compatibility layers.

- **Namespaced classes:** no `class_alias` shims for the old `LS_Plugin_*` names. The old class files are gone.
- **Opt-in post types:** plugin post types and their taxonomies are registered only when enabled under **Settings → LightSpeed → Post Types**. The default is none enabled.
- **Back to Top:** the block is removed. Editors use the Back to Top variation of the Button block.
- Document each change in the `1.0.0` changelog under "BREAKING CHANGES" and in `docs/migrating-to-1.0.md`.

## Rationale

- A single major version is a clear signal that upgrades need attention, and it lines up with the ls-theme redesign release.
- Aliases and a legacy block would add code to maintain for sites that have no earlier stable release to migrate from.
- Opt-in post types keep unused content types, taxonomies and permalink settings out of sites that do not need them.
- Building Back to Top on `core/button` gives users native Button styling controls and icon compatibility.

## Consequences

**Positive**

- Smaller, consistent codebase with PSR-style namespacing.
- Sites only expose the content types they enable.

**Negative**

- Custom code that references the old class names or file paths breaks until updated.
- Portfolio content, its taxonomies and permalink settings are hidden until the post type is enabled.
- Content that used the old Back to Top block must be re-created.

## Alternatives considered

- **Keep `class_alias` shims for one release:** rejected; there is no earlier stable release depending on the old names.
- **Keep the old Back to Top block alongside the variation:** rejected; two ways to build the same control.
- **Enable post types by default and add an opt-out:** rejected; it would register unused content types on every site.

## References

- [CHANGELOG.md](../../CHANGELOG.md) — 1.0.0 breaking changes
- [Migration guide](../migrating-to-1.0.md)
- Linear LSA-181 (release), LS-4288 (post types setting)
