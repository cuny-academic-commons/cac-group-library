<?php

namespace CAC\GroupLibrary\LibraryItem;

class Item {
	protected $table_name;

	protected $data = array(
		'date_modified'  => '0000-00-00 00:00:00',
		'description'    => '',
		'file_type'      => '',
		'group_id'       => 0,
		'id'             => 0,
		'item_type'      => '',
		'source_item_id' => 0,
		'title'          => '',
		'url'            => '',
		'user_id'        => 0,
	);

	public function __construct( $item_id = null ) {
		$this->table_name = cac_group_library()->schema->get_table_name();

		if ( ! is_null( $item_id  )) {
			$this->populate( $item_id );
		}
	}

	public function exists() {
		return $this->get_id() > 0;
	}

	public function save() {
		global $wpdb;

		$is_new = ( $this->get_id() === 0 );

		if ( $is_new ) {
			$wpdb->insert(
				$this->table_name,
				array(
					'id'             => $this->get_id(),
					'group_id'       => $this->get_group_id(),
					'source_item_id' => $this->get_source_item_id(),
					'item_type'      => $this->get_item_type(),
					'file_type'      => $this->get_file_type(),
					'user_id'        => $this->get_user_id(),
					'title'          => $this->get_title(),
					'date_modified'  => $this->get_date_modified(),
					'description'    => $this->get_description(),
					'url'            => $this->get_url(),
				),
				array(
					'%d', // id
					'%d', // group_id
					'%d', // source_item_id
					'%s', // item_type
					'%s', // file_type
					'%d', // user_id
					'%s', // title
					'%s', // date_modified
					'%s', // description
					'%s', // url
				)
			);

			$id = $wpdb->insert_id;
			$this->set_id( $id );
		} else {
			$wpdb->update(
				"{$prefix}cac_invites",
				array(
					'group_id'       => $this->get_group_id(),
					'source_item_id' => $this->get_source_item_id(),
					'item_type'      => $this->get_item_type(),
					'file_type'      => $this->get_file_type(),
					'user_id'        => $this->get_user_id(),
					'title'          => $this->get_title(),
					'date_modified'  => $this->get_date_modified(),
					'description'    => $this->get_description(),
					'url'            => $this->get_url(),
				),
				array(
					'id' => $this->get_id(),
				),
				array(
					'%d', // group_id
					'%d', // source_item_id
					'%s', // item_type
					'%s', // file_type
					'%d', // user_id
					'%s', // title
					'%s', // date_modified
					'%s', // description
					'%s', // url
				),
				array(
					'%d',
				)
			);
		}

		return true;
	}

	public function populate( $id ) {
		global $wpdb;

		$prefix = $wpdb->get_blog_prefix( get_main_site_id() );

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $row ) {
			return;
		}

		$this->set_id( $row->id );
		$this->set_group_id( $row->group_id );
		$this->set_source_item_id( $row->source_item_id );
		$this->set_item_type( $row->item_type );
		$this->set_file_type( $row->file_type );
		$this->set_user_id( $row->user_id );
		$this->set_title( $row->title );
		$this->set_date_modified( $row->date_modified );
		$this->set_description( $row->description );
		$this->set_url( $row->url );
	}

	/**
	 * Get date modified.
	 *
	 * @return string
	 */
	public function get_date_modified() {
		return $this->data['date_modified'];
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->data['description'];
	}

	/**
	 * Get file type.
	 *
	 * @return string
	 */
	public function get_file_type() {
		return $this->data['file_type'];
	}

	/**
	 * Get group id.
	 *
	 * @return int
	 */
	public function get_group_id() {
		return (int) $this->data['group_id'];
	}

	/**
	 * Get ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->data['id'];
	}

	/**
	 * Get item type.
	 *
	 * @return string
	 */
	public function get_item_type() {
		return $this->data['item_type'];
	}

	/**
	 * Get source item ID.
	 *
	 * @return int
	 */
	public function get_source_item_id() {
		return (int) $this->data['source_item_id'];
	}

	/**
	 * Get item title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->data['title'];
	}

	/**
	 * Get URL.
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->data['url'];
	}

	/**
	 * Get user ID.
	 *
	 * @return int
	 */
	public function get_user_id() {
		return (int) $this->data['user_id'];
	}

	/**
	 * Set date modified.
	 *
	 * @param string
	 */
	public function set_date_modified( $date_modified ) {
		$this->data['date_modified'] = $date_modified;
	}

	/**
	 * Set description.
	 *
	 * @param string
	 */
	public function set_description( $description ) {
		$this->data['description'] = $description;
	}

	/**
	 * Set file type.
	 *
	 * @param string
	 */
	public function set_file_type( $file_type ) {
		$this->data['file_type'] = $file_type;
	}

	/**
	 * Set group ID.
	 *
	 * @param int
	 */
	public function set_group_id( $group_id ) {
		$this->data['group_id'] = intval( $group_id );
	}

	/**
	 * Set ID.
	 *
	 * @param string
	 */
	public function set_id( $id ) {
		$this->data['id'] = intval( $id );
	}

	/**
	 * Set item type.
	 *
	 * @param string
	 */
	public function set_item_type( $item_type ) {
		$this->data['item_type'] = $item_type;
	}

	/**
	 * Set date created.
	 *
	 * @param int
	 */
	public function set_source_item_id( $source_item_id ) {
		$this->data['source_item_id'] = intval( $source_item_id );
	}

	/**
	 * Set title.
	 *
	 * @param string
	 */
	public function set_title( $title ) {
		$this->data['title'] = $title;
	}

	/**
	 * Set URL.
	 *
	 * @param string
	 */
	public function set_url( $url ) {
		$this->data['url'] = $url;
	}

	/**
	 * Set user ID.
	 *
	 * @param int
	 */
	public function set_user_id( $user_id ) {
		$this->data['user_id'] = intval( $user_id );
	}

}
