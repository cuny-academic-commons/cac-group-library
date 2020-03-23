<?php

namespace CAC\GroupLibrary\LibraryItem;

class Query {
	public function get( $_args = [] ) {
		global $wpdb;

		$args = array_merge(
			[
				'item_type'      => null,
				'source_item_id' => null,
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

		$where = '';
		if ( $sql['where'] ) {
			$sql['where'] = ' WHERE ' . implode( ' AND ', $sql['where'] );
		}

		$query = $sql['select'] . $sql['where'] . $sql['limits'];

		$results = $wpdb->get_col( $query );
		$retval = array();

		foreach ( $results as $found_id ) {
			$i = new Item( (int) $found_id );
			$retval[ $found_id ] = $i;
		}

		return $retval;
	}
}
