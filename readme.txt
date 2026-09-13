=== Em Dash Remover ===
Contributors: shadabrcspl, arshadfaraz
Tags: em dash, en dash, dash, database cleaner, content cleanup, ai content
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 5.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Permanently cleans or dynamically replaces em dashes (—), en dashes (–), and HTML entities with normal hyphens (-). Includes 1-Click Permanent Database Cleaner.

== Features ==
* 1-Click Permanent Database Cleaner (under Tools > Em Dash Remover)
* Permanent changes survive plugin deactivation and uninstallation
* Cleans Posts, Pages, Custom Post Types, Titles, and Excerpts
* Cleans Elementor / Page Builder JSON data safely
* Real-time AJAX batch progress bar with live logs
* Live runtime output filter for ongoing content protection
* Strictly preserves code blocks (<pre>, <code>), scripts, styles, textareas, SVGs, and HTML attributes

== Installation ==

1. Upload the plugin zip via WordPress Admin > Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. (Optional for Permanent Clean): Go to Tools > Em Dash Remover and click "Clean All Content in Database Permanently".
4. Once completed, your database content is permanently cleaned! You can keep the plugin active for live protection or safely uninstall it.

== Changelog ==

= 5.0.0 =
* Added 1-Click Permanent Database Cleaner for Posts, Pages, Titles, Excerpts, and Elementor builder metadata.
* Added AJAX batch processing engine with live progress bar and statistics.
* Added support for permanent cleanup that remains intact after plugin uninstallation.
* Upgraded runtime filter with single-core sanitize_text_nodes architecture.

= 4.1.0 =
* Added En Dash (–) support and Tools status page.
