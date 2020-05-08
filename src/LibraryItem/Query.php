<?php

namespace CAC\GroupLibrary\LibraryItem;

class Query {
	/**
	 * @todo We must exclude non-public Docs and Papers from public listings.
	 *       For simplicity (to avoid mirroring this kind of data in the table) we can
	 *       probably rely on a "source_item_id__not_in" param or something like that.
	 */
	public static function get( $_args = [] ) {
		global $wpdb;

		$args = array_merge(
			[
				'group_id'       => null,
				'item_type'      => null,
				'source_item_id' => null,
				'orderby'        => 'name',
			],
			$_args
		);

		$table_name = cac_group_library()->schema->get_table_name();

		$sql = array(
			'select' => "SELECT id FROM {$table_name}",
			'where' => array(),
			'limits' => '',
		);

		if ( null !== $args['item_type'] ) {
			$sql['where']['item_type'] = $wpdb->prepare( 'item_type = %s', $args['item_type'] );
		}

		if ( null !== $args['source_item_id'] ) {
			$sql['where']['source_item_id'] = $wpdb->prepare( 'source_item_id = %d', $args['source_item_id'] );
		}

		if ( null !== $args['group_id'] ) {
			$sql['where']['group_id'] = $wpdb->prepare( 'group_id = %d', $args['group_id'] );
		}

		$where = '';
		if ( $sql['where'] ) {
			$sql['where'] = ' WHERE ' . implode( ' AND ', $sql['where'] );
		}

		switch ( $args['orderby'] ) {
			case 'title' :
			default :
				$sql['order'] = ' ORDER BY title ASC ';
			break;
		}

		$query = $sql['select'] . $sql['where'] . $sql['order'] . $sql['limits'];

		$last_changed = wp_cache_get_last_changed( 'cac_group_library' );
		$cache_key    = md5( $query ) . $last_changed;
		$cached       = wp_cache_get( $cache_key, 'cac_group_library' );
		if ( false === $cached ) {
			$results = $wpdb->get_col( $query );
			wp_cache_set( $cache_key, $results, 'cac_group_library' );
		} else {
			$results = $cached;
		}

		$retval = array();

		foreach ( $results as $found_id ) {
			$i = new Item( (int) $found_id );
			$retval[ $found_id ] = $i;
		}

		return $retval;
	}

	public static function get_for_endpoint( $args ) {
		$items = self::get( $args );

		$retval = [];
		foreach ( $items as $item ) {
			// Important! The group ID is not sent but is assumed from the request.
			$formatted_item = [
				'id'             => $item->get_id(),
				'date_modified'  => $item->get_date_modified(),
				'item_type'      => $item->get_item_type(),
				'source_item_id' => $item->get_source_item_id(),
				'title'          => $item->get_title(),
				'url'            => $item->get_url(),
				'user'           => $item->get_user(),
			];

			// Reduce payload size by only adding properties when necessary.
			$topic_title = $item->get_topic_title();
			if ( $topic_title ) {
				$formatted_item['topic_title'] = $topic_title;
			}

			$topic_url = $item->get_topic_url();
			if ( $topic_url ) {
				$formatted_item['topic_url'] = $topic_url;
			}

			$description = $item->get_description();
			if ( $description ) {
				$formatted_item['description'] = $description;
			}

			$file_type = $item->get_file_type();
			if ( $file_type ) {
				$formatted_item['file_type'] = $file_type;
			}

			$folders = $item->get_folders();
			if ( $folders ) {
				$formatted_item['folders'] = $folders;
			}

			$can_edit = $item->get_can_edit( get_current_user_id() );
			if ( $can_edit ) {
				$formatted_item['can_edit'] = true;

				$edit_url = $item->get_edit_url();
				if ( $edit_url ) {
					$formatted_item['edit_url'] = $edit_url;
				}
			}

			$retval[ $item->get_id() ] = $formatted_item;
		}

		return $retval;
	}
}
