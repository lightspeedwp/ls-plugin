# AGENTS.md — AI Agent Guidance

This is the primary guidance document for AI agents working in this repository.
Read this file first before making any changes.

---

## Repo Purpose

This is a LightSpeed WordPress block plugin starter repository.
It provides a clean, lean scaffold for building custom WordPress plugins — with a bias toward Gutenberg block development.

It is **not** a WordPress.org submission starter.
It is **not** a monorepo.
It is a **single plugin in a single repo**.

---

## Repo Structure

```
/
├── {{PLUGIN_SLUG}}.php         Main plugin bootstrap — rename to match your slug
├── uninstall.php               Plugin uninstall stub — keep conservative
├── plugin-utils.mjs            Node CLI for validation, schema checks, and security scanning
├── package.json                Node scripts and dev dependencies
├── composer.json               PHP quality tooling
├── AGENTS.md                   This file — start here
├── CLAUDE.md                   Claude agent pointer
├── CHANGELOG.md                Version history — follow Keep a Changelog + SemVer
├── README.md                   Human-readable root documentation
├── readme.txt                  Lightweight WordPress distribution placeholder
├── inc/                        PHP include files (optional, loaded from main plugin file)
├── src/                        Source files: src/blocks/, src/css/, src/js/
├── blocks/                     Built or registered block asset directories
├── assets/                     Static assets: assets/css/, assets/js/, assets/images/, assets/icons/
├── patterns/                   WordPress block patterns (PHP files with header comments)
├── templates/                  Optional block templates and template parts
├── languages/                  Translation files (.pot, .po, .mo)
├── docs/                       End-user documentation — NOT developer reports
├── .github/                    GitHub-native workflows, Copilot instructions, prompts, tasks, reports
└── .agents/                    Portable agent skills and personas
```

---

## Plugin-First Development

- This is a **plugin repo** — not a theme, not a monorepo.
- One plugin, one repo. Do not add multi-plugin infrastructure.
- The main plugin file must maintain a valid WordPress plugin header.
- Keep the bootstrap file lean — load includes via `plugins_loaded`, not inline.
- Do not invent a plugin framework. Use WordPress core APIs.

---

## Placeholder Conventions

All placeholder tokens use `{{DOUBLE_BRACES}}` format.
These must be replaced before the plugin is used in production.

| Placeholder | Purpose |
|---|---|
| `{{PLUGIN_NAME}}` | Human-readable plugin name |
| `{{PLUGIN_SLUG}}` | URL-safe slug (lowercase, hyphens) |
| `{{TEXT_DOMAIN}}` | WordPress text domain — must match slug |
| `{{PLUGIN_URI}}` | Plugin URI |
| `{{PLUGIN_DESCRIPTION}}` | One-sentence plugin description |
| `{{AUTHOR_NAME}}` | Author or organisation name |
| `{{AUTHOR_URI}}` | Author website URL |
| `{{NAMESPACE}}` | PHP constant namespace prefix (uppercase, underscores) |
| `{{PACKAGE_NAME}}` | Composer/npm package name |
| `{{REPO_NAME}}` | GitHub repository name |
| `{{GITHUB_ORG}}` | GitHub organisation name |

**Rules:**
- Text domain and slug must always match.
- Namespace must be uppercase and underscore-separated.
- Do not leave required metadata blank — use placeholders.
- Keep placeholders consistent across all files.

---

## PHP Architecture

- Keep PHP minimal unless there is a clear need.
- PHP include files go in `inc/`.
- Load includes from `{{PLUGIN_SLUG}}_init()` via `plugins_loaded`.
- Use WordPress coding standards (tabs for indentation in PHP).
- Do not add a PHP autoloader unless genuinely needed.
- Class files go in `inc/` — name them `class-{{plugin-slug}}-name.php`.

### Escaping output (required)

Always escape output before rendering:

```php
echo esc_html( $variable );
echo esc_attr( $attribute );
echo esc_url( $url );
echo wp_kses_post( $html );
```

Never echo unescaped dynamic content.

### Sanitising input (required)

Sanitise all user input before using it:

```php
$value = sanitize_text_field( wp_unslash( $_POST['field'] ) );
$url   = esc_url_raw( wp_unslash( $_POST['url'] ) );
$int   = absint( $_POST['number'] );
```

### Translation

Always wrap strings for translation:

```php
esc_html__( 'String', '{{TEXT_DOMAIN}}' )
esc_html_e( 'String', '{{TEXT_DOMAIN}}' )
```

---

## Block Development

- Source block files go in `src/blocks/{{block-name}}/`.
- Built block assets go in `blocks/{{block-name}}/`.
- Each block must have a valid `block.json` file.
- Register blocks using `register_block_type()` with the path to `block.json`.
- Use `@wordpress/scripts` for building block assets.
- Block editor assets should be separate from frontend assets.
- PHP-rendered blocks must escape all dynamic output.
- Use `block.json` `supports` and `attributes` rather than bespoke registration logic.

Example block registration:

```php
register_block_type( {{NAMESPACE}}_PLUGIN_DIR . 'blocks/my-block' );
```

---

## Asset Conventions

- **Static assets** (ready-to-use CSS, JS, images, icons): `assets/`
- **Source files** (need compilation): `src/`
- **Built block assets**: `blocks/`
- Do not mix source and built assets in the same folder.
- Frontend CSS and editor CSS should be separate files where appropriate.

---

## Validation and Linting Commands

Run these before committing:

```bash
# Node validation
npm run plugin:validate      # Validate plugin structure and headers
npm run schema:validate      # Validate block.json and JSON files
npm run security:scan        # Scan for risky PHP patterns
npm run lint                 # Run all JS, CSS, and JSON linters

# PHP quality
composer run phpcs           # Check coding standards
composer run phpcbf          # Auto-fix coding standards
composer run phplint         # Check PHP syntax
```

---

## Changelog Expectations

- Follow [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
- Follow [Semantic Versioning](https://semver.org/).
- Every version bump must include a `CHANGELOG.md` entry.
- Keep an `[Unreleased]` section at the top.
- Use sections: `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`.

---

## Documentation Expectations

- `docs/` is for **end-user documentation** — installation guides, editor guides, client notes.
- Do **not** put developer reports in `docs/`.
- Developer reports go in `.github/reports/`.
- Task lists go in `.github/tasks/`.

---

## AI Folder Conventions

| Folder | Purpose |
|---|---|
| `.github/prompts/` | GitHub Copilot prompt files for repeatable workflows |
| `.github/reports/` | Developer and AI-generated reports for this repo |
| `.github/tasks/` | Task lists and AI-maintained work tracking |
| `.github/instructions/` | File-type-specific Copilot guidance |
| `.agents/skills/` | Portable, reusable agent skills |
| `.agents/agents/` | Agent persona definitions |

**Rules for AI agents:**
- Store reports in `.github/reports/` — never in `docs/` or the repo root.
- Store task lists in `.github/tasks/` — update `task-list.md` when work is tracked.
- Store GitHub prompt files in `.github/prompts/`.
- Store portable skills in `.agents/skills/`.
- Store agent personas in `.agents/agents/`.
- Keep prompts focused and short — they are wrappers for repeatable workflows.

---

## Code Quality Rules For AI Agents

- Prefer **small diffs** — avoid large rewrites unless clearly justified.
- Avoid adding new dependencies unless strictly necessary.
- Do not invent a build pipeline — use `@wordpress/scripts` if builds are needed.
- Keep the plugin lean — do not add infrastructure that is not used.
- Do not add Playwright, Storybook, Docker, webpack config, or Vite unless there is a strong reason.
- Escape all PHP output correctly.
- Sanitise and validate all inputs.
- Review block render callbacks and patterns carefully before modifying.
- Do not hardcode plugin-specific values — use constants and placeholders.
- Follow WordPress coding standards.
- Follow accessibility best practices in blocks and templates.
