# Lefthook setup (LSA-191, ls-plugin)

Lefthook runs checks on `git commit` so broken PHP and stale built assets do not reach a PR. Config: `lefthook.yml`. Reference: https://lefthook.dev/configuration/

## Install

```bash
npm install      # installs Lefthook and installs the Git hook
composer install # installs phpcs, which the PHP check needs
```

`lefthook` is a devDependency. Its postinstall step installs the hook. To check or repair it:

```bash
npx lefthook check-install   # exit 0 = installed and in sync
npx lefthook install         # re-install the hook
```

## What runs on `pre-commit`

The jobs run in order (`piped`), each only when staged files match, and the hook is skipped during merges and rebases.

| Job | Runs when staged files match | Command | Effect |
|---|---|---|---|
| `php-check` | `*.php` (not `build/` or `vendor/`) | `php -l` on each file, then `vendor/bin/phpcs` on the staged files | A syntax error or a phpcs error blocks the commit. Warnings are printed but do not block. |
| `build` | `src/**`, `webpack.config.cjs` | `npm run build`, then `git add -A -- build` | Rebuilds `build/` and stages it, so source and built output are committed together. A build error blocks the commit. |

### PHP ruleset (`phpcs.xml.dist`)

PHP files only, using the WordPress Coding Standards. The plugin's earlier `phpcs --standard=WordPress .` also read JavaScript and CSS and ran out of memory on the minified Swiper bundle in `assets/`. The Composer scripts `phpcs` and `phpcbf` now use this ruleset, so `composer run phpcs` and CI work on the whole repo in about 1.5 seconds.

The existing code had 119 errors and 46 warnings. So that existing files can be edited and committed:

- Switched off: 13 formatting sniffs that phpcbf can fix automatically (array spacing, short array syntax, trailing whitespace, function call layout and similar).
- Warnings only: 8 findings that need a human decision (translators comments, Yoda conditions, global variable overrides and similar).
- Security sniffs stay on as errors everywhere, with no file-wide exclusions. The two known false positives are suppressed inline, with a reason, on those statements only: the two `echo` lines in `src/blocks/search-filter/render.php` (`get_block_wrapper_attributes()` and `wp_interactivity_data_wp_context()` return escaped markup) and the `$_POST` read in `inc/class-permalinks.php` (sanitised by `sanitize_permalink_fields()` on the next line; the existing `phpcs:ignore` there named the wrong sniff and is corrected). A new violation anywhere else in those files is still caught.
- Result: 0 errors and 70 warnings across the repo. `ignore_warnings_on_exit` is set, so warnings never fail a run.

To clean up later, run `vendor/bin/phpcbf`, then remove the matching `<exclude>` lines from `phpcs.xml.dist` one at a time.

### The build guard

`npm run build` compiles every file in `src/` from the working tree. The `build` job therefore stops before building, with a message that lists the files, when:

- any file under `src/` (or `webpack.config.cjs`) has unstaged changes, because `build/` would be staged with output from a file that is not in the commit;
- there are untracked files under `src/`, because `git diff` does not see new files, and a staged file could import one that is not committed;
- the commit uses a file path (`git commit <file>` or `--only`), because Git runs hooks for those against a temporary index, so the staged `build/` would be committed and then dropped from your real index, leaving a staged revert.

Plain `git commit`, `git commit -a`, `--include` and `--amend` all work. A file that is only partly staged is fine: Lefthook hides its unstaged part during the hook.

`webpack.config.cjs` sets `clean: true`, so `build/` is entirely generated and the whole folder is staged, including files the build no longer produces. Deleting a source file also triggers the job: the build runs, fails if something still imports the file, and otherwise stages the removal of its built output. Do not edit files in `build/` by hand: the next hook run overwrites them.

Some `src/**/style.css` and `.css.map` files are committed next to their `.scss` source, probably from an editor's Sass compiler. They count as source for the guard, so stage them with the `.scss` file.

## Everyday commands

```bash
npx lefthook run pre-commit                       # run the hook on staged files
npx lefthook run pre-commit --job php-check       # run one job
npx lefthook run pre-commit --file inc/foo.php    # run against a named file
composer run phpcs                                # check all PHP
composer run phpcbf                               # fix what can be fixed automatically
npx lefthook dump                                 # show the merged config
LEFTHOOK_VERBOSE=1 git commit ...                 # debug output
```

## Bypassing

```bash
git commit --no-verify      # skip hooks for one commit
LEFTHOOK=0 git commit ...   # disable Lefthook for one command
```

Personal overrides go in `lefthook-local.yml` (gitignored).

## Known limits

- The hook needs `php`, `npm` and `node` on the `PATH`. A Git client that does not load your shell environment can block commits with a message such as `php was not found on the PATH`. Commit from a terminal, or use `git commit --no-verify` for that commit.
- The webpack entries are listed by hand in `webpack.config.cjs`. A new entry has to be added there, and block entries are found from `block.json`.
- ESLint, Stylelint and the JSON lint are not part of this hook. They report existing errors (305 in JS, 39 in SCSS) and the `lint:json` script does not work (`--ext` is not an option of `wp-scripts lint-md-docs`, and `|| true` hides the error). They are left for a separate change.

## Verification (2026-10-20)

Run as real `git commit` commands in a scratch worktree with its own `node_modules`.

| Case | Result |
|---|---|
| Valid PHP edit | Passes |
| PHP edit with a phpcs error (unescaped output) | Commit blocked, errors listed |
| PHP syntax error | Commit blocked, parse error shown |
| Edit to a PHP file that has only warnings | Passes, warnings printed |
| Only a `build/` PHP copy staged | Both jobs skip |
| JS change in `src/` | Build runs; the commit holds the source file and its own build output (`.js` and `.asset.php`) |
| New module added and imported | Passes, both source files and the build output are committed |
| `src/` render.php edit | `php-check` and `build` both run; `build/` gets the copy |
| PHP and JS staged together | Both jobs pass |
| Staged file plus an unrelated unstaged `src/` file | Commit blocked, the file is listed |
| Staged file plus an untracked `src/` file | Commit blocked, the file is listed |
| `git commit <file>` with source staged | Commit blocked with a message to `git add` first |
| `git commit -a`, `--include <file>`, `--amend` | Pass, with the rebuilt output in the commit |
| JS syntax error | Commit blocked, webpack error shown, `build/` left intact |
| Hand edit in `build/` plus a staged source change | The hand edit is overwritten and not committed |
| Commit with no PHP or `src/` files | Both jobs skip |
| New unescaped output added to `search-filter/render.php`, or new unsanitised input to `class-permalinks.php` | Commit blocked (the inline ignores cover only the two documented statements) |
| Harmless edit to either of those two files | Passes |
| Delete a file that is still imported | Build runs, fails with `Module not found`, commit blocked |
| Delete a whole block (`git rm -r src/blocks/search-filter`) | Build runs, and the commit includes the removal of its files from `build/` |
| Delete a source file the build does not use (`style.css.map`), staged or via `commit -a` | Build runs and passes, commit holds only the deletion |
| Minimal `PATH` (no `php`, no `npm`) | Blocked with a "not found on the PATH" message |
| Pure rebuild with no source change | No difference from the committed `build/` |
| `composer run phpcs`, `composer run phplint`, `plugin:validate`, `schema:validate`, `security:scan` | All pass |
| `npm audit` | 122 findings on this branch and on `develop`; Lefthook adds none |

## Uninstall

```bash
npx lefthook uninstall
```
