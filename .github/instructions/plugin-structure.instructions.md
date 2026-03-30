---
applyTo: "**"
---

# Plugin Structure Instructions

## Root files

| File | Purpose |
|---|---|
| `{{PLUGIN_SLUG}}.php` | Main plugin bootstrap — valid WordPress plugin header required |
| `uninstall.php` | Plugin uninstall handler — keep conservative, no data deletion by default |
| `plugin-utils.mjs` | Plugin validation and utility CLI |
| `package.json` | Node scripts and dev dependencies |
| `composer.json` | PHP quality tooling |
| `AGENTS.md` | AI agent guidance — read this first |
| `CHANGELOG.md` | Version history — follow Keep a Changelog + SemVer |
| `README.md` | Human-readable root documentation |

## Main plugin file rules

- Must contain a valid WordPress plugin header.
- Must include `defined( 'ABSPATH' ) || exit;` direct access protection.
- Define plugin constants: `{{NAMESPACE}}_VERSION`, `{{NAMESPACE}}_PLUGIN_DIR`, `{{NAMESPACE}}_PLUGIN_URL`.
- Load includes via `plugins_loaded` — not at the top level.
- Keep the bootstrap lean — no business logic.

## Directory roles

| Directory | Purpose |
|---|---|
| `inc/` | Optional PHP include files — loaded from main plugin file |
| `src/` | Source files for compilation (blocks, CSS, JS) |
| `blocks/` | Built block assets — output of `npm run build` |
| `assets/` | Static (pre-built) CSS, JS, images, icons |
| `patterns/` | WordPress block patterns (PHP with header comments) |
| `templates/` | Optional block templates and template parts |
| `languages/` | Translation files (.pot, .po, .mo) |
| `docs/` | End-user documentation only |
| `.github/` | GitHub-native workflows, instructions, prompts, reports, tasks |
| `.agents/` | Portable agent skills and personas |

## Placeholder consistency

All `{{PLACEHOLDER}}` tokens must be replaced consistently:
- Text domain = plugin slug everywhere.
- Namespace = consistent uppercase constant prefix.
- Package name = consistent in package.json and composer.json.

## What NOT to do

- Do not put developer reports in `docs/`.
- Do not put built assets in `src/`.
- Do not add Playwright, Storybook, Docker, webpack config, or Vite.
- Do not add a PHP autoloader unless there is a genuine reason.
- Do not add issue templates or pull request templates.
