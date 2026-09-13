# Em Dash Remover for WordPress

[![WordPress](https://img.shields.io/badge/WordPress-5.8+-21759B.svg?style=flat&logo=wordpress)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg?style=flat&logo=php)](https://php.net)
[![Version: 5.0.0](https://img.shields.io/badge/Version-5.0.0-orange.svg)](em-dash-remover.php)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2+-blue.svg)](LICENSE)
[![Security: 100% Safe](https://img.shields.io/badge/Security-Audited-brightgreen.svg)](#security--performance)

> **Eliminate telltale AI punctuation on your WordPress site effortlessly.**
> Permanently cleans or dynamically replaces AI-generated **Em Dashes (`—`)** and **En Dashes (`–`)** (including HTML entities like `&mdash;`, `&ndash;`, `&#8212;`, `&#8211;`) with standard hyphens (`-`).

---

## ⚡ Two Modes of Operation

### 1. 💾 1-Click Permanent Database Cleaner (Uninstall-Safe)
- Navigate to **Tools > Em Dash Remover** in your WordPress Admin.
- Click **"Clean All Content in Database Permanently"**.
- Automatically scans all Posts, Pages, Titles, Excerpts, and Elementor builder content in safe AJAX batches.
- **Permanent Effect**: Once processed, all changes are saved directly into your database. **You can safely deactivate or uninstall the plugin, and the clean hyphens will remain permanently!**

### 2. ⚡ Live Runtime Output Filter
- Intercepts public frontend HTML on the fly with zero database modification.
- Acts as a real-time safety net for any newly drafted AI content.

---

## Features

- **Replaces Both Em & En Dashes**:
  - `—` (U+2014) & `–` (U+2013)
  - Named entities: `&mdash;`, `&MDASH;`, `&ndash;`, `&NDASH;`
  - Decimal entities: `&#8212;`, `&#8211;`
  - Hex entities: `&#x2014;`, `&#x2013;`, `&#x02014;`, etc.
- **Elementor & Page Builder Safe**: Recursively cleans Elementor widget text without corrupting JSON structures or URLs.
- **Strict Code & Asset Protection**:
  - Scripts and JSON-LD (`<script>`)
  - CSS Stylesheets (`<style>`)
  - Code snippets & preformatted text (`<pre>`, `<code>`, `<kbd>`, `<samp>`, `<var>`)
  - Form fields (`<textarea>`)
  - Vector Graphics (`<svg>`)
  - HTML tag attributes (`<a href="...">`, `<img alt="...">`, etc.)
- **AJAX Batch Engine**: Safely processes sites with hundreds or thousands of pages without PHP timeouts.

---

## Installation

### Step 1: Download & Activate
1. Download **[em-dash-remover.zip](https://github.com/shadabrcspl/em-dash-remover/raw/main/dist/em-dash-remover.zip)**.
2. In WordPress Admin, go to **Plugins > Add New > Upload Plugin**, select the `.zip`, and click **Activate**.

### Step 2: Run Permanent Clean (Optional)
1. Go to **Tools > Em Dash Remover**.
2. Click **"Clean All Content in Database Permanently"**.
3. Watch the progress bar complete.
4. Once completed, your database is permanently cleaned! You may keep the plugin active for future content or safely delete it.

---

## License

This project is licensed under the **GNU General Public License v2.0 or later** - see the [LICENSE](LICENSE) file for details.

## Authors & Credits

- **Arshad Faraz**
- **Shadab Alam** ([@shadabrcspl](https://github.com/shadabrcspl))
