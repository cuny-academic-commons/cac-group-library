<?php

namespace CAC\GroupLibrary\Endpoints;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

use \CAC\GroupLibrary\LibraryItem\Item;

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

		register_rest_route( $namespace, '/library-item', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
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
	 * Creates an invitation event.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create_item( $request ) {
		$params = $request->get_params();

		$retval = [
			'success' => false,
		];

		$item_type = isset( $params['itemType'] ) ? $params['itemType'] : '';
		if ( ! $item_type ) {
			return rest_ensure_response( $retval );
		}

		switch ( $item_type ) {
			case 'externalLink' :
				$retval = $this->create_external_link( $params );
			break;
		}

		return rest_ensure_response( $retval );
	}

	protected function create_external_link( $params ) {
		$retval = [
			'success' => false,
		];

		$group_id = $params['groupId'];

		if ( empty( $params['title'] ) || empty( $params['url'] ) ) {
			$retval['message'] = 'You must provide a title and a link.';
			return $retval;
		}

		$library_item = new Item();
		$library_item->set_date_modified( date( 'Y-m-d H:i:s' ) );
		$library_item->set_group_id( $group_id );
		$library_item->set_item_type( 'external_link' );
		$library_item->set_user_id( get_current_user_id() );
		$library_item->set_title( $params['title'] );
		$library_item->set_url( $params['url'] );

		// todo add new folder
		if ( ! empty( $params['folder'] ) && '_addNew' !== $params['folder'] ) {
			$library_item->set_folders( [ $params['folder'] ] );
		}

		$saved = $library_item->save();

		if ( $saved ) {
			$retval['success'] = true;
		}

		return rest_ensure_response( $retval );
	}
}
