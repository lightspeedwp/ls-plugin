# SKILL: Block Plugin Audit

Audit a WordPress block plugin for code quality, security, and accessibility.

---

## When to use this skill

Use this skill when:
- Reviewing a plugin before a release.
- Onboarding onto an existing plugin codebase.
- Requested to produce a plugin audit report.
- Running the `audit-plugin.prompt.md` prompt.

---

## Inputs

- The plugin repository root directory.
- Optional: specific files or folders to focus on.

---

## Steps

### 1. Review plugin structure

- Confirm the main plugin file exists and has a valid WordPress plugin header.
- Confirm `defined( 'ABSPATH' )` direct access protection is present.
- Confirm plugin constants are defined correctly.
- Confirm includes are loaded via `plugins_loaded`.
- Confirm the text domain matches the plugin slug.

### 2. PHP security review

- Scan all PHP files for unescaped output.
- Check for unsanitised `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE` usage.
- Check for missing nonce verification on form submissions.
- Check for missing capability checks before privileged operations.
- Check for unsafe database queries (missing `$wpdb->prepare()`).
- Flag any use of `eval()`, `exec()`, `system()`, or `shell_exec()`.

### 3. Block quality review (if blocks present)

- Confirm each block has a valid `block.json`.
- Confirm blocks are registered using `register_block_type()` with `block.json`.
- Confirm PHP render callbacks escape all dynamic output.
- Confirm block patterns use safe escaping.
- Confirm `block.json` files have `$schema`, `name`, `title`, `category`, and `textdomain`.

### 4. Translation review

- Confirm all user-facing strings use translation functions.
- Confirm the correct text domain is used in all translation function calls.

### 5. Accessibility review

- Review block edit and save components for:
  - Semantic HTML elements
  - ARIA attributes where needed
  - Keyboard navigation support
  - Sufficient colour contrast (flag for manual review)
- Review frontend-rendered output for accessibility markers.

### 6. General quality

- Check for unreplaced `{{PLACEHOLDER}}` tokens.
- Check that `CHANGELOG.md` is up to date.
- Check for unused files or stale commented-out code.

---

## Outputs

1. Write an audit report to `.github/reports/audit-YYYY-MM-DD.md`.
2. Create or update `.github/tasks/task-list.md` with actionable tasks from findings.

---

## Quality criteria

A passing audit has:
- No unescaped output in PHP files.
- No unsanitised input used directly.
- Valid `block.json` for every registered block.
- No unreplaced placeholder tokens.
- `CHANGELOG.md` reflects the current state of the plugin.
