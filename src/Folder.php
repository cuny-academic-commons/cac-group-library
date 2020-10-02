<?php

namespace CAC\GroupLibrary;

use \WP_Term_Query;

class Folder {
	protected $data = [
		'group_id' => 0,
		'id'       => 0,
		'name'     => '',
	];

	protected function __construct() {}

	public function get_id() {
		return (int) $this->data['id'];
	}

	public function get_group_id() {
		return (int) $this->data['group_id'];
	}

	public function get_name() {
		return $this->data['name'];
	}

	public function set_id( $value ) {
		$this->data['id'] = (int) $value;
	}

	public function set_group_id( $value ) {
		$this->data['group_id'] = (int) $value;
	}

	public function set_name( $value ) {
		$this->data['name'] = $value;
	}

	public function save() {
		$updated = wp_update_term(
			$this->get_id(),
			self::get_taxonomy_name(),
			[
				'name' => $this->get_name(),
			]
		);
	}

	public function delete() {
		wp_delete_term( $this->get_id(), self::get_taxonomy_name() );
	}

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
			// A new term is needed, so we create it.
			$term_id = self::create_folder_term( $name, $group_id );

			$term = get_term( $term_id, self::get_taxonomy_name() );
		}

		$folder = new self();
		$folder->set_group_id( $group_id );
		$folder->set_name( $term->name );
		$folder->set_id( $term->term_id );

		return $folder;
	}

	public static function create_folder_term( $name, $group_id ) {
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

		return $created['term_id'];
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
