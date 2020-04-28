<?php

namespace CAC\GroupLibrary\Endpoints;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

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
		return is_user_logged_in();
	}

	/**
	 * Creates an invitation event.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create_item( $request ) {
		$params = $request->get_params();
		_b( $params ); return;

		$users = array(
			'email' => $params['usersByEmail'],
			'id' => array_map( 'intval', array_keys( $params['usersById'] ) ),
		);

		$event = new Event();

		$event->set_user_id( get_current_user_id() );

		$timestamp = time();
		$event->set_timestamp( time() );

		$retval = array(
			'success' => false,
		);

		foreach ( $users as $identifier_type => $identifiers ) {
			foreach ( $identifiers as $identifier ) {
				$i = new Invitation();

				if ( 'email' === $identifier_type ) {
					$i->set_invitee_email( $identifier );
				} else {
					$i->set_invitee_id( $identifier );
				}

				$i->set_inviter_id( get_current_user_id() );
				$i->set_date_created( date( 'Y-m-d H:i:s', $timestamp ) );
				$i->set_message( $params['customMessage'] );

				foreach ( $params['membershipItems']['group'] as $group_id ) {
					$group_role = isset( $params['groupRoles'][ $group_id ] ) ? $params['groupRoles'][ $group_id ] : 'member';

					// If the user is already a member of the group, omit the invitation.
					$i->add_group_membership( $group_id, $group_role );
				}

				foreach ( $params['membershipItems']['site'] as $site_id ) {
					$site_role = isset( $params['siteRoles'][ $site_id ] ) ? $params['siteRoles'][ $site_id ] : 'member';
					$i->add_site_membership( $site_id, $site_role );
				}

				$saved = $i->save();

				$event->add_invitation( $i );
			}
		}

		$event->process();

		// @todo More fine-grained success/failure?
		$retval['success'] = true;

		return rest_ensure_response( $retval );
	}
}
