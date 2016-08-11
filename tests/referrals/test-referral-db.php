<?php
/**
 * Tests for Affiliate_WP_DB_Affiliates class
 *
 * @covers Affiliate_WP_Referrals_DB
 * @group database
 * @group referrals
 */
class Referrals_DB_Tests extends AffiliateWP_UnitTestCase {

	/**
	 * Test affiliates.
	 *
	 * @access public
	 * @var array
	 */
	public $_affiliate_id;

	/**
	 * Test referrals.
	 *
	 * @access public
	 * @var array
	 */
	public $referrals = array();

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		$this->_affiliate_id = affiliate_wp()->affiliates->add( array(
			'user_id' => $this->factory->user->create()
		) );
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		// Reset fixtures.
		$this->referrals = array();

		parent::tearDown();
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_should_return_array_of_Referral_objects_if_not_count_query() {
		$this->_set_up_referrals( 4 );

		$results = affiliate_wp()->referrals->get_referrals();

		// Check a random referral.
		$this->assertInstanceOf( 'AffWP\Referral', $results[ rand( 0, 3 ) ] );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_should_return_integer_if_count_query() {
		$this->_set_up_referrals( 4 );

		$results = affiliate_wp()->referrals->get_referrals( array(), $count = true );

		$this->assertTrue( is_numeric( $results ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_fields_ids_should_return_an_array_of_ids_only() {
		$referrals = $this->affwp->referral->create_many( 3 );

		$results = affiliate_wp()->referrals->get_referrals( array(
			'fields' => 'ids'
		) );

		$this->assertEqualSets( $referrals, $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_invalid_fields_arg_should_return_regular_Referral_object_results() {
		$referrals = $this->affwp->referral->create_many( 3 );
		$referrals = array_map( 'affwp_get_referral', $referrals );

		$results = affiliate_wp()->referrals->get_referrals( array(
			'fields' => 'foo'
		) );

		$this->assertEqualSets( $referrals, $results );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_0_if_status_is_invalid() {
		$this->_set_up_referrals( 4 );

		$this->assertSame( 0, affiliate_wp()->referrals->count_by_status( 'foo', $this->_affiliate_id ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_0_if_affiliate_is_invalid() {
		$this->assertSame( 0, affiliate_wp()->referrals->count_by_status( 'unpaid', 0 ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_count_of_referrals_of_given_status() {
		// Add 4 referrals, default 'pending' status.
		$this->_set_up_referrals( 4 );

		$this->assertSame( 4, affiliate_wp()->referrals->count_by_status( 'pending', $this->_affiliate_id ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_count_of_referrals_created_within_a_month_if_date_is_month() {
		// Set up 3 pending referrals for this month.
		$this->_set_up_referrals( 3 );

		// Set up 3 pending referrals for six months ago.
		$this->_set_up_referrals( 3, array(
			'date' => date( 'Y-m-d H:i:s', time() - ( 6 * ( 2592000 ) ) )
		) );

		$this->assertSame( 3, affiliate_wp()->referrals->count_by_status( 'pending', $this->_affiliate_id, 'month' ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::count_by_status()
	 */
	public function test_count_by_status_should_return_count_of_referrals_for_all_time_if_date_is_invalid() {
		// Set up 3 pending referrals for this month.
		$this->_set_up_referrals( 3 );

		// Set up 3 pending referrals for six months ago.
		$this->_set_up_referrals( 3, array(
			'date' => date( 'Y-m-d H:i:s', time() - ( 6 * ( 2592000 ) ) )
		) );

		$this->assertSame( 6, affiliate_wp()->referrals->count_by_status( 'pending', $this->_affiliate_id, 'foo' ) );
	}

	/**
	 * Helper to set up a user, an affiliate, and referrals.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param int   $count         Optional. Number of referrals to create. Default 3.
	 * @param array $referral_args Optional. Arguments for adding referrals. Default empty array.
	 */
	public function _set_up_referrals( $count = 3, $referral_args = array() ) {
		$args = array_merge( $referral_args, array(
			'affiliate_id' => $this->_affiliate_id
		) );

		for ( $i = 1; $i <= $count; $i++ ) {
			$this->referrals[] = affiliate_wp()->referrals->add( $args );
		}
	}
}
