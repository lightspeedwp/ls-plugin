# .github/instructions

This folder contains file-type-specific GitHub Copilot instruction files for {{PLUGIN_NAME}}.

---

## Files

| File | Purpose |
|---|---|
| `php.instructions.md` | PHP coding standards, escaping, and sanitisation |
| `blocks.instructions.md` | WordPress block development conventions |
| `plugin-structure.instructions.md` | Plugin folder structure and architecture rules |
| `assets.instructions.md` | Asset organisation and build conventions |
| `workflows.instructions.md` | CI/CD and GitHub Actions guidance |

---

## How these files work

GitHub Copilot reads these files to understand context for specific file types or workflows.
Reference them from `.github/copilot-instructions.md` using relative links.

To add a new instruction file:
1. Create a file named `{topic}.instructions.md` in this folder.
2. Add a link to it from `.github/copilot-instructions.md`.
