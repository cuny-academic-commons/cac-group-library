<?php

namespace CAC\GroupLibrary\Sync;

use CAC\GroupLibrary\LibraryItem\Item;
use CAC\GroupLibrary\LibraryItem\Query;

use \WP_Post;

class ForumAttachmentsSync implements SyncInterface {
	public static function set_up_sync_hooks() {
//		add_action( 'bp_docs_after_save', [ __CLASS__, 'sync_to_library' ], 999 );
//		add_action( 'trashed_post', [ __CLASS__, 'delete_library_item' ] );
//		add_action( 'delete_post', [ __CLASS__, 'delete_library_item' ] );
	}

	public static function get_library_item_from_source_item_id( $post_id, $group_id ) {
		$found = Query::get(
			[
				'group_id'       => $group_id,
				'item_type'      => 'forum_attachment',
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

		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}

		if ( 'trash' === $post->post_status ) {
			self::delete_library_item( $post_id );
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

		$parent = get_post( $post->post_parent );
		if ( 'reply' === $parent->post_type ) {
			$topic_id = bbp_get_reply_topic_id( $parent->ID );
		} else {
			$topic_id = $parent->ID;
		}

		$forum_id = bbp_get_topic_forum_id( $topic_id );

		global $wpdb, $bp;
		$group_id = $wpdb->get_var( $wpdb->prepare( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'forum_id' AND meta_value = %s", $forum_id ) );

		if ( ! $group_id ) {
			return;
		}

		$file      = get_attached_file( $post_id );
		$file_type = '';
		if ( $file ) {
			$path_parts = pathinfo( $file );
			$file_type  = $path_parts['extension'];
		}

		$item = self::get_library_item_from_source_item_id( $post_id, $group_id );

		$item->set_date_modified( $date );
		$item->set_group_id( $group_id );
		$item->set_item_type( 'forum_attachment' );
		$item->set_file_type( $file_type );
		$item->set_source_item_id( $post_id );
		$item->set_title( $post->post_title );
		$item->set_url( get_permalink( $post ) );
		$item->set_user_id( $post->post_author );

		$item->save();
	}

	public static function delete_library_item( $post_id ) {
		$post = get_post( $post_id );

		if ( ( ! ( $post instanceof WP_Post ) ) || 'attachment' !== $post->post_type ) {
			return false;
		}

		$item = self::get_library_item_from_source_item_id( $post_id, $group_id );

		if ( ! $item->exists() ) {
			return false;
		}

		return $item->delete();
	}
}
