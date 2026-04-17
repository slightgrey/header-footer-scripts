<?php
/**
 * Plugin Name:       Header Footer Scripts
 * Plugin URI:        https://github.com/
 * Description:       Inject custom scripts into the &lt;head&gt; or footer of specific pages/posts and globally across your site.
 * Version:           1.0.2
 * Author:            Vince
 * Text Domain:       header-footer-scripts
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HFS_VERSION', '1.0.2' );
define( 'HFS_PLUGIN_NAME', 'header-footer-scripts' );
define( 'HFS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once HFS_PLUGIN_DIR . 'includes/class-hfs-activator.php';
require_once HFS_PLUGIN_DIR . 'includes/class-hfs-deactivator.php';

register_activation_hook( __FILE__, array( 'HFS_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'HFS_Deactivator', 'deactivate' ) );

// Initialise default options whenever a new site is added to the network.
add_action( 'wp_insert_site', array( 'HFS_Activator', 'on_new_site' ) );

add_action( 'plugins_loaded', 'hfs_run_plugin' );

function hfs_run_plugin() {
	require_once HFS_PLUGIN_DIR . 'includes/class-header-footer-scripts.php';
	$plugin = new Header_Footer_Scripts();
	$plugin->run();
}
