=== Em Dash Remover ===
Contributors: shadabrcspl, arshadfaraz
Tags: em dash, en dash, dash, ai content, typography, content cleanup
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 4.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically replaces em dashes (—), en dashes (–), and HTML entities with normal hyphens (-) in rendered public HTML.

== Features ==
* Em dash: — & entities (e.g. &mdash;, &#8212;) → -
* En dash: – & entities (e.g. &ndash;, &#8211;) → -
* No database modification (100% Safe)
* No Elementor / builder modification
* No theme or plugin file modification
* No URL or HTML attribute modification
* No script, style, comment, pre, code, textarea, or SVG modification
* Works automatically on public frontend requests
* Includes Tools > Em Dash Remover status dashboard

== Important ==
This plugin changes only the final rendered HTML sent to visitors. Original WordPress database content and posts remain completely unchanged.

== Changelog ==

= 4.1.0 =
* Added support for both Em Dash (—) and En Dash (–) plus all standard HTML entities.
* Added Tools > Em Dash Remover admin status dashboard.
* Added single-pass protected token extraction and fast early-exit checks.
* Added developer filter hooks (em_dash_remover_replacement, em_dash_remover_targets, em_dash_remover_enabled).
* Added full compatibility with WordPress 6.x and PHP 8.x.
