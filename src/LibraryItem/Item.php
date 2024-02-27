<?php

namespace CAC\GroupLibrary\LibraryItem;

use CAC\GroupLibrary\Folder;

class Item {
	protected $table_name;

	protected $data = array(
		'date_modified'  => '0000-00-00 00:00:00',
		'description'    => '',
		'file_type'      => '',
		'folders'        => [],
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

			$saved = $id > 0;

			$this->set_id( $id );
		} else {
			$saved = $wpdb->update(
				$this->table_name,
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

		// Set folders no matter what, in case of deletion.
		if ( $this->get_id() ) {
			$folder_names = $this->get_folders();
			$folder_ids   = [];
			foreach ( $folder_names as $folder ) {
				$term = Folder::get_group_folder_by_name( $this->get_group_id(), $folder );

				$folder_ids[] = $term->get_id();
			}
			$set = wp_set_object_terms( $this->get_id(), $folder_ids, 'cacgl_folder' );

			if ( $set ) {
				$saved = true;
			}
		}

		wp_cache_delete( 'last_changed', 'cac_group_library' );
		wp_cache_delete( $this->get_id(), 'cac_group_library_items' );

		return (bool) $saved;
	}

	public function delete() {
		global $wpdb;

		$deleted = $wpdb->delete(
			$this->table_name,
			[
				'id' => $this->get_id(),
			],
			[
				'%d',
			]
		);

		wp_cache_delete( 'last_changed', 'cac_group_library' );
		wp_cache_delete( $this->get_id(), 'cac_group_library_items' );

		return $deleted > 0;
	}

	public function populate( $id ) {
		global $wpdb;

		$cached = wp_cache_get( $id, 'cac_group_library_items' );
		if ( false === $cached ) {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			wp_cache_set( $id, $row, 'cac_group_library_items' );
		} else {
			$row = $cached;
		}

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

		$folders = wp_get_object_terms( $this->get_id(), 'cacgl_folder' );
		$this->set_folders( wp_list_pluck( $folders, 'name' ) );
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
	 * Get topic title.
	 *
	 * Only relevant for attachments. Otherwise returns empty.
	 */
	public function get_topic_title() {
		if ( 'forum_attachment' !== $this->get_item_type() ) {
			return '';
		}

		$attachment = get_post( $this->get_source_item_id() );
		$parent     = get_post( $attachment->post_parent );

		if ( bbp_get_reply_post_type() === $parent->post_type ) {
			$topic_id = bbp_get_reply_topic_id( $parent->ID );
		} else {
			$topic_id = $parent->ID;
		}

		$topic = get_post( $topic_id );

		return $topic->post_title;
	}

	/**
	 * Get topic URL.
	 *
	 * Only relevant for attachments. Otherwise returns empty.
	 */
	public function get_topic_url() {
		if ( 'forum_attachment' !== $this->get_item_type() ) {
			return '';
		}

		$attachment = get_post( $this->get_source_item_id() );
		$parent     = get_post( $attachment->post_parent );

		return 'reply' === $parent->post_type ? bbp_get_reply_permalink( $parent->ID ) : bbp_get_topic_permalink( $parent->ID );
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
	 * Get user data.
	 *
	 * @return array
	 */
	public function get_user() {
		$user_id = $this->get_user_id();

		$retval = [
			'id'   => $user_id,
			'name' => bp_core_get_user_displayname( $user_id ),
			'url'  => bp_members_get_user_url( $user_id ),
		];

		return $retval;
	}

	/**
	 * Get folders.
	 *
	 * @return array
	 */
	public function get_folders() {
		if ( ! is_array( $this->data['folders'] ) ) {
			$folders = [];
		} else {
			$folders = $this->data['folders'];
		}

		return $folders;
	}

	/**
	 * Determine whether a user can edit the item.
	 */
	public function get_can_edit( $user_id ) {
		if ( ! is_int( $user_id ) || 0 === $user_id ) {
			return false;
		}

		$user_id = intval( $user_id );

		if ( user_can( $user_id, 'bp_moderate' ) ) {
			return true;
		}

		if ( $this->get_user_id() === $user_id ) {
			return true;
		}

		$can_edit = false;
		switch ( $this->get_item_type() ) {
			case 'bp_doc' :
				$can_edit = current_user_can( 'bp_docs_edit', $this->get_source_item_id() );
			break;

			default :
				$can_edit = groups_is_user_mod( $user_id, $this->get_group_id() ) || groups_is_user_admin( $user_id, $this->get_group_id() );
			break;
		}

		return $can_edit;
	}

	/**
	 * Get Edit URL.
	 *
	 * @return string
	 */
	public function get_edit_url() {
		switch ( $this->get_item_type() ) {
			case 'cacsp_paper' :
				// Editing happens at the regular URL.
				return get_permalink( $this->get_source_item_id() );

			case 'forum_attachment' :
				$attachment = get_post( $this->get_source_item_id() );
				$parent     = get_post( $attachment->post_parent );

				$url = '';
				if ( $parent ) {
					if ( 'reply' === $parent->post_type ) {
						$url = bbp_get_reply_edit_url( $parent->ID );
					} else {
						$url = bbp_get_topic_edit_url( $parent->ID );
					}
				}

				return $url;

			default :
				return '';
		}
	}

	/**
	 * Get content.
	 */
	public function get_content() {
		switch ( $this->get_item_type() ) {
			case 'bp_doc' :
				$post = get_post( $this->get_source_item_id() );
				return ( $post instanceof \WP_Post ) ? $post->post_content : '';

			default :
				return '';
		}
	}

	/**
	 * Get parent doc.
	 */
	public function get_parent_doc() {
		switch ( $this->get_item_type() ) {
			case 'bp_doc' :
				$post = get_post( $this->get_source_item_id() );

				if ( $post instanceof \WP_Post ) {
					$parent = get_post( $post->post_parent );
					if ( $parent instanceof \WP_Post ) {
						return [
							'code'  => $post->post_parent,
							'label' => $parent->post_title,
						];
					}
				}

				return '';
			default :
				return '';
		}
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

	/**
	 * Set folders.
	 *
	 * @param array Array of slugs. Group folder slugs are of the form {$group_id}-{$slug}.
	 */
	public function set_folders( $folders ) {
		$this->data['folders'] = $folders;
	}
}
