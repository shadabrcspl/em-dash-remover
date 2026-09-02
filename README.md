# Em Dash Remover for WordPress

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-21759B.svg?style=flat&logo=wordpress)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg?style=flat&logo=php)](https://php.net)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2+-blue.svg)](LICENSE)
[![Security: 100% Safe](https://img.shields.io/badge/Security-Audited-brightgreen.svg)](#security--performance)

> **Eliminate telltale AI punctuation on your WordPress site effortlessly.**
> Automatically replaces AI-generated Em Dashes (`—`, `&mdash;`, `&#8212;`, `&#x2014;`) with natural hyphens (`-`) on runtime, without touching your database, posts, or source files.

---

## Why Em Dash Remover?

Modern AI writing tools (ChatGPT, Claude, Gemini, Copilot, etc.) heavily overuse the **Em Dash (`—`)**. Its repetitive appearance is one of the most prominent markers used by readers and AI detection tools to identify AI-generated copy.

**Em Dash Remover** solves this instantly and safely:
- ⚡ **Zero-touch**: Operates purely on rendered public HTML output.
- 🛡️ **Zero Risk to Database**: Your original post content, Elementor layouts, Gutenberg blocks, and database remain 100% untouched.
- 🔒 **Code & Layout Safe**: Will never break `<script>`, `<style>`, `<code>`, `<pre>`, `<textarea>`, `<svg>`, or HTML tag attributes (e.g., links, image alts, meta tags).
- 🚀 **Ultra-lightweight**: Instant execution with fail-safe fast exit if no em dashes exist on the page.

---

## Features

- **Comprehensive Entity Support**: Replaces Unicode `—` (U+2014), named HTML entities (`&mdash;`, `&MDASH;`), decimal entities (`&#8212;`), and hex entities (`&#x2014;`).
- **Full Builder Compatibility**: Works seamlessly with Elementor, Divi, Gutenberg, Beaver Builder, Oxygen, Bricks, and classic themes.
- **Smart Protection**:
  - Scripts and JSON-LD structured data (`<script>`)
  - CSS Stylesheets (`<style>`)
  - Preformatted and Code blocks (`<pre>`, `<code>`, `<kbd>`, `<samp>`, `<var>`)
  - Form fields (`<textarea>`)
  - Vector Graphics (`<svg>`)
  - HTML comments (`<!-- ... -->`)
  - HTML tag attributes (`<a href="...">`, `<img alt="...">`, etc.)
- **Bypasses Non-HTML Endpoints**: Automatically ignores WP Admin, REST API endpoints, WP-CLI, CRON jobs, AJAX, XML sitemaps, and RSS feeds.
- **Extensible Hook API**: Filter and customize target patterns, replacement strings, or conditional execution using WordPress filters.

---

## Installation

### Option 1: Direct Zip Download (Recommended)
1. Download the latest **[em-dash-remover.zip](https://github.com/shadabrcspl/em-dash-remover/raw/main/dist/em-dash-remover.zip)**.
2. In your WordPress Admin dashboard, go to **Plugins > Add New > Upload Plugin**.
3. Choose the downloaded `.zip` file and click **Install Now**.
4. Click **Activate Plugin**.

### Option 2: Manual FTP / Git
1. Clone or upload the `em-dash-remover` folder into your `/wp-content/plugins/` directory:
   ```bash
   git clone https://github.com/shadabrcspl/em-dash-remover.git /path/to/wordpress/wp-content/plugins/em-dash-remover
   ```
2. Navigate to **Plugins** in WordPress Admin and activate **Em Dash Remover**.

---

## Developer Filters & Customization

You can customize the behavior via your theme's `functions.php` or a custom snippet plugin.

### Change the Replacement String
By default, em dashes are replaced with a single hyphen `-`. You can change it to a spaced hyphen (` - `) or any custom string:
```php
add_filter( 'em_dash_remover_replacement', function( $replacement ) {
    return ' - '; // Replace with spaced hyphen
} );
```

### Add or Modify Target Characters
```php
add_filter( 'em_dash_remover_targets', function( $targets ) {
    // Add custom characters, e.g., En Dash (–)
    $targets[] = '–';
    $targets[] = '&ndash;';
    return $targets;
} );
```

### Conditionally Disable on Specific Pages
```php
add_filter( 'em_dash_remover_enabled', function( $enabled ) {
    // Disable on specific post ID or post type
    if ( is_single( 123 ) || is_singular( 'documentation' ) ) {
        return false;
    }
    return $enabled;
} );
```

---

## Security & Performance

- **Direct Execution Blocked**: Direct access to PHP files is prevented via `defined('ABSPATH') || exit;`.
- **Zero Database / Query Impact**: No SQL queries are executed.
- **Fail-Safe Regex Handling**: In the rare event of a regex backtrack limitation on unusually large documents, the original unmodified HTML is safely returned.
- **Clean Uninstall**: No leftover database tables, options, or transients.

---

## License

This project is licensed under the **GNU General Public License v2.0 or later** - see the [LICENSE](LICENSE) file for details.

## Authors & Credits

- **Arshad Faraz**
- **Shadab Alam** ([@shadabrcspl](https://github.com/shadabrcspl))
