# .github

This folder contains GitHub-native AI and repository workflow infrastructure for {{PLUGIN_NAME}}.

---

## Folder structure

| Folder / File | Purpose |
|---|---|
| `copilot-instructions.md` | Primary GitHub Copilot instruction file for this repo |
| `instructions/` | File-type-specific Copilot guidance files |
| `prompts/` | Reusable GitHub Copilot prompt files for repeatable workflows |
| `reports/` | Developer and AI-generated reports (not end-user docs) |
| `tasks/` | Task lists and AI-maintained work tracking |
| `workflows/` | GitHub Actions CI/CD workflows |

---

## Usage

- AI agents: read `AGENTS.md` first, then `.github/copilot-instructions.md`.
- Developers: see `prompts/` for useful starting prompts for common tasks.
- Reports and task lists: see `reports/README.md` and `tasks/README.md`.
- Workflows are triggered automatically via GitHub Actions.

---

## What does NOT belong in `.github/`

- End-user documentation → `docs/`
- Plugin source code → `src/`, `inc/`, `blocks/`
- Static assets → `assets/`
