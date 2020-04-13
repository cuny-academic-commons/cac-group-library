<?php

namespace CAC\GroupLibrary\Screens;

use CAC\GroupLibrary\LibraryItem\Query;

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

		$items = Query::get_for_endpoint(
			[
				'group_id' => bp_get_current_group_id(),
			]
		);

//		$add_new_url = bp_get_group_permalink( groups_get_current_group() ) . 'library

		wp_localize_script(
			'cac-group-library',
			'CACGroupLibrary',
			[
				'canCreateNew'   => true,
				'iconUrlBase'    => CAC_GROUP_LIBRARY_PLUGIN_URL . '/assets/img/',
				'libraryItemIds' => array_keys( $items ),
				'libraryItems'   => $items,
			]
		);

		bp_core_load_template( 'groups/single/plugins' );
	}
}
