<?php
namespace AffWP\Visit\Database;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for Affiliate_WP_Visits_DB class
 *
 * @covers Affiliate_WP_Visits_DB
 * @group database
 * @group visits
 */
class Tests extends UnitTestCase {

	/**
	 * Test affiliates.
	 *
	 * @access public
	 * @var array
	 */
	public static $affiliates = array();

	/**
	 * Test visits.
	 *
	 * @access public
	 * @var array
	 */
	public static $visits = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$affiliates = parent::affwp()->affiliate->create_many( 4 );
		self::$visits = parent::affwp()->visit->create_many( 4 );
	}

	/**
	 * Destroy fixtures.
	 */
	public static function wpTearDownAfterClass() {
		foreach ( self::$affiliates as $affiliate ) {
			affwp_delete_affiliate( $affiliate );
		}

		foreach ( self::$visits as $visit ) {
			affwp_delete_visit( $visit );
		}
	}

	/**
	 * @covers Affiliate_WP_Visits_DB::get_visits()
	 */
	public function test_get_visits_should_return_array_of_Visit_objects_if_not_count_query() {
		$results = affiliate_wp()->visits->get_visits();

		// Check a random visit.
		$this->assertInstanceOf( 'AffWP\Visit', $results[0] );
	}

	/**
	 * @covers Affiliate_WP_Visits_DB::get_visits()
	 */
	public function test_get_visits_should_turn_integer_if_count_query() {
		$results = affiliate_wp()->visits->get_visits( array(), $count = true );

		$this->assertTrue( is_numeric( $results ) );
	}

	/**
	 * @covers Affiliate_WP_Visits_DB::get_visits()
	 */
	public function test_get_visits_fields_ids_should_return_an_array_of_ids_only() {
		$results = affiliate_wp()->visits->get_visits( array(
			'fields' => 'ids'
		) );

		$this->assertEqualSets( self::$visits, $results );
	}

	/**
	 * @covers Affiliate_WP_Visits_DB::get_visits()
	 */
	public function test_get_visits_invalid_fields_arg_should_return_regular_Visit_object_results() {
		$visits = array_map( 'affwp_get_visit', self::$visits );

		$results = affiliate_wp()->visits->get_visits( array(
			'fields' => 'foo'
		) );

		$this->assertEqualSets( $visits, $results );

	}

}
