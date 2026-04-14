# Changelog

All notable changes to this plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Added a Style Switcher block with selectable theme style variations and configurable icon display behaviour.
- Added a Button Icon selector panel for core Button blocks, including left/right positioning and up/down icon options.
- Added a Back to Top option as a `core/button` variation so users inherit native Button styling controls and icon compatibility.
- Added smooth scrolling support for Back to Top button clicks and internal anchor links using vanilla JavaScript.

### Changed
- Changed Back to Top implementation from a standalone custom block to a `core/button` variation.
- Changed Back to Top frontend targeting to use a dedicated wrapper class (`is-back-to-top`) for reliable JS and CSS behaviour.
- Changed Back to Top visibility to always display (removed scroll-threshold hide/show behaviour).

### Deprecated

### Removed
- Removed the standalone Back to Top block source in favour of variation-based implementation.

### Fixed
- Fixed editor runtime errors from invalid React component handling in Back to Top editor integration.
- Fixed strict mode error in Back to Top animation loop by replacing `arguments.callee` with a named animation step.

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
