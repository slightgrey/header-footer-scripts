# WP Header Footer Scripts

A WordPress plugin that lets administrators inject custom scripts into the `<head>` or footer of specific pages/posts and globally across the site.

## Features

- **Per-page scripts** — Add header and footer scripts to individual posts, pages, or custom post types. Scripts only load on that specific page.
- **Global scripts** — Inject scripts into `<head>` or before `</body>` on every page of your site.
- **Post type control** — Choose which post types show the script fields (posts, pages, or any registered custom post type).
- **Multisite compatible** — Works with WordPress Multisite. Network-activate to cover all sites; each site manages its own scripts independently.
- **ShadCN-inspired admin UI** — Clean, modern settings page with dark code editor textareas, toggle switches, and card layout.

## Installation

1. Upload the `header-footer-scripts` folder to `/wp-content/plugins/`.
2. Activate the plugin through **Plugins** in WordPress admin.
3. Go to **Settings → Header Footer Scripts** to configure.

### Network / Multisite

Activate network-wide from the **Network Admin → Plugins** screen. Default options will be seeded on all existing sites and any new sites added to the network.

## Usage

### Global Scripts

Navigate to **Settings → Header Footer Scripts**:

- **Global Header Scripts** — Paste any code here (e.g. Google Analytics, GTM, custom meta tags). Injected into `<head>` on every page.
- **Global Footer Scripts** — Paste any code here (e.g. chat widgets, conversion pixels). Injected before `</body>` on every page.

### Per-Page Scripts

1. Enable the post types you want under **Enabled Post Types**.
2. Edit any post/page of an enabled type.
3. Find the **Header and Footer Scripts** meta box below the editor.
4. Add your scripts — they will only load on that single page.

## Requirements

- WordPress 5.1 or higher
- PHP 7.4 or higher

## File Structure

Follows the [DevinVinson WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate) structure.

```
header-footer-scripts/
├── header-footer-scripts.php     # Plugin bootstrap
├── uninstall.php                 # Cleanup on uninstall
├── includes/
│   ├── class-header-footer-scripts.php   # Core orchestrator
│   ├── class-hfs-loader.php              # Hook registry
│   ├── class-hfs-i18n.php               # Internationalisation
│   ├── class-hfs-activator.php          # Activation (multisite aware)
│   └── class-hfs-deactivator.php        # Deactivation
├── admin/
│   ├── class-hfs-admin.php              # Admin logic
│   ├── css/hfs-admin.css               # ShadCN-inspired styles
│   ├── js/hfs-admin.js                 # Admin interactions
│   └── partials/
│       ├── hfs-admin-settings.php      # Settings page
│       └── hfs-admin-meta-box.php      # Per-page meta box
└── public/
    └── class-hfs-public.php            # Script injection
```

## Security

- Settings page requires `manage_options` capability.
- Meta box saves are nonce-verified and capability-checked (`edit_post`).
- Script content is stored and output raw — this is intentional for a trusted-admin script injection plugin (same approach as WPBeginner's Insert Headers and Footers).

## License

GPL-2.0-or-later
