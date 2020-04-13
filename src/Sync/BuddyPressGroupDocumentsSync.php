<?php

namespace CAC\GroupLibrary\Sync;

use CAC\GroupLibrary\LibraryItem\Item;
use CAC\GroupLibrary\LibraryItem\Query;

use \BP_Group_Documents;

class BuddyPressGroupDocumentsSync implements SyncInterface {
	public static function set_up_sync_hooks() {
		add_action(
			'bp_group_documents_add_success',
			function( $document ) {
				self::sync_to_library( $document->id );
			}
		);

		add_action(
			'bp_group_documents_data_before_delete',
			function( $document ) {
				self::delete_library_item( $document->id );
			}
		);
	}

	public static function get_library_item_from_source_item_id( $document_id, $group_id ) {
		$found = Query::get(
			[
				'group_id'       => $group_id,
				'item_type'      => 'bp_group_document',
				'source_item_id' => $document_id,
			]
		);

		if ( ! $found ) {
			$item = new Item();
		} else {
			$item = reset( $found );
		}

		return $item;
	}

	public static function sync_to_library( $document_id ) {
		$document = new BP_Group_Documents( $document_id );

		$group_id = $document->group_id;

		$item = self::get_library_item_from_source_item_id( $post_id, $group_id );

		$item->set_date_modified( date( 'Y-m-d H:i:s' ) );
		$item->set_group_id( $group_id );
		$item->set_item_type( 'bp_group_document' );
		$item->set_source_item_id( $document_id );
		$item->set_title( $document->name );
		$item->set_url( $document->get_url() );
		$item->set_user_id( $document->user_id );

		$item->save();
	}

	public static function delete_library_item( $document_id ) {
		$document = new BP_Group_Documents( $document_id );

		$group_id = $document->group_id;

		$item = self::get_library_item_from_source_item_id( $document_id, $group_id );

		if ( ! $item->exists() ) {
			return false;
		}

		return $item->delete();
	}
}
