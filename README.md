# Em Dash Remover for WordPress

[![WordPress](https://img.shields.io/badge/WordPress-5.8+-21759B.svg?style=flat&logo=wordpress)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg?style=flat&logo=php)](https://php.net)
[![Version: 4.1.0](https://img.shields.io/badge/Version-4.1.0-orange.svg)](em-dash-remover.php)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2+-blue.svg)](LICENSE)
[![Security: 100% Safe](https://img.shields.io/badge/Security-Audited-brightgreen.svg)](#security--performance)

> **Eliminate telltale AI punctuation on your WordPress site effortlessly.**
> Automatically replaces AI-generated **Em Dashes (`—`)** and **En Dashes (`–`)** (including HTML entities like `&mdash;`, `&ndash;`, `&#8212;`, `&#8211;`) with clean standard hyphens (`-`) on runtime, without altering your database, posts, or theme files.

---

## Why Em Dash Remover?

Modern AI writing models (ChatGPT, Claude, Gemini, Copilot) frequently output excessive **Em Dashes (`—`)** and **En Dashes (`–`)**. These are classic signals used by readers and AI detection systems to identify machine-generated text.

**Em Dash Remover** fixes this automatically:
- ⚡ **Zero-touch**: Runs purely in the public rendered HTML output buffer.
- 🛡️ **Zero Risk to Database**: Your original post content, Elementor layouts, Gutenberg blocks, and database remain 100% untouched.
- 🔒 **Code & Layout Safe**: Never breaks `<script>`, `<style>`, `<code>`, `<pre>`, `<textarea>`, `<svg>`, or HTML tag attributes (`href`, `title`, `alt`, `data-*`).
- 📊 **Status Dashboard**: Includes a handy overview under **Tools > Em Dash Remover** in your WordPress Admin.
- 🚀 **Ultra-lightweight**: Fast single-pass tokenization with instant early-exit optimization.

---

## Features

- **Replaces Both Em & En Dashes**:
  - `—` (U+2014) & `–` (U+2013)
  - Named entities: `&mdash;`, `&MDASH;`, `&ndash;`, `&NDASH;`
  - Decimal entities: `&#8212;`, `&#8211;`
  - Hex entities: `&#x2014;`, `&#x2013;`, `&#x02014;`, etc.
- **Full Page Builder Compatibility**: Works seamlessly with Elementor, Divi, Gutenberg, Beaver Builder, Oxygen, Bricks, and classic themes.
- **Strict Protection**:
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

### Option 2: Manual / Git
1. Clone the repository into your `/wp-content/plugins/` folder:
   ```bash
   git clone https://github.com/shadabrcspl/em-dash-remover.git /path/to/wordpress/wp-content/plugins/em-dash-remover
   ```
2. Activate **Em Dash Remover** from the WordPress Plugins screen.

---

## Developer Filters & Customization

### Change the Replacement String
```php
add_filter( 'em_dash_remover_replacement', function( $replacement ) {
    return ' - '; // Replace with spaced hyphen
} );
```

### Add or Modify Target Characters
```php
add_filter( 'em_dash_remover_targets', function( $targets ) {
    $targets[] = '―'; // Add horizontal bar
    return $targets;
} );
```

### Conditionally Disable on Specific Pages
```php
add_filter( 'em_dash_remover_enabled', function( $enabled ) {
    if ( is_single( 123 ) || is_singular( 'docs' ) ) {
        return false;
    }
    return $enabled;
} );
```

---

## Security & Performance

- **Direct Script Access Blocked**: Protected via `defined('ABSPATH') || exit;`.
- **Admin Access Protected**: Status page requires `manage_options` capability.
- **Zero Database / Query Overhead**: No SQL queries or DB writes.
- **Single-Pass Parsing**: High performance with minimal memory overhead and zero regex backtracking risk.

---

## License

This project is licensed under the **GNU General Public License v2.0 or later** - see the [LICENSE](LICENSE) file for details.

## Authors & Credits

- **Arshad Faraz**
- **Shadab Alam** ([@shadabrcspl](https://github.com/shadabrcspl))
