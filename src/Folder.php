<?php

namespace CAC\GroupLibrary;

use \WP_Term_Query;

class Folder {
	public static function get_taxonomy_name() {
		return 'cacgl_folder';
	}

	public static function get_group_folder_by_name( $group_id, $name ) {
		$tq = new WP_Term_Query(
			[
				'hide_empty' => false,
				'taxonomy'   => self::get_taxonomy_name(),
				'meta_query' => [
					[
						'key'   => 'group_id',
						'value' => $group_id,
					]
				],
				'name'       => $name,
			]
		);

		if ( $tq->terms ) {
			$term = reset( $tq->terms );
		} else {
			$slug = $group_id . '-' . sanitize_title_with_dashes( $name );

			// A new term is needed, so we create it.
			$created = wp_insert_term(
				$name,
				self::get_taxonomy_name(),
				[
					'slug' => $slug,
				]
			);

			update_term_meta( $created['term_id'], 'group_id', $group_id );

			$term = get_term( $created['term_id'], self::get_taxonomy_name() );
		}

		return $term;
	}


	public static function get_folders_of_group( $group_id ) {
		$tq = new WP_Term_Query(
			[
				'hide_empty' => false,
				'taxonomy'   => self::get_taxonomy_name(),
				'meta_query' => [
					[
						'key'   => 'group_id',
						'value' => $group_id,
					]
				],
			]
		);

		return wp_list_pluck( $tq->terms, 'name' );
	}
}
