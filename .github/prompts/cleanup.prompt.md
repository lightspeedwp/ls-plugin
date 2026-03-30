# Cleanup Prompt

Review the specified file and clean it up.

## Instructions

1. Review the file for:
   - Unused variables, functions, or imports
   - Dead or commented-out code that should be removed
   - Inconsistent indentation or formatting
   - Missing or outdated doc comments
   - Strings not wrapped in translation functions
   - PHP output that is not escaped

2. Apply fixes using the project's coding standards:
   - PHP: WordPress Coding Standards (tabs, WordPress escaping, translation)
   - JS: `@wordpress/scripts` eslint config
   - CSS: `@wordpress/scripts` stylelint config

3. Do not change logic — only clean up formatting, escaping, and documentation.

## File to review

> Replace this line with the path to the file you want to clean up.
> Example: `inc/class-my-plugin-example.php`
