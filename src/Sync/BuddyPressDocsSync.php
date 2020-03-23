<?php

namespace CAC\GroupLibrary\Sync;

use CAC\GroupLibrary\LibraryItem\Item;
use CAC\GroupLibrary\LibraryItem\Query;

use \WP_Post;

class BuddyPressDocsSync implements SyncInterface {
	public static function set_up_sync_hooks() {
		add_action( 'save_post', [ __CLASS__, 'sync_to_library' ] );
	}

	public static function get_library_item_from_source_item_id( $post_id ) {
		$found = Query::get(
			[
				'item_type'      => 'bp_doc',
				'source_item_id' => $post_id,
			]
		);

		if ( ! $found ) {
			$item = new Item();
		} else {
			$item = reset( $found );
		}

		return $item;
	}

	public static function sync_to_library( $post_id ) {
		$post = get_post( $post_id );

		if ( ( ! ( $post instanceof WP_Post ) ) || bp_docs_get_post_type_name() !== $post->post_type ) {
			return;
		}

		$item = self::get_library_item_from_source_item_id( $post_id );

		$item->set_date_modified( date( 'Y-m-d H:i:s' ) );
		$item->set_group_id( bp_docs_get_associated_group_id( $post_id ) );
		$item->set_item_type( 'bp_doc' );
		$item->set_source_item_id( $post_id );
		$item->set_title( $post->post_title );
		$item->set_url( get_permalink( $post ) );
		$item->set_user_id( $post->post_author );

		$item->save();
	}
}
