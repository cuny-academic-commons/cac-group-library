<?php

namespace CAC\GroupLibrary;

class App {
	protected $api;
	protected $nav;
	protected $schema;

	public $frontend;

	protected $post_type = 'cac_library_item';

	/**
	 * Main nav slug.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $nav_slug = 'library';

	/**
	 * Method to fetch any non-static property.
	 *
	 * @since 0.1.0
	 *
	 * @param  string     $prop Prop to fetch
	 * @return mixed|null Null on failure.
	 */
	public function get_prop( $prop ) {
		if ( isset( $this->{$prop} ) ) {
			return $this->{$prop};
		}

		return null;
	}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function init() {
		$this->schema = Schema::get_instance();
		$this->schema->init();

		$this->nav = Nav::get_instance();
		$this->nav->init();

		$this->api = API::get_instance();
		$this->api->init();

		add_action(
			'plugins_loaded',
			function() {
				$this->set_up_item_type_sync();
			},
			999
		);

		$this->group_doc_taxonomy_mods();

		if ( defined( 'WP_CLI' ) ) {
			$this->set_up_cli_commands();
		}
	}

	protected function set_up_item_type_sync() {
		$item_type_syncs = [];

		if ( function_exists( 'bp_docs_get_post_type_name' ) ) {
			$item_type_syncs[] = 'BuddyPressDocsSync';
		}

		if ( defined( 'BP_GROUP_DOCUMENTS_IS_INSTALLED' ) && BP_GROUP_DOCUMENTS_IS_INSTALLED ) {
			$item_type_syncs[] = 'BuddyPressGroupDocumentsSync';
		}

		if ( defined( 'GDBBPRESSATTACHMENTS_CAP' ) ) {
			$item_type_syncs[] = 'ForumAttachmentsSync';
		}

		foreach ( $item_type_syncs as $item_type_sync ) {
			$class_name = __NAMESPACE__ . '\Sync\\' . $item_type_sync;
			call_user_func( [ $class_name, 'set_up_sync_hooks' ] );
		}
	}

	/**
	 * Method to return the absolute path to our template directory.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public static function template_stack() {
		return CAC_GROUP_LIBRARY_PLUGIN_DIR . '/templates/';
	}

	/**
	 * Method to allow developers to filter various properties.
	 *
	 * @since 0.1.0
	 */
	protected function props() {
		/**
		 * Filters the main nav slug.
		 *
		 * @since 0.1.0
		 *
		 * @param string $nav_slug
		 */
		$this->nav_slug = apply_filters( 'cac_onboarding_nav_slug', $this->nav_slug );

		/**
		 * Filters the subnav slug for "Invitations Received" page.
		 *
		 * Not usually used.
		 *
		 * @since 0.1.0
		 *
		 * @param string $subnav_slug
		 */
		$this->subnav_slug_received = apply_filters( 'cac_onboarding_subnav_slug_received', $this->subnav_slug_received );

		/**
		 * Filters the subnav slug for "Invitations Sent" page.
		 *
		 * @since 0.1.0
		 *
		 * @param string $subnav_slug
		 */
		$this->subnav_slug_sent = apply_filters( 'cac_onboarding_subnav_slug_sent', $this->subnav_slug_sent );

		/**
		 * Filters the position where our menu is placed.
		 *
		 * Specifically, in the BuddyPress user nav and the admin bar account menus.
		 *
		 * @since 0.1.0
		 *
		 * @param int $nav_position
		 */
		$this->nav_position = (int) apply_filters( 'cac_onboarding_nav_position', $this->nav_position );

		// Sanitize slugs.
		foreach ( array( 'nav_slug', 'subnav_slug_received', 'subnav_slug_sent' ) as $slug ) {
			$this->{$slug} = sanitize_title( $this->{$slug} );
		}
	}

	protected function set_up_cli_commands() {
		\WP_CLI::add_command( 'cacgl database', '\CAC\GroupLibrary\CLI\Command\DatabaseCommand' );
		\WP_CLI::add_command( 'cacgl sync', '\CAC\GroupLibrary\CLI\Command\SyncCommand' );
	}

	/**
	 * Ensures 'group-documents-category' taxonomy is not public.
	 */
	protected function group_doc_taxonomy_mods() {
		add_filter(
			'register_group-documents-category_taxonomy_args',
			function( $args ) {
				$args['public'] = false;
				return $args;
			}
		);
	}

	public function __get( $key ) {
		$value = null;

		switch ( $key ) {
			case 'schema':
				$value = $this->{$key};
				break;
		}

		return $value;
	}
}
