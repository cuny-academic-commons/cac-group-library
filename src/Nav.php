<?php

namespace CAC\GroupLibrary;

class Nav {
	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @return CAC\GroupLibrary\Nav
	 */
	private function __construct() {
		return $this;
	}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function init() {
		add_action( 'bp_setup_nav', [ $this, 'add_library_nav_item' ], 200 );
		add_action( 'bp_actions', [ $this, 'remove_nav_items' ], 200 );
	}

	public function add_library_nav_item() {
		if ( ! bp_is_group() ) {
			return;
		}

		$group = groups_get_current_group();

		bp_core_new_subnav_item(
			[
				'slug'            => cac_group_library()->get_prop( 'nav_slug' ),
				'parent_slug'     => $group->slug,
				'parent_url'      => bp_get_group_permalink( $group ),
				'name'            => 'Library',
				'screen_function' => [ '\CAC\GroupLibrary\Screens\Library', 'panel' ],
				'position'        => 32,
			],
			'groups'
		);
	}

	/**
	 * Run at bp_actions in order to run after BP_Group_Extension nav has been set up.
	 */
	public function remove_nav_items() {
		if ( ! bp_is_group() ) {
			return;
		}

		$group = groups_get_current_group();

		buddypress()->groups->nav->delete_nav( BP_GROUP_DOCUMENTS_SLUG, $group->slug );
		buddypress()->groups->nav->delete_nav( bp_docs_get_docs_slug(), $group->slug );
		buddypress()->groups->nav->delete_nav( 'papers', $group->slug );
	}
}
