<?php

namespace CAC\GroupLibrary\Sync;

use CAC\GroupLibrary\LibraryItem\Item;
use CAC\GroupLibrary\LibraryItem\Query;

use \WP_Post;

class ForumAttachmentsSync implements SyncInterface {
	public static function set_up_sync_hooks() {
		add_action( 'add_attachment', [ __CLASS__, 'sync_to_library' ], 999 );
		add_action( 'trashed_post', [ __CLASS__, 'delete_library_item' ] );
		add_action( 'delete_post', [ __CLASS__, 'delete_library_item' ] );
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

		$group_id = self::get_group_id( $post_id );
		if ( ! $group_id ) {
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

		$file      = get_attached_file( $post_id );
		$file_type = '';
		if ( $file ) {
			$path_parts = pathinfo( $file );
			$file_type  = $path_parts['extension'];
		}

		$file_url = wp_get_attachment_url( $post_id );

		$item = self::get_library_item_from_source_item_id( $post_id, $group_id );

		$item->set_date_modified( $date );
		$item->set_group_id( $group_id );
		$item->set_item_type( 'forum_attachment' );
		$item->set_file_type( $file_type );
		$item->set_source_item_id( $post_id );
		$item->set_title( $post->post_title );
		$item->set_url( $file_url );
		$item->set_user_id( $post->post_author );

		$item->save();
	}

	public static function delete_library_item( $post_id ) {
		$post = get_post( $post_id );

		if ( ( ! ( $post instanceof WP_Post ) ) || 'attachment' !== $post->post_type ) {
			return false;
		}

		$group_id = self::get_group_id( $post_id );
		if ( ! $group_id ) {
			return;
		}

		$item = self::get_library_item_from_source_item_id( $post_id, $group_id );

		if ( ! $item->exists() ) {
			return false;
		}

		return $item->delete();
	}

	protected static function get_group_id( $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( ! ( $attachment instanceof WP_Post ) || 'attachment' !== $attachment->post_type ) {
			return 0;
		}

		$parent = get_post( $attachment->post_parent );
		if ( ! ( $parent instanceof WP_Post ) || ! in_array( $parent->post_type, [ bbp_get_topic_post_type(), bbp_get_reply_post_type() ], true ) ) {
			return 0;
		}

		if ( 'reply' === $parent->post_type ) {
			$topic_id = bbp_get_reply_topic_id( $parent->ID );
		} else {
			$topic_id = $parent->ID;
		}

		$forum_id = bbp_get_topic_forum_id( $topic_id );

		$group_ids = get_post_meta( $forum_id, '_bbp_group_ids', true );
		if ( $group_ids ) {
			$group_id = intval( reset( $group_ids ) );
		} else {
			$group_id = 0;
		}

		return $group_id;
	}
}
