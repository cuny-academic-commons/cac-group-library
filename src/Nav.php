<?php

namespace CAC\GroupLibrary;

use CAC\GroupLibrary\Sync\BuddyPressDocsSync;

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
		add_action( 'bp_setup_nav', array( $this, 'remove_group_documents_admin_nav' ), 1 );
		add_action( 'bp_setup_nav', array( $this, 'add_library_nav_item' ), 200 );
		add_action( 'bp_actions', array( $this, 'remove_nav_items' ), 9 );
		add_action( 'bp_actions', array( $this, 'redirect_from_legacy_panels' ) );
	}

	public function remove_group_documents_admin_nav() {
		remove_action( 'bp_setup_nav', 'bp_group_documents_group_admin_nav', 2 );
		remove_action( 'wp', 'bp_group_documents_group_admin_settings', 4 );
	}

	public function add_library_nav_item() {
		if ( ! bp_is_group() ) {
			return;
		}

		$group = groups_get_current_group();

		$user_has_access = current_user_can( 'bp_moderate' ) || 'public' === $group->status || groups_is_user_member( bp_loggedin_user_id(), $group->id );

		bp_core_new_subnav_item(
			array(
				'slug'            => cac_group_library()->get_prop( 'nav_slug' ),
				'parent_slug'     => $group->slug,
				'parent_url'      => bp_get_group_url( $group ),
				'name'            => 'Library',
				'screen_function' => array( '\CAC\GroupLibrary\Screens\Library', 'panel' ),
				'position'        => 32,
				'user_has_access' => $user_has_access,
			),
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

	/**
	 * Redirect away from legacy panels that are now controlled by Library.
	 */
	public function redirect_from_legacy_panels() {
		if ( ! bp_is_group() ) {
			return;
		}

		$redirect    = false;
		$redirect_to = bp_get_group_url( groups_get_current_group() ) . cac_group_library()->get_prop( 'nav_slug' );

		// BP Group Documents
		if ( bp_is_group() && bp_is_current_action( BP_GROUP_DOCUMENTS_SLUG ) ) {
			$redirect = true;
		}

		// BuddyPress Docs
		if ( bp_is_group() && bp_is_current_action( bp_docs_get_docs_slug() ) ) {
			// Only when looking at the group listing, on Edit, or on Create.
			if ( ! bp_action_variables() || bp_is_action_variable( BP_DOCS_CREATE_SLUG, 0 ) ) {
				$redirect = true;

				// If on Doc listing, redirect to library filtered by docs.
				if ( ! bp_action_variables() ) {
					$redirect_to .= '/#/?itemType=bp_doc';
				}

			} elseif ( bp_docs_is_doc_edit() ) {
				$doc  = bp_docs_get_current_doc();
				$item = BuddyPressDocsSync::get_library_item_from_source_item_id( $doc->ID, bp_get_current_group_id() );

				$redirect     = true;
				$redirect_to .= '/#/edit/' . $item->get_id();
			}
		}

		// Social Paper
		if ( bp_is_group() && bp_is_current_action( 'papers' ) ) {
			$redirect = true;
		}

		if ( $redirect ) {
			bp_core_redirect( $redirect_to );
		}
	}
}
