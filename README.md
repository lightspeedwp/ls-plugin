# LightSpeed Site Plugin

> Custom blocks and site-specific functionality for the LightSpeed website — separate from theme responsibilities.

---

## What this repo is

The LightSpeed Site Plugin is the canonical home for custom WordPress blocks and site-specific PHP functionality for [lightspeedwp.agency](https://lightspeedwp.agency/).

It is **not** a theme and does not contain theme-layer concerns.
It is **not** a monorepo.
It is a **single plugin in a single repo**, intentionally lean and block-first.

Responsibilities covered by this plugin:

- Custom Gutenberg blocks specific to the LightSpeed site
- Site-specific PHP functionality that does not belong in the theme
- Shared non-theme behaviour for the LightSpeed website

---

## Requirements

- PHP 8.0+
- WordPress 6.4+
- Node.js 20+ (see `.nvmrc`)
- Composer

---

## Getting started

```bash
# Install Node dependencies
npm install

# Install Composer dependencies
composer install

# Validate the plugin structure
npm run plugin:validate

# Lint all code
npm run lint
composer run phpcs
```

---

## Repo map

```
/
├── ls-plugin.php               Main plugin bootstrap
├── uninstall.php               Plugin uninstall handler
├── plugin-utils.mjs            Plugin validation and utility CLI
├── package.json                Node tooling and scripts
├── composer.json               PHP tooling and standards
├── AGENTS.md                   AI agent guidance (start here for AI)
├── CLAUDE.md                   Claude-specific pointer
├── CHANGELOG.md                Version history
├── docs/                       End-user documentation
├── inc/                        PHP include files
├── src/                        Source files (blocks, CSS, JS)
├── blocks/                     Built/registered block assets
├── assets/                     Static assets (css, js, images, icons)
├── patterns/                   WordPress block patterns
├── templates/                  Block templates (optional)
├── languages/                  Translation files
├── .github/                    GitHub workflows, prompts, reports, tasks
└── .agents/                    Portable agent skills and personas
```

---

## Available commands

### Node

| Command | Description |
|---|---|
| `npm run plugin:validate` | Validate plugin structure and headers |
| `npm run schema:validate` | Validate block.json and JSON files |
| `npm run security:scan` | Scan PHP files for risky patterns |
| `npm run lint` | Run all JS, CSS, and JSON linters |
| `npm run lint:js` | Lint JS source files |
| `npm run lint:css` | Lint CSS source files |
| `npm run lint:json` | Lint JSON files |
| `npm run build` | Build block assets |
| `npm run start` | Watch and build block assets |
| `npm run i18n` | Generate translation POT file |

### Composer

| Command | Description |
|---|---|
| `composer run phpcs` | Check PHP coding standards |
| `composer run phpcbf` | Auto-fix PHP coding standards |
| `composer run phplint` | Check PHP syntax |

---

## Plugin constants

| Constant | Value |
|---|---|
| `LS_PLUGIN_VERSION` | Current plugin version |
| `LS_PLUGIN_PLUGIN_FILE` | Absolute path to `ls-plugin.php` |
| `LS_PLUGIN_PLUGIN_DIR` | Absolute path to the plugin directory |
| `LS_PLUGIN_PLUGIN_URL` | URL to the plugin directory |

---

## AI workflows

| Folder | Purpose |
|---|---|
| `AGENTS.md` | Primary AI agent guidance for this repo |
| `.github/copilot-instructions.md` | GitHub Copilot instructions |
| `.github/instructions/` | File-type-specific Copilot guidance |
| `.github/prompts/` | Reusable GitHub Copilot prompt files |
| `.github/reports/` | Developer and AI-generated reports |
| `.github/tasks/` | Task lists and AI work tracking |
| `.agents/skills/` | Portable agent skills |
| `.agents/agents/` | Agent persona definitions |

---

## Notes

- `composer.lock` is not committed (this is a plugin, not a deployment artefact).
- `package-lock.json` is committed to pin Node dependencies.