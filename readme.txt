=== Em Dash Remover ===
Contributors: shadabrcspl, arshadfaraz
Tags: em dash, dash, ai content, formatting, typography, text clean
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 3.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Replaces AI-telltale em dashes (—, &mdash;, &#8212;, &#x2014;) with normal hyphens (-) in rendered public HTML.

== Description ==

Modern AI writing assistants frequently use em dashes (—). This plugin replaces em dashes with normal hyphens (-) only in rendered public HTML text.

* **Safe**: It does not modify the database, Elementor data, theme/plugin files, URLs, or HTML attributes.
* **Code-Safe**: Leaves scripts, styles, code blocks (`<pre>`, `<code>`), textareas, and SVGs completely untouched.
* **Performance**: Ultra-fast execution with early exit checks.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or upload the `.zip` via Plugins > Add New > Upload Plugin.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Done! The plugin works automatically with zero configuration required.

== Frequently Asked Questions ==

= Does this modify my database? =
No. The plugin works exclusively on the public rendered HTML output buffer. Your database, posts, and page builder data are completely untouched.

= Will it break my code snippets or scripts? =
No. Code tags (`<pre>`, `<code>`, `<kbd>`, `<samp>`, `<var>`), `<script>`, `<style>`, `<textarea>`, and `<svg>` tags are strictly protected.

== Changelog ==

= 3.1.0 =
* Added support for all HTML entity representations (&mdash;, &#8212;, &#x2014;).
* Added protection for <svg>, <kbd>, <samp>, and <var> elements.
* Added developer filter hooks (em_dash_remover_replacement, em_dash_remover_targets, em_dash_remover_enabled).
* Added full compatibility with WordPress 6.x and PHP 8.x.

= 3.0.0 =
* Initial public release.
