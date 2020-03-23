<?php

namespace CAC\GroupLibrary;

class App {
	protected $modal;
	protected $api;
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
	 * Subnav slug for "Invitations Received" page.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $subnav_slug_received = 'received';

	/**
	 * Subnav slug for "Invitations Sent" page.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $subnav_slug_sent = 'sent';

	/**
	 * Subnav slug for "Claim an Invitation" page.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $subnav_slug_claim = 'claim';

	/**
	 * Slug for top-level "Claim an Invitation" page.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $slug_claim = 'claim-an-invitation';

	/**
	 * Position to place our menu in the user nav and admin bar.
	 *
	 * @since 0.1.0
	 *
	 * @var int
	 */
	protected $nav_position = 77;

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

		return;

		$this->props();

		if ( defined( 'WP_CLI' ) ) {
			$this->set_up_cli_commands();
		}

		$this->modal = Modal::get_instance();
		$this->modal->init();

		$this->api = API::get_instance();
		$this->api->init();
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
		\WP_CLI::add_command( 'caco database', '\CAC\Onboarding\CLI\Command\DatabaseCommand' );
		\WP_CLI::add_command( 'caco invitation', '\CAC\Onboarding\CLI\Command\InvitationCommand' );
	}
}
