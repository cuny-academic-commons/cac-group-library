<?php

namespace CAC\GroupLibrary\Sync;

interface SyncInterface {
	public static function set_up_sync_hooks();
	public static function get_library_item_from_source_item_id( $source_item_id, $group_id );
	public static function sync_to_library( $source_item_id );

	/*
	public function populate_from_source_item_id( $source_item_id );

	public function get_date_modified();
	public function get_description();
	public function get_file_type();
	public function get_group_id();
	public function get_item_type();
	public function get_source_item_id();
	public function get_title();
	public function get_url();
	public function get_user_id();
	*/
}
