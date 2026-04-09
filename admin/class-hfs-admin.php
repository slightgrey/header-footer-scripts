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
	}

	public function sanitize_enabled_post_types( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}
		return array_map( 'sanitize_key', $input );
	}

	public function sanitize_script_content( $input ) {
		return wp_unslash( $input );
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
		include HFS_PLUGIN_DIR . 'admin/partials/hfs-admin-meta-box.php';
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

		// Save header scripts.
		$header_scripts = isset( $_POST['hfs_header_scripts'] ) ? wp_unslash( $_POST['hfs_header_scripts'] ) : '';
		update_post_meta( $post_id, '_hfs_header_scripts', $header_scripts );

		// Save footer scripts.
		$footer_scripts = isset( $_POST['hfs_footer_scripts'] ) ? wp_unslash( $_POST['hfs_footer_scripts'] ) : '';
		update_post_meta( $post_id, '_hfs_footer_scripts', $footer_scripts );
	}
}
