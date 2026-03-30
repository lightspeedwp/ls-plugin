---
applyTo: ".github/workflows/**"
---

# Workflows Instructions

## GitHub Actions workflows

| Workflow | Purpose |
|---|---|
| `ci.yml` | Install dependencies, validate plugin, lint |
| `code-quality.yml` | PHP coding standards and lint checks |
| `release.yml` | Validate plugin scaffold (plugin:validate, security scan, placeholder scan, PHPCS) on release tags |

## Rules

- Keep workflows focused and fast.
- Install Node dependencies with `npm ci`.
- Install Composer dependencies with `composer install --no-interaction`.
- Run `npm run plugin:validate` as part of CI.
- Run `composer run phpcs` as part of CI.
- Do not add Docker, Playwright, or E2E tests unless explicitly required.

## Workflow triggers

- `ci.yml` and `code-quality.yml` should run on `push` and `pull_request` to `main`.
- `release.yml` should run on `push` of tags matching `v*`.

## Secrets

- Do not hardcode credentials in workflow files.
- Use GitHub Actions secrets for any tokens or keys.

## Node setup

Use the `actions/setup-node` action with `.nvmrc` for the Node version:

```yaml
- uses: actions/setup-node@v4
  with:
    node-version-file: '.nvmrc'
    cache: 'npm'
```

## Composer setup

Use the `shivammathur/setup-php` action for PHP:

```yaml
- uses: shivammathur/setup-php@v2
  with:
    php-version: '8.2'
    tools: composer
```
