<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HFS_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles( $hook ) {
		if ( ! $this->is_hfs_screen( $hook ) ) {
			return;
		}
		wp_enqueue_style(
			$this->plugin_name,
			HFS_PLUGIN_URL . 'admin/css/hfs-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts( $hook ) {
		if ( ! $this->is_hfs_screen( $hook ) ) {
			return;
		}
		wp_enqueue_script(
			$this->plugin_name,
			HFS_PLUGIN_URL . 'admin/js/hfs-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}

	/**
	 * Returns true when on the plugin settings page or a post-type edit screen
	 * for an enabled post type.
	 */
	private function is_hfs_screen( $hook ) {
		// Settings page.
		if ( 'settings_page_header-footer-scripts' === $hook ) {
			return true;
		}
		// Post editor screens for enabled post types.
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			$enabled = get_option( 'hfs_enabled_post_types', array() );
			$screen  = get_current_screen();
			if ( $screen && in_array( $screen->post_type, (array) $enabled, true ) ) {
				return true;
			}
		}
		return false;
	}

	public function add_settings_page() {
		add_options_page(
			__( 'Header Footer Scripts', 'header-footer-scripts' ),
			__( 'Header Footer Scripts', 'header-footer-scripts' ),
			'manage_options',
			'header-footer-scripts',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include HFS_PLUGIN_DIR . 'admin/partials/hfs-admin-settings.php';
	}

	public function register_settings() {
		register_setting(
			'hfs_settings',
			'hfs_enabled_post_types',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_enabled_post_types' ),
				'default'           => array( 'post', 'page' ),
			)
		);

		register_setting(
			'hfs_settings',
			'hfs_global_header_scripts',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_script_content' ),
				'default'           => '',
			)
		);

		register_setting(
			'hfs_settings',
			'hfs_global_footer_scripts',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_script_content' ),
				'default'           => '',
			)
		);

		register_setting(
			'hfs_settings',
			'hfs_legacy_fallback',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '1',
			)
		);

		register_setting(
			'hfs_settings',
			'hfs_override_yoast_canonical',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '0',
			)
		);
	}

	public function sanitize_enabled_post_types( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}
		$registered = get_post_types( array( 'public' => true ) );
		unset( $registered['attachment'] );
		return array_values(
			array_intersect( array_map( 'sanitize_key', $input ), array_keys( $registered ) )
		);
	}

	public function sanitize_script_content( $input ) {
		// Enforce string type and strip null bytes. wp_unslash() is already
		// applied by the Settings API before this callback fires.
		$input = (string) $input;
		$input = str_replace( "\0", '', $input );
		return $input;
	}

	public function add_meta_boxes() {
		$enabled_post_types = get_option( 'hfs_enabled_post_types', array() );
		foreach ( (array) $enabled_post_types as $post_type ) {
			add_meta_box(
				'hfs_scripts',
				__( 'Header and Footer Scripts', 'header-footer-scripts' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	public function render_meta_box( $post ) {
		$this->maybe_migrate_legacy_data( $post );
		include HFS_PLUGIN_DIR . 'admin/partials/hfs-admin-meta-box.php';
	}

	/**
	 * Automatically copies _auhfc head/footer data into the HFS post meta fields
	 * the first time an admin opens a post in the editor — but only when:
	 *  - The legacy fallback option is enabled.
	 *  - Both HFS fields (_hfs_header_scripts and _hfs_footer_scripts) are empty.
	 *  - The post has _auhfc meta with at least one non-empty head or footer value.
	 *
	 * After this runs, the HFS fields are populated in the DB. On the next
	 * front-end visit the plugin uses those HFS values instead of falling back
	 * to the legacy data.
	 *
	 * @param WP_Post $post
	 */
	private function maybe_migrate_legacy_data( $post ) {
		if ( '1' !== get_option( 'hfs_legacy_fallback', '1' ) ) {
			return;
		}

		// Skip if HFS data already exists on this post.
		$existing_header = get_post_meta( $post->ID, '_hfs_header_scripts', true );
		$existing_footer = get_post_meta( $post->ID, '_hfs_footer_scripts', true );
		if ( ! empty( trim( (string) $existing_header ) ) || ! empty( trim( (string) $existing_footer ) ) ) {
			return;
		}

		// Read legacy _auhfc data.
		$raw = get_post_meta( $post->ID, '_auhfc', true );
		if ( empty( $raw ) ) {
			return;
		}

		$data = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
		if ( ! is_array( $data ) ) {
			return;
		}

		$head   = isset( $data['head'] ) ? (string) $data['head'] : '';
		$footer = isset( $data['footer'] ) ? (string) $data['footer'] : '';

		// Nothing to migrate.
		if ( empty( trim( $head ) ) && empty( trim( $footer ) ) ) {
			return;
		}

		update_post_meta( $post->ID, '_hfs_header_scripts', $head );
		update_post_meta( $post->ID, '_hfs_footer_scripts', $footer );
	}

	public function save_meta_box_data( $post_id, $post ) {
		// Verify nonce.
		if (
			! isset( $_POST['hfs_meta_box_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hfs_meta_box_nonce'] ) ), 'hfs_save_meta_box_' . $post_id )
		) {
			return;
		}

		// Skip autosaves.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check capability.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Only save for enabled post types.
		$enabled = (array) get_option( 'hfs_enabled_post_types', array() );
		if ( ! in_array( $post->post_type, $enabled, true ) ) {
			return;
		}

		// Save header scripts.
		$header_scripts = isset( $_POST['hfs_header_scripts'] ) ? wp_unslash( $_POST['hfs_header_scripts'] ) : '';
		update_post_meta( $post_id, '_hfs_header_scripts', $header_scripts );

		// Save footer scripts.
		$footer_scripts = isset( $_POST['hfs_footer_scripts'] ) ? wp_unslash( $_POST['hfs_footer_scripts'] ) : '';
		update_post_meta( $post_id, '_hfs_footer_scripts', $footer_scripts );

		// Save per-post Yoast canonical override.
		$override_yoast = isset( $_POST['hfs_override_yoast_canonical'] ) ? '1' : '0';
		update_post_meta( $post_id, '_hfs_override_yoast_canonical', $override_yoast );
	}
}
