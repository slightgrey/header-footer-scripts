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
				$post_id      = get_the_ID();
				$post_scripts = $this->resolve_header_scripts( $post_id );
				if ( ! empty( trim( $post_scripts ) ) ) {
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
				$post_id      = get_the_ID();
				$post_scripts = $this->resolve_footer_scripts( $post_id );
				if ( ! empty( trim( $post_scripts ) ) ) {
					echo "\n" . $post_scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}

	/**
	 * Returns the header scripts for a post.
	 * New plugin data takes priority. Falls back to legacy _auhfc `head` value
	 * if no new data has been saved yet.
	 *
	 * @param int $post_id
	 * @return string
	 */
	private function resolve_header_scripts( $post_id ) {
		$scripts = (string) get_post_meta( $post_id, '_hfs_header_scripts', true );
		if ( ! empty( trim( $scripts ) ) ) {
			return $scripts;
		}

		if ( '1' === get_option( 'hfs_legacy_fallback', '1' ) ) {
			$legacy = $this->get_legacy_auhfc_data( $post_id );
			if ( $legacy && ! empty( trim( (string) $legacy['head'] ) ) ) {
				return $legacy['head'];
			}
		}

		return '';
	}

	/**
	 * Returns the footer scripts for a post.
	 * New plugin data takes priority. Falls back to legacy _auhfc `footer` value
	 * if no new data has been saved yet.
	 *
	 * @param int $post_id
	 * @return string
	 */
	private function resolve_footer_scripts( $post_id ) {
		$scripts = (string) get_post_meta( $post_id, '_hfs_footer_scripts', true );
		if ( ! empty( trim( $scripts ) ) ) {
			return $scripts;
		}

		if ( '1' === get_option( 'hfs_legacy_fallback', '1' ) ) {
			$legacy = $this->get_legacy_auhfc_data( $post_id );
			if ( $legacy && ! empty( trim( (string) $legacy['footer'] ) ) ) {
				return $legacy['footer'];
			}
		}

		return '';
	}

	/**
	 * Reads and decodes the legacy _auhfc post meta left by the previous plugin.
	 * Returns the decoded array on success, or null if not present / invalid.
	 *
	 * Expected format: {"head":"...","body":"...","footer":"...","behavior":"append"}
	 *
	 * @param int $post_id
	 * @return array|null
	 */
	private function get_legacy_auhfc_data( $post_id ) {
		$raw = get_post_meta( $post_id, '_auhfc', true );
		if ( empty( $raw ) ) {
			return null;
		}

		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			return null;
		}

		return array_merge(
			array(
				'head'   => '',
				'body'   => '',
				'footer' => '',
			),
			$data
		);
	}
}
