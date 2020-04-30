<?php

namespace CAC\GroupLibrary\Screens;

use CAC\GroupLibrary\LibraryItem\Query;
use CAC\GroupLibrary\Folder;

class Library {
	public static function panel() {
		add_action(
			'bp_template_content',
			function() {
				bp_register_template_stack( array( '\CAC\GroupLibrary\App', 'template_stack' ), 14 );
				bp_get_template_part( 'groups/single/library' );
				bp_deregister_template_stack( array( '\CAC\GroupLibrary\App', 'template_stack' ), 14 );
			}
		);

		wp_enqueue_style( 'cac-group-library', CAC_GROUP_LIBRARY_PLUGIN_URL . '/assets/css/frontend.css', [], CAC_GROUP_LIBRARY_VER );
		wp_enqueue_script( 'cac-group-library-runtime', CAC_GROUP_LIBRARY_PLUGIN_URL . '/assets/js/runtime.js', [], CAC_GROUP_LIBRARY_VER, true );
		wp_enqueue_script( 'cac-group-library-vendors', CAC_GROUP_LIBRARY_PLUGIN_URL . '/assets/js/vendors.js', [], CAC_GROUP_LIBRARY_VER, true );
		wp_enqueue_script( 'cac-group-library', CAC_GROUP_LIBRARY_PLUGIN_URL . '/assets/js/frontend.js', [ 'cac-group-library-runtime', 'cac-group-library-vendors' ], CAC_GROUP_LIBRARY_VER, true );

		if ( is_user_logged_in() ) {
			wp_enqueue_editor();
		}

		$folders = Folder::get_folders_of_group( bp_get_current_group_id() );

		wp_localize_script(
			'cac-group-library',
			'CACGroupLibrary',
			[
				'appUrlBase'     => bp_get_group_permalink( groups_get_current_group() ) . '/library/',
				'canCreateNew'   => groups_is_user_member( bp_loggedin_user_id(), bp_get_current_group_id() ),
				'endpointBase'   => home_url() . '/wp-json/cacgl/v1/',
				'foldersOfGroup' => $folders,
				'groupId'        => bp_get_current_group_id(),
				'imgUrlBase'     => CAC_GROUP_LIBRARY_PLUGIN_URL . '/assets/img/',
				'iconUrlBase'    => CAC_GROUP_LIBRARY_PLUGIN_URL . '/assets/img/file-type-icons/',
				'nonce'          => wp_create_nonce( 'wp_rest' ),
			]
		);

		bp_core_load_template( 'groups/single/plugins' );
	}
}
