<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Cac_Onboarding
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( ! defined( 'BP_TESTS_DIR' ) ) {
	define( 'BP_TESTS_DIR', dirname( __FILE__ ) . '/../../../buddypress/tests/phpunit' );
}

if ( ! file_exists( BP_TESTS_DIR . '/bootstrap.php' ) ) {
	die( 'BuddyPress tests could not be found. Please define BP_TESTS_DIR.' . "\n" );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require BP_TESTS_DIR . '/includes/loader.php';
	require dirname( dirname( dirname( __FILE__ ) ) ) . '/cac-group-library.php';

	add_action(
		'bp_include',
		function() {
			global $wpdb;
			if ( $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}cac_library_items" ) ) ) {
				$wpdb->query( "DROP TABLE {$wpdb->prefix}cac_library_items" );
			}
			$schema = cac_group_library()->schema;
			$schema->install_table();
		},
		20
	);
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

require BP_TESTS_DIR . '/includes/testcase.php';
