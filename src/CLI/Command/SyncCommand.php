<?php

namespace CAC\GroupLibrary\CLI\Command;

use \WP_CLI;
use \WP_CLI_Command;

use CAC\GroupLibrary\Sync\BuddyPressDocsSync;
use CAC\GroupLibrary\Sync\BuddyPressGroupDocumentsSync;
use CAC\GroupLibrary\Sync\ForumAttachmentsSync;
use CAC\GroupLibrary\Sync\PapersSync;

class SyncCommand extends WP_CLI_Command {
	/**
	 * ## OPTIONS
	 *
	 * <item_type>
	 * : The item type to sync.
	 * ---
	 * options:
	 *   - bp_doc
	 *   - bp_group_document
	 *   - cacsp_paper
	 *   - forum_attachment
	 *
	 * [--group-id=<group_id>]
	 * : ID of the group to be synced. Omit to sync items across all groups.
	 */
	public function __invoke( $args, $assoc_args ) {
		$item_type = $args[0];

		$group_id = isset( $assoc_args['group-id'] ) ? (int) $assoc_args['group-id'] : null;

		switch ( $item_type ) {
			case 'bp_doc' :
				$this->sync_bp_doc( $group_id );
			break;

			case 'bp_group_document' :
				$this->sync_bp_group_document( $group_id );
			break;

			case 'forum_attachment' :
				$this->sync_forum_attachment( $group_id );
			break;

			case 'cacsp_paper' :
				$this->sync_cacsp_paper( $group_id );
			break;
		}
	}

	protected function sync_bp_doc( $group_id = null ) {
		global $wpdb;

		$doc_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'bp_doc' AND post_status = 'publish'" );

		$progress = WP_CLI\Utils\make_progress_bar( 'Syncing BuddyPress Docs items', count( $doc_ids ) );

		foreach ( $doc_ids as $doc_id ) {
			BuddyPressDocsSync::sync_to_library( $doc_id, [ 'date_type' => 'date_modified' ] );
			$progress->tick();
		}

		$progress->finish();
	}

	protected function sync_bp_group_document( $group_id = null ) {
		global $wpdb, $bp;

		if ( $group_id ) {
			$doc_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$bp->group_documents->table_name} WHERE group_id = %d", $group_id ) );
		} else {
			$doc_ids = $wpdb->get_col( "SELECT id FROM {$bp->group_documents->table_name}" );
		}

		$progress = WP_CLI\Utils\make_progress_bar( 'Syncing BuddyPress Group Documents items', count( $doc_ids ) );

		foreach ( $doc_ids as $doc_id ) {
			BuddyPressGroupDocumentsSync::sync_to_library( $doc_id, [ 'date_type' => 'date_modified' ] );
			$progress->tick();
		}

		$progress->finish();
	}

	protected function sync_forum_attachment( $group_id = null ) {
		global $wpdb;

		$att_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent IN ( SELECT ID FROM {$wpdb->posts} WHERE post_type in ('forum', 'topic', 'reply') )" );

		$progress = WP_CLI\Utils\make_progress_bar( 'Syncing forum attachments', count( $att_ids ) );

		foreach ( $att_ids as $att_id ) {
			ForumAttachmentsSync::sync_to_library( $att_id, [ 'date_type' => 'date_modified' ] );
			$progress->tick();
		}

		$progress->finish();
	}

	protected function sync_cacsp_paper( $group_id = null ) {
		global $wpdb;

		$att_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'cacsp_paper' and post_status IN ( 'private', 'publish' )" );

		$progress = WP_CLI\Utils\make_progress_bar( 'Syncing papers', count( $att_ids ) );

		foreach ( $att_ids as $att_id ) {
			PapersSync::sync_to_library( $att_id, [ 'date_type' => 'date_modified' ] );
			$progress->tick();
		}

		$progress->finish();
	}
}
