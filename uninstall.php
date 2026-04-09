<?php
/**
 * Fired when the plugin is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	$sites = get_sites( array( 'number' => 0, 'fields' => 'ids' ) );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'hfs_enabled_post_types' );
		delete_option( 'hfs_global_header_scripts' );
		delete_option( 'hfs_global_footer_scripts' );
		delete_post_meta_by_key( '_hfs_header_scripts' );
		delete_post_meta_by_key( '_hfs_footer_scripts' );
		restore_current_blog();
	}
} else {
	delete_option( 'hfs_enabled_post_types' );
	delete_option( 'hfs_global_header_scripts' );
	delete_option( 'hfs_global_footer_scripts' );
	delete_post_meta_by_key( '_hfs_header_scripts' );
	delete_post_meta_by_key( '_hfs_footer_scripts' );
}
