<?php

namespace CAC\GroupLibrary;

class Folder {
	public static function get_taxonomy_name() {
		return 'cacgl_folder';
	}

	public static function get_group_folder_by_name( $group_id, $name ) {
		$slug = $group_id . '-' . sanitize_title_with_dashes( $name );

		$term = get_term_by( 'slug', $slug, self::get_taxonomy_name() );
		if ( $term ) {
			return $term;
		}

		// A new term is needed, so we create it.
		$created = wp_insert_term(
			$name,
			self::get_taxonomy_name(),
			[
				'slug' => $slug,
			]
		);

		return get_term( $created['term_id'], self::get_taxonomy_name() );
	}
}
