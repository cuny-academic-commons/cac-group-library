<?php

namespace CAC\GroupLibrary\Endpoints;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

use \BP_Docs_Query;

/**
 * potential-parent-docs endpoint.
 */
class PotentialParentDocs extends WP_REST_Controller {
	/**
	 * Register endpoint routes.
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'cacgl/v' . $version;

		register_rest_route( $namespace, '/potential-parent-docs', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
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

		$query_args = [
			'doc_slug'       => false,
			'posts_per_page' => -1,
			'group_id'       => $group_id,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		$doc_query_builder = new BP_Docs_Query( $query_args );
		$doc_query = $doc_query_builder->get_wp_query();

		// @todo Must exclude current on edit.
		$exclude = 0;
		$items = [];
		if ( $doc_query->have_posts() ) {
			while ( $doc_query->have_posts() ) {
				$doc_query->the_post();
				if ( ! $exclude || $exclude !== get_the_ID() ) {
					$items[] = [
						'code'  => get_the_ID(),
						'label' => get_the_title(),
					];
				}
			}
		}

		$doc_query->reset_postdata();

		return rest_ensure_response( [
			'success' => true,
			'results' => $items,
		] );
	}
}
