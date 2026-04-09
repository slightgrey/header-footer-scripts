<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Header_Footer_Scripts {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->plugin_name = HFS_PLUGIN_NAME;
		$this->version     = HFS_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once HFS_PLUGIN_DIR . 'includes/class-hfs-loader.php';
		require_once HFS_PLUGIN_DIR . 'includes/class-hfs-i18n.php';
		require_once HFS_PLUGIN_DIR . 'admin/class-hfs-admin.php';
		require_once HFS_PLUGIN_DIR . 'public/class-hfs-public.php';
		$this->loader = new HFS_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new HFS_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks() {
		$plugin_admin = new HFS_Admin( $this->plugin_name, $this->version );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_meta_box_data', 10, 2 );
	}

	private function define_public_hooks() {
		$plugin_public = new HFS_Public( $this->plugin_name, $this->version );

		$this->loader->add_action( 'wp_head', $plugin_public, 'inject_header_scripts', 999 );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'inject_footer_scripts', 999 );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}

	public function get_loader() {
		return $this->loader;
	}
}
