<?php
namespace AffWP\Creative\Database;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for Affiliate_WP_Creatives_DB class
 *
 * @covers Affiliate_WP_Creatives_DB
 * @group database
 * @group creatives
 */
class Tests extends UnitTestCase {

	/**
	 * Creatives fixture.
	 *
	 * @access public
	 * @var array
	 * @static
	 */
	public static $creatives = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$creatives = parent::affwp()->creative->create_many( 4 );
	}

	/**
	 * Destroy fixtures.
	 */
	public static function wpTearDownAfterClass() {
		$creatives = affiliate_wp()->creatives->get_creatives( array(
			'number' => -1,
			'fields' => 'ids',
		) );

		foreach ( $creatives as $creative ) {
			affwp_delete_creative( $creative );
		}
	}

	/**
	 * @covers Affiliate_WP_Creatives_DB::get_creatives()
	 */
	public function test_get_creatives_should_return_array_of_Creative_objects_if_not_count_query() {
		$results = affiliate_wp()->creatives->get_creatives();

		// Check a random creative.
		$this->assertInstanceOf( 'AffWP\Creative', $results[0] );
	}

	/**
	 * @covers Affiliate_WP_Creatives_DB::get_creatives()
	 */
	public function test_get_creatives_should_return_integer_if_count_query() {
		$results = affiliate_wp()->creatives->get_creatives( array(), $count = true );

		$this->assertTrue( is_numeric( $results ) );
	}

	/**
	 * @covers Affiliate_WP_Creatives_DB::get_creatives()
	 */
	public function test_get_creatives_fields_ids_should_return_an_array_of_ids_only() {
		$results = affiliate_wp()->creatives->get_creatives( array(
			'fields' => 'ids'
		) );

		$this->assertEqualSets( self::$creatives, $results );
	}

	/**
	 * @covers Affiliate_WP_Creatives_DB::get_creatives()
	 */
	public function test_get_creatives_invalid_fields_arg_should_return_regular_Creative_object_results() {
		$creatives = array_map( 'affwp_get_creative', self::$creatives );

		$results = affiliate_wp()->creatives->get_creatives( array(
			'fields' => 'foo'
		) );

		$this->assertEqualSets( $creatives, $results );
	}

	/**
	 * Helper to set up creatives.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param int   $count          Optional. Number of creatives to create. Default 3.
	 * @param array $creatives_args Optional. Arguments for adding creatives. Default empty array.
	 */
	public function _set_up_creatives( $count = 3, $creatives_args = array() ) {
		for ( $i = 1; $i <= $count; $i++ ) {
			$this->creatives[] = affiliate_wp()->creatives->add();
		}
	}

}
