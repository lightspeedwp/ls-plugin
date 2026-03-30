# GitHub Copilot Instructions

This file configures GitHub Copilot for the {{PLUGIN_NAME}} repository.
Read [`AGENTS.md`](../AGENTS.md) for full agent guidance.

---

## Repo overview

This is a LightSpeed WordPress block plugin starter.
- One plugin, one repo.
- Block-ready but not locked into a large block framework.
- Uses placeholder tokens (`{{DOUBLE_BRACES}}`) throughout — replace before production.

---

## Key conventions

- PHP: tabs for indentation, WordPress coding standards.
- JS/CSS: spaces (2), follow `@wordpress/scripts` defaults.
- Text domain must match the plugin slug everywhere.
- All PHP output must be escaped. All input must be sanitised.
- Use `esc_html__()`, `esc_attr__()`, `esc_url()`, `wp_kses_post()`.

---

## Where things live

| What | Where |
|---|---|
| Main plugin bootstrap | `{{PLUGIN_SLUG}}.php` |
| PHP includes | `inc/` |
| Block source files | `src/blocks/` |
| Built block assets | `blocks/` |
| Static assets | `assets/` |
| Block patterns | `patterns/` |
| Translation files | `languages/` |
| End-user docs | `docs/` |
| Copilot prompts | `.github/prompts/` |
| Developer reports | `.github/reports/` |
| Task lists | `.github/tasks/` |
| Portable skills | `.agents/skills/` |
| Agent personas | `.agents/agents/` |

---

## File-type guidance

See `.github/instructions/` for detailed guidance:

- [`php.instructions.md`](instructions/php.instructions.md) — PHP coding standards and escaping
- [`blocks.instructions.md`](instructions/blocks.instructions.md) — Block development conventions
- [`plugin-structure.instructions.md`](instructions/plugin-structure.instructions.md) — Plugin structure rules
- [`assets.instructions.md`](instructions/assets.instructions.md) — Asset conventions
- [`workflows.instructions.md`](instructions/workflows.instructions.md) — CI/CD and workflow guidance

---

## Security reminders

- Never echo unescaped dynamic values.
- Always sanitise `$_GET`, `$_POST`, `$_REQUEST`, and `$_COOKIE`.
- Use `$wpdb->prepare()` for database queries.
- Validate nonces on form submissions.
- Use capability checks before sensitive operations.

---

## Validation and linting

```bash
npm run plugin:validate    # Validate plugin structure
npm run security:scan      # PHP security scan
composer run phpcs         # PHP coding standards
npm run lint               # JS + CSS + JSON linting
```

---

## Accessibility

- Use semantic HTML elements.
- Add ARIA attributes where native semantics are insufficient.
- Ensure sufficient colour contrast.
- Support keyboard navigation in interactive blocks.
- Use `aria-label` or `aria-labelledby` on interactive controls without visible labels.
