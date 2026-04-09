<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HFS_i18n {

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'header-footer-scripts',
			false,
			dirname( plugin_basename( HFS_PLUGIN_DIR . 'header-footer-scripts.php' ) ) . '/languages/'
		);
	}
}
