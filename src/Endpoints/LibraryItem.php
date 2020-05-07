<?php

namespace CAC\GroupLibrary\Endpoints;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

use \CAC\GroupLibrary\LibraryItem\Item;
use \CAC\GroupLibrary\LibraryItem\Query;
use \CAC\GroupLibrary\Folder;
use \CAC\GroupLibrary\Sync\BuddyPressGroupDocumentsSync;
use \CAC\GroupLibrary\Sync\BuddyPressDocsSync;

use \BP_Group_Documents;
use \BP_Docs_Query;

/**
 * library-item endpoint.
 */
class LibraryItem extends WP_REST_Controller {
	/**
	 * Register endpoint routes.
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'cacgl/v' . $version;

		register_rest_route( $namespace, '/library-items', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( $namespace, '/library-items/(?P<item_id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'edit_item' ),
				'permission_callback' => array( $this, 'edit_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( $namespace, '/library-items/(?P<item_id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::DELETABLE,
				'callback'        => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( $namespace, '/library-items', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
	}

	/**
	 * Permission check for creating item.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$group_id = $request->get_param( 'groupId' );

		if ( ! $group_id || ! groups_is_user_member( get_current_user_id(), $group_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Creates a library item.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create_item( $request ) {
		$params = $request->get_params();

		$retval = [
			'success' => false,
			'message' => '',
		];

		$item_type = isset( $params['itemType'] ) ? $params['itemType'] : '';
		if ( ! $item_type ) {
			return rest_ensure_response( $retval );
		}

		if ( ! empty( $params['silentUpdate'] ) ) {
			$this->silence_update();
		}

		switch ( $item_type ) {
			case 'externalLink' :
				$retval = $this->save_external_link( $params );
			break;

			case 'bpGroupDocument' :
				$file_params = $request->get_file_params();
				$retval = $this->save_bp_group_document( $params, $file_params['file'] );
			break;

			case 'bpDoc' :
				$retval = $this->create_bp_doc( $params );
			break;
		}

		return rest_ensure_response( $retval );
	}

	/**
	 * Permission check for editing item.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function edit_item_permissions_check( $request ) {
		$item_id = $request->get_param( 'item_id' );
		$item    = new Item( $item_id );

		if ( ! $item->exists() ) {
			return false;
		}

		return $item->get_can_edit( get_current_user_id() );
	}

	/**
	 * Edits a library item.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function edit_item( $request ) {
		$params  = $request->get_params();
		$item_id = $request->get_param( 'item_id' );

		$retval = [
			'success' => false,
			'message' => '',
		];

		$item = new Item( $item_id );
		if ( ! $item->exists() ) {
			return $retval;
		}

		switch ( $item->get_item_type() ) {
			case 'external_link' :
				$retval = $this->save_external_link( $params );
			break;

			case 'bp_group_document' :
				$file_params = $request->get_file_params();
				$retval = $this->save_bp_group_document( $params, $file_params['file'] );
			break;
		}

		return rest_ensure_response( $retval );
	}

	/**
	 * Permission check for deleting item.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function delete_item_permissions_check( $request ) {
		$item_id = $request->get_param( 'item_id' );
		$item    = new Item( $item_id );

		if ( ! $item->exists() ) {
			return false;
		}

		return $item->get_can_edit( get_current_user_id() );
	}

	/**
	 * Deletes a library item.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete_item( $request ) {
		$item_id = $request->get_param( 'item_id' );

		$retval = [
			'success' => false,
			'message' => '',
		];

		$item = new Item( $item_id );
		if ( ! $item->exists() ) {
			return $retval;
		}

		switch ( $item->get_item_type() ) {
			case 'external_link' :
				$retval = $this->delete_external_link( $item_id );
			break;

			case 'bp_group_document' :
				$retval = $this->delete_bp_group_document( $item_id );
			break;
		}

		return rest_ensure_response( $retval );
	}

	protected function save_external_link( $params ) {
		$retval = [
			'success' => false,
			'message' => '',
		];

		$group_id = $params['groupId'];

		if ( empty( $params['title'] ) || empty( $params['url'] ) ) {
			$retval['message'] = 'You must provide a title and a link.';
			return $retval;
		}

		$item_id = ! empty( $params['item_id'] ) ? (int) $params['item_id'] : null;

		$library_item = new Item( $item_id );
		$library_item->set_date_modified( date( 'Y-m-d H:i:s' ) );
		$library_item->set_group_id( $group_id );
		$library_item->set_item_type( 'external_link' );
		$library_item->set_user_id( get_current_user_id() );
		$library_item->set_title( $params['title'] );
		$library_item->set_url( $params['url'] );

		if ( ! empty( $params['folder'] ) ) {
			if ( '_addNew' === $params['folder'] ) {
				$library_item->set_folders( [ $params['newFolderTitle'] ] );
			} else {
				$library_item->set_folders( [ $params['folder'] ] );
			}
		}

		$saved = $library_item->save();

		if ( $saved ) {
			$retval['success'] = true;
			$retval['message'] = $item_id ? 'Your external link was updated successfully.' : 'Your external link was added successfully';
		}

		return rest_ensure_response( $retval );
	}

	protected function delete_external_link( $item_id ) {
		$item = new Item( $item_id );

		$retval = [
			'success' => false,
			'message' => '',
		];

		if ( $item->delete() ) {
			$retval['success'] = true;
			$retval['message'] = 'Your external link was deleted successfully.';
		}

		return $retval;
	}

	protected function save_bp_group_document( $params, $file_params ) {
		$retval = [
			'success' => false,
		];

		$error_message = '';
		if ( ! empty( $file_params['error'] ) ) {
			switch( $file_params['error'] ) {
				case UPLOAD_ERR_INI_SIZE:
					$error_message = 'There was a problem; your file is larger than is allowed by the site administrator.';
				break;
				case UPLOAD_ERR_PARTIAL:
					$error_message = 'There was a problem; the file was only partially uploaded.';
				break;
				case UPLOAD_ERR_NO_FILE:
					$error_message = 'There was a problem; no file was found for the upload.';
				break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$error_message = 'There was a problem; the temporary folder for the file is missing.';
				break;
				case UPLOAD_ERR_CANT_WRITE:
					$error_message = 'There was a problem; the file could not be saved.';
				break;
			}
		}

		if ( $error_message ) {
			$retval['message'] = $error_message;
			return $retval;
		}

		// If the user didn't specify a display name, use the file name (before the timestamp).
		if ( empty( $params['title'] ) ) {
			$params['title'] = basename( $file_params['name'] );
		}

		$doc_id  = null;
		$item_id = ! empty( $params['itemId'] ) ? (int) $params['itemId'] : null;
		if ( $item_id ) {
			$item   = new Item( $item_id );
			$doc_id = $item->get_source_item_id();
		}

		$doc = new BP_Group_Documents( $doc_id );

		$doc->user_id     = get_current_user_id();
		$doc->group_id    = $params['groupId'];
		$doc->name        = $params['title'];
		$doc->description = $params['description'];
		$doc->featured    = false;

		// Only provide file info on creation.
		if ( ! $item_id ) {
			$doc->file = basename( $file_params['name'] );

			$file_path = $doc->get_path( 0,1 );

			if ( ! move_uploaded_file( $file_params['tmp_name'], $file_path ) ) {
				$error_message = 'There was a problem saving your file, please try again.';
			}
		}

		$saved = $doc->save( false );

		if ( ! $saved ) {
			return $retval;
		}

		// @todo This does nothing at the moment.
		$silent = ! empty( $_POST['bp_group_documents_silent_add'] );
		do_action( 'bp_group_documents_add_success', $doc, $silent );

		if ( ! empty( $params['folder'] ) ) {
			if ( '_addNew' === $params['folder'] ) {
				$folder_name = $params['newFolderTitle'];
			} else {
				$folder_name = $params['folder'];
			}

			$gd_category = get_term_by( 'name', $folder_name, 'group-documents-category' );

			if ( ! $gd_category ) {
				$term_info = wp_insert_term( $folder_name, 'group-documents-category' );
				$term_id   = $term_info['term_id'];
			} else {
				$term_id = $gd_category->term_id;
			}

			wp_set_object_terms( $doc->id, [ $term_id ], 'group-documents-category' );

			// Resync.
			BuddyPressGroupDocumentsSync::sync_to_library( $doc->id );
		}

		$retval['success'] = true;

		if ( $item_id ) {
			$retval['message'] = 'Your update was successful.';
		} else {
			$retval['message'] = 'Your file was uploaded successfully.';
		}

		return $retval;
	}

	protected function delete_bp_group_document( $item_id ) {
		global $wpdb, $bp;

		$retval = [
			'success' => false,
			'message' => '',
		];

		$item   = new Item( $item_id );
		$doc_id = $item->get_source_item_id();

		// Reproduce logic here because in the plugin there's a hardcoded permission check.
		$doc = new BP_Group_Documents( $doc_id );
		do_action( 'bp_group_documents_data_before_delete', $doc );
		if ( $doc->file && file_exists( $doc->get_path(1) ) ) {
			@unlink( $doc->get_path(1) );
		}

		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->group_documents->table_name} WHERE id = %d", $doc_id ) );

		if ( $deleted ) {
			$retval['success'] = true;
			$retval['message'] = 'Your file has been successfully deleted.';
		}

		return $retval;
	}

	protected function create_bp_doc( $params ) {
		$create_args = [
			'title'     => $params['title'],
			'content'   => $params['content'],
			'author_id' => get_current_user_id(),
			'group_id'  => $params['groupId'],
			'parent_id' => isset( $params['parent']['code'] ) ? intval( $params['parent']['code'] ) : 0,
		];

		$docs_query = new BP_Docs_Query();
		$created    = $docs_query->save( $create_args );

		$retval = [
			'success' => false,
			'message' => '',
		];

		if ( empty( $created['doc_id'] ) ) {
			return $retval;
		}

		$retval['success'] = true;
		$retval['message'] = 'Your doc was created successfully';

		if ( ! empty( $params['folder'] ) ) {
			if ( '_addNew' === $params['folder'] ) {
				$folder_name = $params['newFolderTitle'];
			} else {
				$folder_name = $params['folder'];
			}

			$library_item = BuddyPressDocsSync::get_library_item_from_source_item_id( $created['doc_id'], $params['groupId'] );
			$library_item->set_folders( [ $folder_name ] );
			$library_item->save();
		}

		return $retval;
	}

	/**
	 * Permission check for getting item.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		$group_id = $request->get_param( 'groupId' );

		// Only group-specific items for now.
		if ( ! $group_id ) {
			return false;
		}

		$group = groups_get_group( $group_id );

		if ( ! $group->id ) {
			return false;
		}

		if ( 'public' === $group->status ) {
			return true;
		}

		return groups_is_user_member( get_current_user_id(), $group_id );
	}

	/**
	 * Gets a list of library items.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$params = $request->get_params();

		$group_id = $params['groupId'];

		$results = Query::get_for_endpoint(
			[
				'group_id' => $group_id,
			]
		);

		return rest_ensure_response( [
			'success' => true,
			'results' => $results,
		] );
	}

	protected function silence_update() {
		add_action( 'bp_ass_send_activity_notification_for_user', '__return_false', 100 );
		add_action( 'bp_ges_add_to_digest_queue_for_user', '__return_false', 100 );
	}
}
