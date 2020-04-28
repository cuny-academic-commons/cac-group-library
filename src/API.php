<?php

namespace CAC\GroupLibrary;

class API {
	protected $endpoints = array();

	private function __construct() {}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function init() {
		add_action( 'rest_api_init', array( $this, 'init_endpoints' ) );
	}

	public function init_endpoints() {
		$this->endpoints['library-item'] = new Endpoints\LibraryItem();

		foreach ( $this->endpoints as $endpoint ) {
			$endpoint->register_routes();
		}
	}
}
