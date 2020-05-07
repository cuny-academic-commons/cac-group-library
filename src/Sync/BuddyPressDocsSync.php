<?php

namespace CAC\GroupLibrary\Sync;

use CAC\GroupLibrary\LibraryItem\Item;
use CAC\GroupLibrary\LibraryItem\Query;

use \WP_Post;

class BuddyPressDocsSync implements SyncInterface {
	public static function set_up_sync_hooks() {
		add_action( 'bp_docs_after_save', [ __CLASS__, 'sync_to_library' ], 999 );
		add_action( 'trashed_post', [ __CLASS__, 'delete_library_item' ] );
		add_action( 'delete_post', [ __CLASS__, 'delete_library_item' ] );
	}

	public static function get_library_item_from_source_item_id( $post_id, $group_id ) {
		$found = Query::get(
			[
				'group_id'       => $group_id,
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

	public static function sync_to_library( $post_id, $args = [] ) {
		$post = get_post( $post_id );

		if ( ( ! ( $post instanceof WP_Post ) ) || bp_docs_get_post_type_name() !== $post->post_type ) {
			return;
		}

		if ( 'trash' === $post->post_status ) {
			self::delete_library_item( $post_id );
			return;
		}

		if ( 'publish' !== $post->post_status ) {
			return;
		}

		$r = array_merge(
			[
				'date_type' => 'now',
			],
			$args
		);

		switch ( $r['date_type'] ) {
			case 'now' :
				$date = date( 'Y-m-d H:i:s' );
			break;

			default :
				$date = $post->post_modified;
			break;
		}

		$group_id = bp_docs_get_associated_group_id( $post_id );

		$item = self::get_library_item_from_source_item_id( $post_id, $group_id );

		$item->set_date_modified( $date );
		$item->set_group_id( $group_id );
		$item->set_item_type( 'bp_doc' );
		$item->set_source_item_id( $post_id );
		$item->set_title( $post->post_title );
		$item->set_url( get_permalink( $post ) );
		$item->set_user_id( $post->post_author );

		$item->save();
	}

	public static function delete_library_item( $post_id ) {
		$post = get_post( $post_id );

		if ( ( ! ( $post instanceof WP_Post ) ) || bp_docs_get_post_type_name() !== $post->post_type ) {
			return false;
		}

		$item = self::get_library_item_from_source_item_id( $post_id, $group_id );

		if ( ! $item->exists() ) {
			return false;
		}

		return $item->delete();
	}

	public static function silence_update() {
		add_action( 'bp_ass_send_activity_notification_for_user', '__return_false', 100 );
		add_action( 'bp_ges_add_to_digest_queue_for_user', '__return_false', 100 );
	}
}
