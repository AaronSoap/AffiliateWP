<?php
/**
 * Tests for Affiliate_WP_Creatives_DB class
 *
 * @covers Affiliate_WP_Creatives_DB
 * @group database
 * @group creatives
 */
class Creatives_DB_Tests extends AffiliateWP_UnitTestCase {

	/**
	 * Test creatives.
	 *
	 * @access public
	 * @var array
	 */
	public $creatives = array();

	/**
	 * Tear down.
	 */
	public function tearDown() {
		// Reset fixtures.
		foreach ( $this->creatives as $creative ) {
			affwp_delete_creative( $creative );
		}

		parent::tearDown();
	}

	/**
	 * @covers Affiliate_WP_Creatives_DB::get_creatives()
	 */
	public function test_get_creatives_should_return_array_of_Creative_objects_if_not_count_query() {
		$this->_set_up_creatives( 4 );

		$results = affiliate_wp()->creatives->get_creatives();

		// Check a random creative.
		$this->assertInstanceOf( 'AffWP\Creative', $results[ rand( 0, 3 ) ] );
	}

	/**
	 * @covers Affiliate_WP_Creatives_DB::get_creatives()
	 */
	public function test_get_creatives_should_return_integer_if_count_query() {
		$this->_set_up_creatives( 4 );

		$results = affiliate_wp()->creatives->get_creatives( array(), $count = true );

		$this->assertTrue( is_numeric( $results ) );
	}

	/**
	 * @covers Affiliate_WP_Creatives_DB::get_creatives()
	 */
	public function test_get_creatives_fields_ids_should_return_an_array_of_ids_only() {
		$creatives = $this->affwp->creative->create_many( 3 );

		$results = affiliate_wp()->creatives->get_creatives( array(
			'fields' => 'ids'
		) );

		$this->assertEqualSets( $creatives, $results );
	}

	/**
	 * @covers Affiliate_WP_Creatives_DB::get_creatives()
	 */
	public function test_get_creatives_invalid_fields_arg_should_return_regular_Creative_object_results() {
		$creatives = $this->affwp->creative->create_many( 3 );
		$creatives = array_map( 'affwp_get_creative', $creatives );

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
