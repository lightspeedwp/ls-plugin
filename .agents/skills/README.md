# .agents/skills

This folder contains portable, reusable agent skills for {{PLUGIN_NAME}}.

---

## What is a skill?

A skill is a focused, self-contained description of how an AI agent should perform a specific task in this repository.
Skills are not personas — they describe *what to do*, not *who to be*.

---

## Structure

Each skill lives in its own folder:

```
skills/
└── {skill-name}/
    └── SKILL.md
```

The `SKILL.md` file describes:
- What the skill does
- When to use it
- Step-by-step instructions
- Expected inputs and outputs
- Quality criteria

---

## Available skills

| Skill | Description |
|---|---|
| `block-plugin-audit/` | Audit a WordPress block plugin for quality, security, and accessibility |

---

## Adding new skills

1. Create a folder under `.agents/skills/` with a descriptive name.
2. Add a `SKILL.md` file following the structure above.
3. Add it to the table in this README.
4. Reference it from `AGENTS.md` if it is commonly needed.
