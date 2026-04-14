# Changelog

All notable changes to this plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Added a Style Switcher block with selectable theme style variations and configurable icon display behaviour.
- Added a Button Icon selector panel for core Button blocks, including left/right positioning and up/down icon options.
- Added a Back to Top block with smooth scrolling, sticky header detection, and configurable positioning (scroll-triggered or fixed footer).
  - Supports smooth scrolling for all internal anchor links.
  - Respects `prefers-reduced-motion` for accessibility.
  - Two positioning modes: "scroll" (appears after % viewport scroll) and "fixed" (sticky footer).
  - Vanilla JavaScript with zero external dependencies.
  - Includes sticky header offset detection to prevent content overlap.
  - Full keyboard and screen reader support.

### Changed

### Deprecated

### Removed

### Fixed

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
