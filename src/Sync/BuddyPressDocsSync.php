<?php

namespace CAC\GroupLibrary\Sync;

use CAC\GroupLibrary\LibraryItem\Item;
use CAC\GroupLibrary\LibraryItem\Query;

use \WP_Post;

class BuddyPressDocsSync implements SyncInterface {
	public static function set_up_sync_hooks() {
		add_action( 'bp_docs_after_save', [ __CLASS__, 'sync_to_library' ], 999 );
		add_action( 'bp_docs_doc_deleted', [ __CLASS__, 'delete_library_item' ] );
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
			self::delete_library_item( [ 'ID' => $post_id ] );
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
		$item->set_url( bp_docs_get_doc_link( $post_id ) );
		$item->set_user_id( $post->post_author );

		$item->save();
	}

	public static function delete_library_item( $args ) {
		$post = get_post( $args['ID'] );

		if ( ( ! ( $post instanceof WP_Post ) ) || bp_docs_get_post_type_name() !== $post->post_type ) {
			return false;
		}

		$group_id = bp_docs_get_associated_group_id( $post->ID );

		$item = self::get_library_item_from_source_item_id( $post->ID, $group_id );

		if ( ! $item->exists() ) {
			return false;
		}

		return $item->delete();
	}
}
