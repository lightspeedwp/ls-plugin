---
applyTo: "**/*.php"
---

# PHP Instructions

## Coding standards

- Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).
- Use **tabs** for indentation (not spaces).
- Opening braces go on the same line for control structures.
- Use `true`, `false`, `null` (lowercase).

## Escaping output

Always escape before output. Match the escaping function to the context:

```php
echo esc_html( $text );             // Plain text output
echo esc_attr( $attribute );        // HTML attribute values
echo esc_url( $url );               // URLs in href/src
echo esc_html__( 'String', '{{TEXT_DOMAIN}}' );  // Translated plain text
echo wp_kses_post( $html );         // HTML content (limited tags)
```

Never echo unescaped variables or user input.

## Sanitising input

Sanitise all external input before storing or using it:

```php
$text  = sanitize_text_field( wp_unslash( $_POST['field'] ) );
$url   = esc_url_raw( wp_unslash( $_POST['url'] ) );
$int   = absint( $_POST['number'] );
$email = sanitize_email( $_POST['email'] );
$html  = wp_kses_post( wp_unslash( $_POST['content'] ) );
```

## Translation

Wrap all user-facing strings:

```php
esc_html__( 'String', '{{TEXT_DOMAIN}}' )
esc_html_e( 'String', '{{TEXT_DOMAIN}}' )
esc_attr__( 'String', '{{TEXT_DOMAIN}}' )
```

## Security

- Check nonces with `check_ajax_referer()` or `wp_verify_nonce()` before processing form submissions.
- Check capabilities with `current_user_can()` before privileged operations.
- Use `$wpdb->prepare()` for all database queries with dynamic values.
- Never use `eval()`.

## File structure

- PHP includes go in `inc/`.
- Class files: `inc/class-{{plugin-slug}}-name.php`.
- Load includes from the main plugin file via `plugins_loaded`.
