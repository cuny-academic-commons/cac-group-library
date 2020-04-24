<?php

namespace CAC\GroupLibrary\CLI\Command;

use \WP_CLI;
use \WP_CLI_Command;

use CAC\GroupLibrary\Sync\BuddyPressDocsSync;
use CAC\GroupLibrary\Sync\BuddyPressGroupDocumentsSync;
use CAC\GroupLibrary\Sync\ForumAttachmentsSync;

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
	 *   - cacap_paper
	 *   - forum_attachment
	 */
	public function __invoke( $args, $assoc_args ) {
		$item_type = $args[0];

		switch ( $item_type ) {
			case 'bp_doc' :
				$this->sync_bp_doc();
			break;

			case 'bp_group_document' :
				$this->sync_bp_group_document();
			break;

			case 'forum_attachment' :
				$this->sync_forum_attachment();
			break;
		}
	}

	protected function sync_bp_doc() {
		global $wpdb;

		$doc_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'bp_doc' AND post_status = 'publish'" );

		$progress = WP_CLI\Utils\make_progress_bar( 'Syncing BuddyPress Docs items', count( $doc_ids ) );

		foreach ( $doc_ids as $doc_id ) {
			BuddyPressDocsSync::sync_to_library( $doc_id, [ 'date_type' => 'date_modified' ] );
			$progress->tick();
		}

		$progress->finish();
	}

	protected function sync_bp_group_document() {
		global $wpdb, $bp;

		$doc_ids = $wpdb->get_col( "SELECT id FROM {$bp->group_documents->table_name}" );

		$progress = WP_CLI\Utils\make_progress_bar( 'Syncing BuddyPress Group Documents items', count( $doc_ids ) );

		foreach ( $doc_ids as $doc_id ) {
			BuddyPressGroupDocumentsSync::sync_to_library( $doc_id, [ 'date_type' => 'date_modified' ] );
			$progress->tick();
		}

		$progress->finish();
	}

	protected function sync_forum_attachment() {
		global $wpdb;

		$att_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent IN ( SELECT ID FROM {$wpdb->posts} WHERE post_type in ('forum', 'topic', 'reply') )" );

		$progress = WP_CLI\Utils\make_progress_bar( 'Syncing forum attachments', count( $att_ids ) );

		foreach ( $att_ids as $att_id ) {
			ForumAttachmentsSync::sync_to_library( $att_id, [ 'date_type' => 'date_modified' ] );
			$progress->tick();
		}

		$progress->finish();
	}
}
