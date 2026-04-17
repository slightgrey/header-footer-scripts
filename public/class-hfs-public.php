<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HFS_Public {

	private $plugin_name;
	private $version;

	/**
	 * Prevents inject_header_scripts from running twice when it is moved to
	 * an earlier wp_head priority because the scripts contain a canonical tag.
	 *
	 * @var bool
	 */
	private $header_scripts_injected = false;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Inject scripts into <head>.
	 * Normally hooked at priority 999. When scripts contain a canonical tag,
	 * maybe_remove_default_canonical() re-registers this at priority 10 so the
	 * canonical appears just after the title. The flag prevents double output.
	 */
	public function inject_header_scripts() {
		if ( $this->header_scripts_injected ) {
			return;
		}
		$this->header_scripts_injected = true;

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
	/**
	 * Hooked to `wp` — fires on every front-end request after the main query
	 * is established but before any output. If the current page is a singular
	 * post of an enabled type with _auhfc legacy data and empty HFS fields,
	 * the legacy data is copied into the HFS post meta immediately.
	 *
	 * After this runs, subsequent front-end visits and admin views use the
	 * HFS data directly — no fallback lookup needed.
	 */
	public function maybe_migrate_legacy_data() {
		if ( ! is_singular() ) {
			return;
		}

		if ( '1' !== get_option( 'hfs_legacy_fallback', '1' ) ) {
			return;
		}

		$enabled_post_types = (array) get_option( 'hfs_enabled_post_types', array() );
		if ( ! in_array( get_post_type(), $enabled_post_types, true ) ) {
			return;
		}

		$post_id = get_the_ID();

		// Skip if HFS data already exists on either field.
		$existing_header = get_post_meta( $post_id, '_hfs_header_scripts', true );
		$existing_footer = get_post_meta( $post_id, '_hfs_footer_scripts', true );
		if ( ! empty( trim( (string) $existing_header ) ) || ! empty( trim( (string) $existing_footer ) ) ) {
			return;
		}

		// Read and decode legacy _auhfc data.
		$raw = get_post_meta( $post_id, '_auhfc', true );
		if ( empty( $raw ) ) {
			return;
		}

		$data = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
		if ( ! is_array( $data ) ) {
			return;
		}

		$head   = isset( $data['head'] ) ? (string) $data['head'] : '';
		$footer = isset( $data['footer'] ) ? (string) $data['footer'] : '';

		if ( empty( trim( $head ) ) && empty( trim( $footer ) ) ) {
			return;
		}

		update_post_meta( $post_id, '_hfs_header_scripts', $head );
		update_post_meta( $post_id, '_hfs_footer_scripts', $footer );
	}

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
	 * Filters Yoast SEO's canonical URL output.
	 * Returns empty string to suppress Yoast's canonical when:
	 *  - The global override option is enabled, OR the per-post override is checked, AND
	 *  - Our resolved header scripts actually contain a <link rel="canonical"> tag.
	 * Passes through unchanged in all other cases so Yoast remains in control.
	 *
	 * Hooked to wpseo_canonical at priority 999 (after Yoast's own resolution).
	 *
	 * @param string $canonical The canonical URL Yoast intends to output.
	 * @return string Empty string to suppress, original value to keep.
	 */
	public function maybe_suppress_yoast_canonical( $canonical ) {
		if ( ! is_singular() ) {
			return $canonical;
		}

		$enabled_post_types = (array) get_option( 'hfs_enabled_post_types', array() );
		if ( ! in_array( get_post_type(), $enabled_post_types, true ) ) {
			return $canonical;
		}

		$post_id = get_the_ID();

		$per_post = get_post_meta( $post_id, '_hfs_override_yoast_canonical', true );
		$global   = get_option( 'hfs_override_yoast_canonical', '0' );

		if ( '1' !== $per_post && '1' !== $global ) {
			return $canonical;
		}

		// Only suppress if we're actually replacing Yoast's canonical with our own.
		$resolved = $this->resolve_header_scripts( $post_id );
		if ( $this->has_canonical_tag( $resolved ) ) {
			return '';
		}

		return $canonical;
	}

	/**
	 * Fires at wp_head priority 1 — before WordPress core outputs rel_canonical
	 * at priority 10. If our header scripts (global or per-page) contain a
	 * canonical link tag, WordPress's built-in canonical is removed so it does
	 * not duplicate ours.
	 */
	public function maybe_remove_default_canonical() {
		$has_canonical = false;

		// Check global header scripts first.
		$global = get_option( 'hfs_global_header_scripts', '' );
		if ( $this->has_canonical_tag( $global ) ) {
			$has_canonical = true;
		}

		// Check per-page header scripts for singular enabled post types.
		if ( ! $has_canonical && is_singular() ) {
			$enabled_post_types = (array) get_option( 'hfs_enabled_post_types', array() );
			if ( in_array( get_post_type(), $enabled_post_types, true ) ) {
				$resolved = $this->resolve_header_scripts( get_the_ID() );
				if ( $this->has_canonical_tag( $resolved ) ) {
					$has_canonical = true;
				}
			}
		}

		if ( $has_canonical ) {
			remove_action( 'wp_head', 'rel_canonical' );
			// Re-register header injection at priority 10 so the canonical
			// appears just after <title> instead of at the end of <head>.
			add_action( 'wp_head', array( $this, 'inject_header_scripts' ), 10 );
		}
	}

	/**
	 * Returns true if the given string contains a <link rel="canonical"> tag.
	 *
	 * @param string $content
	 * @return bool
	 */
	private function has_canonical_tag( $content ) {
		return (bool) preg_match( '/<link[^>]+rel=["\']canonical["\'][^>]*>/i', (string) $content );
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

		// WordPress auto-unserializes post meta, so _auhfc may already be an
		// array (PHP serialized by the old plugin) or still a JSON string.
		if ( is_array( $raw ) ) {
			$data = $raw;
		} else {
			$data = json_decode( (string) $raw, true );
		}

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
