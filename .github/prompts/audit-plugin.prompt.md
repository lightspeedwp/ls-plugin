# Audit Plugin Prompt

Perform a quality and security audit of this WordPress plugin.

## Instructions

Review the following areas and produce a structured report:

### 1. Plugin structure
- Is the main plugin file correctly structured with a valid header?
- Is ABSPATH protection present?
- Are constants defined correctly?
- Are includes loaded correctly via `plugins_loaded`?

### 2. PHP quality
- Is all output correctly escaped?
- Is all input correctly sanitised and validated?
- Are nonces used on form submissions?
- Are capability checks in place before privileged operations?
- Are translation functions used for all user-facing strings?

### 3. Block quality (if blocks are present)
- Is each block registered with a valid `block.json`?
- Is `block.json` used for asset declaration instead of manual enqueuing?
- Are PHP-rendered block callbacks escaping all output?
- Are block patterns using safe escaping?

### 4. JavaScript quality
- Are there any hardcoded strings that should be translated?
- Are there any obvious accessibility issues?

### 5. General
- Are any `{{PLACEHOLDER}}` tokens still unreplaced?
- Is the `CHANGELOG.md` up to date?
- Are there any unused files or folders?

## Output format

Write the audit report to `.github/reports/audit-YYYY-MM-DD.md`.
Create a task list in `.github/tasks/task-list.md` based on findings.
