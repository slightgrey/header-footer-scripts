<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HFS_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Inject scripts into <head>.
	 * Hooked to wp_head at priority 999.
	 */
	public function inject_header_scripts() {
		// Global header scripts — always output.
		$global = get_option( 'hfs_global_header_scripts', '' );
		if ( ! empty( trim( $global ) ) ) {
			echo "\n" . $global . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Per-page header scripts — only on singular enabled post types.
		if ( is_singular() ) {
			$enabled_post_types = (array) get_option( 'hfs_enabled_post_types', array() );
			if ( in_array( get_post_type(), $enabled_post_types, true ) ) {
				$post_scripts = get_post_meta( get_the_ID(), '_hfs_header_scripts', true );
				if ( ! empty( trim( (string) $post_scripts ) ) ) {
					echo "\n" . $post_scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}

	/**
	 * Inject scripts before </body>.
	 * Hooked to wp_footer at priority 999.
	 */
	public function inject_footer_scripts() {
		// Global footer scripts — always output.
		$global = get_option( 'hfs_global_footer_scripts', '' );
		if ( ! empty( trim( $global ) ) ) {
			echo "\n" . $global . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Per-page footer scripts — only on singular enabled post types.
		if ( is_singular() ) {
			$enabled_post_types = (array) get_option( 'hfs_enabled_post_types', array() );
			if ( in_array( get_post_type(), $enabled_post_types, true ) ) {
				$post_scripts = get_post_meta( get_the_ID(), '_hfs_footer_scripts', true );
				if ( ! empty( trim( (string) $post_scripts ) ) ) {
					echo "\n" . $post_scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}
}
