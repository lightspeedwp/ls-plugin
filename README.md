# {{PLUGIN_NAME}}

> LightSpeed WordPress block plugin starter — lean, block-ready, AI-workflow-aware.

---

## What this repo is

A production-aware starter template for building custom WordPress plugins at LightSpeed.
It is **not** a WordPress.org submission starter and does not include submission-specific bureaucracy.

It is intentionally lighter than `block-plugin-scaffold` while still providing:

- a valid WordPress plugin skeleton
- a block-ready folder structure
- practical PHP and JS linting and validation
- AI-ready workflows with prompts, reports, tasks, and agent personas
- clear placeholder conventions throughout

---

## Who it is for

LightSpeed developers building custom WordPress plugins — for client and commercial work.
Also suitable for use by AI agents working within a structured, well-documented repo.

---

## What it includes

| Area | Description |
|---|---|
| `{{PLUGIN_SLUG}}.php` | Main plugin bootstrap file |
| `uninstall.php` | Safe plugin uninstall stub |
| `inc/` | Optional PHP include files |
| `src/` | Source files for block and plugin assets |
| `blocks/` | Built or registered block asset directories |
| `assets/` | Static CSS, JS, images, and icons |
| `patterns/` | WordPress block patterns |
| `templates/` | Optional block templates |
| `languages/` | Translation files |
| `docs/` | End-user documentation |
| `.github/` | GitHub-native AI and workflow infrastructure |
| `.agents/` | Portable agent skills and personas |

---

## Requirements

- PHP 8.0+
- WordPress 6.4+
- Node.js 20+ (see `.nvmrc`)
- Composer

---

## Quick start

```bash
# 1. Use this template via GitHub (recommended):
#    Click "Use this template" on the GitHub repo page,
#    or clone and replace placeholders manually:
git clone https://github.com/{{GITHUB_ORG}}/{{REPO_NAME}}.git my-plugin
cd my-plugin

# 2. Replace all {{PLACEHOLDER}} tokens (see Customise placeholders below)
#    Rename {{PLUGIN_SLUG}}.php to match your actual plugin slug.

# 3. Install Node dependencies
npm install

# 4. Install Composer dependencies
composer install

# 5. Validate the plugin scaffold
npm run plugin:validate

# 6. Lint your code
npm run lint
composer run phpcs
```

---

## Customise placeholders

Search and replace these tokens across the entire repo before starting work:

| Placeholder | Example value |
|---|---|
| `{{PLUGIN_NAME}}` | `My Awesome Plugin` |
| `{{PLUGIN_SLUG}}` | `my-awesome-plugin` |
| `{{TEXT_DOMAIN}}` | `my-awesome-plugin` |
| `{{PLUGIN_URI}}` | `https://example.com/plugins/my-awesome-plugin` |
| `{{PLUGIN_DESCRIPTION}}` | `A useful WordPress plugin.` |
| `{{AUTHOR_NAME}}` | `LightSpeed` |
| `{{AUTHOR_URI}}` | `https://lightspeedwp.agency` |
| `{{NAMESPACE}}` | `MY_AWESOME_PLUGIN` |
| `{{PACKAGE_NAME}}` | `lightspeedwp/my-awesome-plugin` |
| `{{REPO_NAME}}` | `my-awesome-plugin` |
| `{{GITHUB_ORG}}` | `lightspeedwp` |

Also rename `{{PLUGIN_SLUG}}.php` to match your actual plugin slug.

---

## Repo map

```
/
├── {{PLUGIN_SLUG}}.php         Main plugin bootstrap
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

## What to edit first

1. Replace all placeholders (see table above).
2. Rename `{{PLUGIN_SLUG}}.php` to your plugin slug.
3. Update `CHANGELOG.md` with your real start date.
4. Update `docs/README.md` with your plugin's documentation structure.
5. Update `CODEOWNERS` with real GitHub usernames.
6. Add your PHP includes to `inc/` and load them from the main plugin file.
7. Add blocks to `src/blocks/` and register them in the main plugin file.

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

- This repo is **not** packaged specifically for WordPress.org submission.
- `composer.lock` is not committed (this is a plugin, not a deployment artefact).
- `package-lock.json` is committed to pin Node dependencies.