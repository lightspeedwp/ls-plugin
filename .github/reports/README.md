# .github/reports

This folder contains **developer and AI-generated reports** for {{PLUGIN_NAME}}.

---

## What belongs here

- Plugin audit reports
- Security scan reports
- Code quality reports
- AI-generated analysis outputs
- Accessibility audit reports

## What does NOT belong here

- End-user documentation → `docs/`
- Task lists → `.github/tasks/`

---

## Naming conventions

Use the following format for report filenames:

```
{type}-YYYY-MM-DD.md
```

Examples:
- `audit-2025-06-01.md`
- `security-scan-2025-06-01.md`
- `accessibility-audit-2025-07-15.md`

For multiple reports of the same type in one period, append a short descriptor:

```
audit-2025-06-01-blocks.md
audit-2025-06-01-php.md
```

---

## Creating reports

When an AI agent runs a prompt from `.github/prompts/audit-plugin.prompt.md` or similar:
1. Write the report to this folder using the naming convention above.
2. Summarise findings clearly under headings.
3. Create follow-up tasks in `.github/tasks/task-list.md`.

---

## Subfolders (optional)

For larger repos, reports may be organised by month or category:

```
reports/
├── 2025-06/
│   ├── audit-2025-06-01.md
│   └── security-scan-2025-06-15.md
└── 2025-07/
    └── audit-2025-07-01.md
```

This is optional — keep it flat if the volume is low.
