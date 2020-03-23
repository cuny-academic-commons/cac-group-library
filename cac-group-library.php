<?php
/**
 * Plugin Name:     CAC Group Library
 * Plugin URI:      https://commons.gc.cuny.edu
 * Description:     Group Library for the CUNY Academic Commons
 * Author:          CUNY Academic Commons
 * Author URI:      https://cmomons.gc.cuny.edu
 * Text Domain:     cac-onboarding
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Cac_Onboarding
 */

define( 'CAC_GROUP_LIBRARY_VER', '0.1.0-20200323' );
define( 'CAC_GROUP_LIBRARY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAC_GROUP_LIBRARY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action(
	'bp_include',
	function() {
		require __DIR__ . '/autoload.php';

		cac_group_library()->init();
	}
);

/**
 * Shorthand function to fetch our CAC Group Library instance.
 *
 * @since 0.1.0
 */
function cac_group_library() {
	return \CAC\GroupLibrary\App::get_instance();
}
