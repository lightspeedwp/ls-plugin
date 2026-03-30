# .agents

This folder contains portable agent-specific assets for {{PLUGIN_NAME}}.
These assets are designed to be reusable across different AI systems and tools.

---

## Structure

| Folder | Purpose |
|---|---|
| `skills/` | Portable, reusable agent skills — focused tasks agents can perform |
| `agents/` | Agent persona definitions — describe specialist roles agents can adopt |

---

## Difference between `.github/` and `.agents/`

| `.github/` | `.agents/` |
|---|---|
| GitHub-specific (Copilot, Actions, instructions) | Portable across AI systems |
| Workflows, prompts, reports, tasks | Skills and personas |
| Tied to GitHub platform features | Usable in any AI tool or agent framework |

---

## Usage

- AI agents should read `AGENTS.md` first, then consult relevant skills and personas here.
- Skills describe *how* to perform a specific task in this repo context.
- Agents describe *who* the AI should act as for a given type of work.

---

## Adding skills and agents

- Add new skills as folders under `.agents/skills/`, each with a `SKILL.md` file.
- Add new agent personas as Markdown files under `.agents/agents/`.
- Keep skills focused — one skill, one task type.
