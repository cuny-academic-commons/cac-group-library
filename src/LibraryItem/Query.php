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

		$results = $wpdb->get_col( $query );
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
			$retval[ $item->get_id() ] = [
				'id'             => $item->get_id(),
				'date_modified'  => $item->get_date_modified(),
				'description'    => $item->get_description(),
				'file_type'      => $item->get_file_type(),
				'folders'        => $item->get_folder_objects(),
				'group_id'       => $item->get_group_id(),
				'item_type'      => $item->get_item_type(),
				'source_item_id' => $item->get_source_item_id(),
				'title'          => $item->get_title(),
				'url'            => $item->get_url(),
				'user'           => $item->get_user(),
			];
		}

		return $retval;
	}
}
