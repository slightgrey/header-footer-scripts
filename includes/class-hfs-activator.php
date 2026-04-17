<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HFS_Activator {

	public static function activate( $network_wide = false ) {
		if ( is_multisite() && $network_wide ) {
			$sites = get_sites( array( 'number' => 0, 'fields' => 'ids' ) );
			foreach ( $sites as $site_id ) {
				switch_to_blog( $site_id );
				self::set_default_options();
				restore_current_blog();
			}
		} else {
			self::set_default_options();
		}
	}

	/**
	 * Initialise default options for a newly created site when the plugin
	 * is network-activated.
	 *
	 * @param WP_Site $new_site
	 */
	public static function on_new_site( $new_site ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active_for_network( plugin_basename( HFS_PLUGIN_DIR . 'header-footer-scripts.php' ) ) ) {
			return;
		}
		switch_to_blog( $new_site->blog_id );
		self::set_default_options();
		restore_current_blog();
	}

	private static function set_default_options() {
		add_option( 'hfs_enabled_post_types', array( 'post', 'page' ) );
		add_option( 'hfs_global_header_scripts', '' );
		add_option( 'hfs_global_footer_scripts', '' );
		add_option( 'hfs_legacy_fallback', '1' );
	}
}
