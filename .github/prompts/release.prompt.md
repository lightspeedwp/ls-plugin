# Release Prompt

Prepare a new plugin release.

## Instructions

Perform the following steps to prepare a release:

### 1. Review changes
- Review `git log` or `git diff` since the last release.
- Identify all significant changes (features, fixes, security updates).

### 2. Update CHANGELOG.md
- Move all entries from `[Unreleased]` to a new versioned section.
- Use the format `## [X.Y.Z] - YYYY-MM-DD`.
- Follow [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) conventions.
- Add a compare link at the bottom of `CHANGELOG.md`.

### 3. Update version numbers
Update the version number in:
- `{{PLUGIN_SLUG}}.php` — `Version:` header field
- `{{PLUGIN_SLUG}}.php` — `{{NAMESPACE}}_VERSION` constant
- `package.json` — `version` field
- `readme.txt` — `Stable tag:` field

### 4. Validate
Run:
```bash
npm run plugin:validate
npm run security:scan
composer run phpcs
npm run lint
```

Fix any issues before tagging.

### 5. Commit and tag
```bash
git add .
git commit -m "Release v{VERSION}"
git tag v{VERSION}
git push && git push --tags
```

## Notes

- Follow [SemVer](https://semver.org/): MAJOR.MINOR.PATCH.
- Security fixes should be PATCH or MINOR releases with a `Security` changelog entry.
- Do not release with unreplaced `{{PLACEHOLDER}}` tokens.
