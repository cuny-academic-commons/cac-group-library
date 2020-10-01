<?php

namespace CAC\GroupLibrary\Endpoints;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

use CAC\GroupLibrary\Folder;
use CAC\GroupLibrary\LibraryItem\Query;

/**
 * potential-parent-docs endpoint.
 */
class FoldersOfGroup extends WP_REST_Controller {
	/**
	 * Register endpoint routes.
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'cacgl/v' . $version;

		register_rest_route( $namespace, '/folders-of-group', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( $namespace, '/folders-of-group', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'edit_item' ),
				'permission_callback' => array( $this, 'edit_items_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
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

		$folders = Folder::get_folders_of_group( $group_id );

		return rest_ensure_response( [
			'success' => true,
			'results' => $folders,
		] );
	}

	/**
	 * Permission check for editing item.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public function edit_items_permissions_check( $request ) {
		$group_id = $request->get_param( 'groupId' );

		if ( ! $group_id ) {
			return false;
		}

		$group = groups_get_group( $group_id );

		if ( ! $group->id ) {
			return false;
		}

		if ( current_user_can( 'bp_moderate' ) ) {
			return true;
		}

		return groups_is_user_admin( get_current_user_id(), $group_id );
	}

	public function edit_item( $request ) {
		$delete_type = $request->get_param( 'deleteType' );
		if ( $delete_type ) {
			return $this->delete_item( $request );
		}

		$group_id = $request->get_param( 'groupId' );

		$retval = [
			'success' => false,
			'message' => '',
		];

		$folder_name = $request->get_param( 'folderName' );
		$edit_value  = $request->get_param( 'editValue' );

		if ( ! $folder_name || ! $edit_value ) {
			$retval['message'] = 'You must provide a folderName and editValue.';
			return rest_ensure_response( $retval );
		}

		$folder = Folder::get_group_folder_by_name( $group_id, $folder_name );

		$folder->set_name( $edit_value );
		$folder->save();

		$retval['success'] = true;

		return rest_ensure_response( $retval );
	}

	public function delete_item( $request ) {
		$delete_type = $request->get_param( 'deleteType' );
		$group_id    = $request->get_param( 'groupId' );
		$folder_name = $request->get_param( 'folderName' );

		$retval = [
			'success' => false,
			'message' => '',
		];

		if ( ! $folder_name || ! $group_id ) {
			$retval['message'] = 'You must provide a folderName and groupId.';
			return rest_ensure_response( $retval );
		}

		$folder = Folder::get_group_folder_by_name( $group_id, $folder_name );

		_b( $folder );
		if ( 'deleteFolderAndContents' === $delete_type ) {
			$group_items = Query::get(
				[
					'group_id' => $group_id,
				]
			);

			foreach ( $group_items as $group_item ) {
				$folders = $group_item->get_folders();

				if ( ! in_array( $folder_name, $folders, true ) ) {
					continue;
				}
			}
		}

		$folder->delete();

		$retval['success'] = true;

		return rest_ensure_response( $retval );
	}
}
