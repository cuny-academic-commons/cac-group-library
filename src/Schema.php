<?php

namespace CAC\GroupLibrary;

class Schema {
	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @return CAC\GroupLibrary\Schema
	 */
	private function __construct() {
		global $wpdb;
		$this->table_name = "{$wpdb->prefix}cac_library_items";
		return $this;
	}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function get_table_name() {
		return $this->table_name;
	}

	public function init() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	public function register_post_types() {

	}

	public function install_table() {
		$sql = $this->get_schema();

		if ( ! function_exists( 'dbDelta' ) ) {
			require ABSPATH . '/wp-admin/includes/upgrade.php';
		}

		$installed = dbDelta( $sql );
	}

	public function get_schema() {
		global $wpdb;

		$sql             = array();
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = "CREATE TABLE {$this->table_name} (
					id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					group_id bigint(20) NOT NULL,
					source_item_id bigint(20) NOT NULL,
					item_type varchar(30) NOT NULL,
					file_type varchar(30),
					user_id bigint(20),
					title varchar(150) NOT NULL,
					description longtext,
					url varchar(150) NOT NULL,
					date_modified datetime NOT NULL,
					KEY group_id (group_id),
					KEY title (title),
					KEY date_modified (date_modified),
					KEY item_type (item_type)
				) {$charset_collate};";

		return $sql;
	}
}
