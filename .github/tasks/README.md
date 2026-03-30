# .github/tasks

This folder contains task lists and AI-maintained work tracking for {{PLUGIN_NAME}}.

---

## Files

| File | Purpose |
|---|---|
| `task-list.md` | Current task list — update as work progresses |
| `.gitkeep` | Keeps the folder tracked by Git when empty |

---

## Naming conventions

| File type | Format | Example |
|---|---|---|
| Main task list | `task-list.md` | `task-list.md` |
| Milestone task list | `tasks-{milestone}.md` | `tasks-v1.0.0.md` |
| Sprint task list | `tasks-{date}.md` | `tasks-2025-06.md` |

---

## When to create task lists

- After running an audit prompt — create tasks from findings.
- When planning a new feature or milestone.
- When an AI agent identifies work that requires human review.

---

## How AI agents should use this folder

- After generating a report in `.github/reports/`, create or update `task-list.md`.
- Mark tasks as `[ ]` (pending) or `[x]` (completed).
- Add a reference to the report that generated each task.
- Do not delete completed tasks — mark them done so there is a history.

---

## How tasks relate to reports

- Reports live in `.github/reports/`.
- Tasks live in `.github/tasks/`.
- A task entry should reference the report it came from where applicable.

Example:

```markdown
- [ ] Fix unescaped output in `inc/class-example.php:42` — see `reports/audit-2025-06-01.md`
```
