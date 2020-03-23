<?php

namespace CAC\GroupLibrary\Tests;

use \CAC\GroupLibrary\LibraryItem\Item;
use \WP_UnitTestCase;

class Tests_Invitation extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass( $factory ) {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}cac_library_items" );
	}

	public function test_save_and_retrieve() {
		$group_id       = 123;
		$source_item_id = 789;
		$item_type      = 'file';
		$file_type      = 'pdf';
		$user_id        = 456;
		$title          = 'Test Item';
		$date           = '2017-12-06 12:00:00';
		$description    = 'Fliff';
		$url            = 'http://example.com/test-file';

		$i = new Item();
		$i->set_group_id( $group_id );
		$i->set_source_item_id( $source_item_id );
		$i->set_item_type( $item_type );
		$i->set_file_type( $file_type );
		$i->set_user_id( $user_id );
		$i->set_title( $title );
		$i->set_date_modified( $date );
		$i->set_description( $description );
		$i->set_url( $url );

		$saved = $i->save();
		$this->assertTrue( $saved );

		$id = $i->get_id();
		$this->assertTrue( $id > 0 );

		$j = new Item( $id );

		$this->assertSame( $group_id, $j->get_group_id() );
		$this->assertSame( $source_item_id, $j->get_source_item_id() );
		$this->assertSame( $item_type, $j->get_item_type() );
		$this->assertSame( $file_type, $j->get_file_type() );
		$this->assertSame( $user_id, $j->get_user_id() );
		$this->assertSame( $title, $j->get_title() );
		$this->assertSame( $date, $j->get_date_modified() );
		$this->assertSame( $description, $j->get_description() );
		$this->assertSame( $url, $j->get_url() );
	}
}
