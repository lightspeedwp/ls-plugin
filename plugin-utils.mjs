#!/usr/bin/env node
/**
 * plugin-utils.mjs
 *
 * Plugin validation and utility CLI for {{PLUGIN_NAME}}.
 * Run with: node plugin-utils.mjs <command>
 *
 * Commands:
 *   validate-plugin   Validate plugin structure and headers
 *   validate-schema   Validate block.json and JSON schema files
 *   security-scan     Scan PHP files for risky patterns
 *   scan-placeholders Check for unreplaced placeholder tokens
 *   help              Show available commands
 */

import { readFileSync, readdirSync, existsSync, statSync } from 'fs';
import { join, relative } from 'path';

// ─── Helpers ────────────────────────────────────────────────────────────────

const ROOT = process.cwd();

const colours = {
  reset: '\x1b[0m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  green: '\x1b[32m',
  cyan: '\x1b[36m',
  bold: '\x1b[1m',
};

const log = {
  info: ( msg ) => console.log( `  ${msg}` ),
  ok: ( msg ) => console.log( `${colours.green}  ✓${colours.reset} ${msg}` ),
  warn: ( msg ) => console.log( `${colours.yellow}  ⚠ ${msg}${colours.reset}` ),
  error: ( msg ) => console.log( `${colours.red}  ✗ ${msg}${colours.reset}` ),
  heading: ( msg ) => console.log( `\n${colours.bold}${colours.cyan}${msg}${colours.reset}\n` ),
};

/**
 * Recursively find files matching a predicate.
 *
 * @param {string}   dir
 * @param {Function} predicate
 * @param {string[]} results
 * @returns {string[]}
 */
function findFiles( dir, predicate, results = [] ) {
  if ( ! existsSync( dir ) ) return results;

  for ( const entry of readdirSync( dir ) ) {
    const fullPath = join( dir, entry );

    // Skip common large directories.
    if ( [ 'node_modules', 'vendor', '.git', 'build', 'dist' ].includes( entry ) ) {
      continue;
    }

    const stat = statSync( fullPath );
    if ( stat.isDirectory() ) {
      findFiles( fullPath, predicate, results );
    } else if ( predicate( entry, fullPath ) ) {
      results.push( fullPath );
    }
  }

  return results;
}

/**
 * Read a file as a string, return empty string on failure.
 *
 * @param {string} filePath
 * @returns {string}
 */
function readFileSafe( filePath ) {
  try {
    return readFileSync( filePath, 'utf8' );
  } catch {
    return '';
  }
}

// ─── Commands ────────────────────────────────────────────────────────────────

/**
 * validate-plugin
 * Check that required plugin files exist and the main plugin header is valid.
 */
function validatePlugin() {
  log.heading( 'Validating plugin structure…' );

  let errors = 0;
  let warnings = 0;

  // Required files.
  const required = [
    'uninstall.php',
    'README.md',
    'CHANGELOG.md',
    'package.json',
    'composer.json',
    'plugin-utils.mjs',
  ];

  for ( const file of required ) {
    if ( existsSync( join( ROOT, file ) ) ) {
      log.ok( `${file} exists` );
    } else {
      log.error( `Missing required file: ${file}` );
      errors++;
    }
  }

  // Find the main plugin file (a PHP file in root with a plugin header).
  const phpFiles = readdirSync( ROOT ).filter(
    ( f ) => f.endsWith( '.php' ) && f !== 'uninstall.php'
  );

  const mainPluginFile = phpFiles.find( ( file ) => {
    const content = readFileSafe( join( ROOT, file ) );
    return content.includes( 'Plugin Name:' );
  } );

  if ( mainPluginFile ) {
    log.ok( `Main plugin file found: ${mainPluginFile}` );

    const content = readFileSafe( join( ROOT, mainPluginFile ) );

    // Check for ABSPATH protection.
    if ( content.includes( "defined( 'ABSPATH' )" ) || content.includes( "defined('ABSPATH')" ) ) {
      log.ok( 'Direct access protection (ABSPATH check) present' );
    } else {
      log.error( 'Missing ABSPATH direct access protection in main plugin file' );
      errors++;
    }

    // Check for Text Domain header.
    const textDomainMatch = content.match( /Text Domain:\s*(.+)/i );
    if ( textDomainMatch ) {
      const textDomain = textDomainMatch[ 1 ].trim();
      log.ok( `Text Domain: ${textDomain}` );

      // Warn if text domain still contains a placeholder.
      if ( textDomain.includes( '{{' ) ) {
        log.warn( 'Text Domain still contains a placeholder token — replace before use' );
        warnings++;
      }
    } else {
      log.warn( 'Text Domain header not found in main plugin file' );
      warnings++;
    }

    // Check plugin name.
    const pluginNameMatch = content.match( /Plugin Name:\s*(.+)/i );
    if ( pluginNameMatch ) {
      const pluginName = pluginNameMatch[ 1 ].trim();
      if ( pluginName.includes( '{{' ) ) {
        log.warn( `Plugin Name still contains a placeholder: ${pluginName}` );
        warnings++;
      } else {
        log.ok( `Plugin Name: ${pluginName}` );
      }
    }
  } else {
    log.error( 'No main plugin file found in root (a PHP file with "Plugin Name:" header)' );
    errors++;
  }

  // Check required folders.
  const requiredDirs = [ 'inc', 'src', 'blocks', 'assets', 'docs', 'languages' ];
  for ( const dir of requiredDirs ) {
    if ( existsSync( join( ROOT, dir ) ) ) {
      log.ok( `Directory exists: ${dir}/` );
    } else {
      log.warn( `Directory missing: ${dir}/ — create it when needed` );
      warnings++;
    }
  }

  // Summary.
  console.log( '' );
  if ( errors > 0 ) {
    log.error( `Validation failed with ${errors} error(s) and ${warnings} warning(s).` );
    process.exit( 1 );
  } else if ( warnings > 0 ) {
    log.warn( `Validation passed with ${warnings} warning(s). Review before release.` );
  } else {
    log.ok( 'Plugin structure looks good.' );
  }
}

/**
 * validate-schema
 * Validate block.json files and other plugin JSON files.
 */
function validateSchema() {
  log.heading( 'Validating JSON and block.json files…' );

  const jsonFiles = findFiles( ROOT, ( name ) => name.endsWith( '.json' ) );

  if ( jsonFiles.length === 0 ) {
    log.info( 'No JSON files found.' );
    return;
  }

  let errors = 0;

  for ( const filePath of jsonFiles ) {
    const rel = relative( ROOT, filePath );
    const content = readFileSafe( filePath );

    try {
      const parsed = JSON.parse( content );

      // For block.json files, check for key fields.
      if ( filePath.endsWith( 'block.json' ) ) {
        const missing = [];
        if ( ! parsed.name ) missing.push( 'name' );
        if ( ! parsed.title ) missing.push( 'title' );
        if ( ! parsed.category ) missing.push( 'category' );
        if ( ! parsed[ '$schema' ] ) missing.push( '$schema' );

        if ( missing.length > 0 ) {
          log.warn( `${rel}: block.json missing recommended fields: ${missing.join( ', ' )}` );
        } else {
          log.ok( `${rel}: valid block.json` );
        }
      } else {
        log.ok( `${rel}: valid JSON` );
      }
    } catch ( err ) {
      log.error( `${rel}: invalid JSON — ${err.message}` );
      errors++;
    }
  }

  console.log( '' );
  if ( errors > 0 ) {
    log.error( `Schema validation failed with ${errors} error(s).` );
    process.exit( 1 );
  } else {
    log.ok( 'All JSON files are valid.' );
  }
}

/**
 * security-scan
 * Scan PHP files for common risky patterns.
 * This is a practical, lightweight scan — not a replacement for a proper SAST tool.
 */
function securityScan() {
  log.heading( 'Scanning PHP files for risky patterns…' );

  // Patterns that suggest risky code.
  const riskyPatterns = [
    {
      label: 'Unescaped echo of $_GET/$_POST/$_REQUEST/$_COOKIE',
      regex: /echo\s+\$_(GET|POST|REQUEST|COOKIE)\[/,
      severity: 'error',
    },
    {
      // Flag direct assignment of superglobals to a variable without an obvious
      // sanitisation wrapper on the same line. Common safe wrappers are excluded
      // to reduce false positives, but manual review is still recommended.
      label: 'Possible unsanitised superglobal assignment — verify sanitisation',
      regex: /=\s*\$_(GET|POST|REQUEST|COOKIE)\[/,
      // Exclude lines that already call a known sanitisation function on the same line.
      exclude: /sanitize_|esc_url_raw|absint|intval|wp_unslash/,
      severity: 'warn',
      note: 'Confirm input is sanitised, e.g. sanitize_text_field( wp_unslash( $_POST["field"] ) )',
    },
    {
      label: 'eval() usage',
      regex: /\beval\s*\(/,
      severity: 'error',
    },
    {
      label: 'base64_decode used — review for obfuscated payloads',
      regex: /base64_decode\s*\(/,
      severity: 'warn',
    },
    {
      label: 'Potential SQL injection — unescaped $wpdb->query with variable',
      regex: /\$wpdb->(query|get_results|get_row|get_var)\s*\(\s*["']?\s*\$/,
      severity: 'warn',
      note: 'Use $wpdb->prepare() or verify this is safe',
    },
    {
      label: 'Direct file inclusion with variable',
      regex: /(include|require)(_once)?\s*\(\s*\$/,
      severity: 'warn',
      note: 'Ensure path is validated and sanitised',
    },
    {
      label: 'system/exec/shell_exec/passthru usage',
      regex: /\b(system|exec|shell_exec|passthru)\s*\(/,
      severity: 'error',
    },
  ];

  const phpFiles = findFiles( ROOT, ( name ) => name.endsWith( '.php' ) );

  if ( phpFiles.length === 0 ) {
    log.info( 'No PHP files found.' );
    return;
  }

  let totalIssues = 0;

  for ( const filePath of phpFiles ) {
    const rel = relative( ROOT, filePath );
    const lines = readFileSafe( filePath ).split( '\n' );

    for ( let i = 0; i < lines.length; i++ ) {
      const line = lines[ i ];

      for ( const pattern of riskyPatterns ) {
        if ( pattern.regex.test( line ) ) {
          // Skip if the line also matches an exclusion pattern (indicates safe usage).
          if ( pattern.exclude && pattern.exclude.test( line ) ) {
            continue;
          }
          const lineNum = i + 1;
          if ( pattern.severity === 'error' ) {
            log.error( `${rel}:${lineNum} — ${pattern.label}` );
          } else {
            log.warn( `${rel}:${lineNum} — ${pattern.label}` );
          }
          if ( pattern.note ) {
            log.info( `    Note: ${pattern.note}` );
          }
          totalIssues++;
        }
      }
    }
  }

  console.log( '' );
  if ( totalIssues === 0 ) {
    log.ok( `Scanned ${phpFiles.length} PHP file(s) — no obvious risky patterns found.` );
  } else {
    log.warn(
      `Scanned ${phpFiles.length} PHP file(s) — ${totalIssues} potential issue(s) found. Review carefully.`
    );
  }
}

/**
 * scan-placeholders
 * Check for unreplaced {{PLACEHOLDER}} tokens.
 */
function scanPlaceholders() {
  log.heading( 'Scanning for unreplaced placeholder tokens…' );

  const scanExtensions = [ '.php', '.json', '.md', '.txt', '.yml', '.yaml', '.mjs', '.js' ];
  const placeholderRegex = /\{\{[A-Z_]+\}\}/g;

  const files = findFiles(
    ROOT,
    ( name ) => scanExtensions.some( ( ext ) => name.endsWith( ext ) )
  );

  let found = 0;

  for ( const filePath of files ) {
    const rel = relative( ROOT, filePath );
    const content = readFileSafe( filePath );
    const matches = content.match( placeholderRegex );

    if ( matches ) {
      const unique = [ ...new Set( matches ) ];
      log.warn( `${rel}: ${unique.join( ', ' )}` );
      found += unique.length;
    }
  }

  console.log( '' );
  if ( found === 0 ) {
    log.ok( 'No unreplaced placeholder tokens found.' );
  } else {
    log.warn( `Found ${found} placeholder token(s) across scanned files. Replace before deploying.` );
  }
}

/**
 * help
 * Display available commands.
 */
function showHelp() {
  console.log( `
${colours.bold}plugin-utils.mjs — {{PLUGIN_NAME}} validation and utility CLI${colours.reset}

Usage:
  node plugin-utils.mjs <command>

Commands:
  ${colours.cyan}validate-plugin${colours.reset}    Validate plugin structure and headers
  ${colours.cyan}validate-schema${colours.reset}    Validate block.json and JSON files
  ${colours.cyan}security-scan${colours.reset}      Scan PHP files for risky patterns
  ${colours.cyan}scan-placeholders${colours.reset}  Check for unreplaced {{PLACEHOLDER}} tokens
  ${colours.cyan}help${colours.reset}               Show this help message
` );
}

// ─── Entrypoint ──────────────────────────────────────────────────────────────

const command = process.argv[ 2 ];

switch ( command ) {
  case 'validate-plugin':
    validatePlugin();
    break;
  case 'validate-schema':
    validateSchema();
    break;
  case 'security-scan':
    securityScan();
    break;
  case 'scan-placeholders':
    scanPlaceholders();
    break;
  case 'help':
  case '--help':
  case '-h':
  case undefined:
    showHelp();
    break;
  default:
    log.error( `Unknown command: ${command}` );
    showHelp();
    process.exit( 1 );
}
