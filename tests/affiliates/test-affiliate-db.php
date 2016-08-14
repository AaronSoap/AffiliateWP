<?php
namespace AffWP\Affiliate\Database;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for Affiliate_WP_DB_Affiliates class
 *
 * @covers Affiliate_WP_DB_Affiliates
 * @group database
 * @group affiliates
 */
class Tests extends UnitTestCase {

	/**
	 * Users fixture.
	 *
	 * @access protected
	 * @var array
	 * @static
	 */
	public static $users = array();

	/**
	 * Affiliates fixture.
	 *
	 * @access protected
	 * @var array
	 * @static
	 */
	public static $affiliates = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$users = parent::affwp()->user->create_many( 4 );

		foreach ( self::$users as $user_id ) {
			self::$affiliates[] = parent::affwp()->affiliate->create( array(
				'user_id' => $user_id
			) );
		}
	}

	/**
	 * Destroy fixtures.
	 */
	public static function wpTearDownAfterClass() {
		$affiliates = affiliate_wp()->affiliates->get_affiliates( array(
			'number' => -1,
			'fields' => 'ids',
		) );

		foreach ( $affiliates as $affiliate ) {
			affwp_delete_affiliate( $affiliate );
		}
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_should_return_array_of_Affiliate_objects_if_not_count_query() {
		$results = affiliate_wp()->affiliates->get_affiliates();

		// Check a random affiliate.
		$this->assertInstanceOf( 'AffWP\Affiliate', $results[0] );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_should_return_integer_if_count_query() {
		$results = affiliate_wp()->affiliates->get_affiliates( array(), $count = true );

		$this->assertTrue( is_numeric( $results ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_method_should_allow_searching_by_display_name() {
		$display_name = 'Foo';

		wp_update_user( array(
			'ID'           => self::$users[0],
			'display_name' => $display_name
		) );

		// Get affiliates based on the search term(s).
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'search' => 'foo',
		) );

		// Assert that results were found.
		$this->assertNotEmpty( $results );

		// Assert that the result we're looking for was found.
		$this->assertNotEmpty( wp_list_filter( $results, array( 'user_id' => self::$users[0] ) ) );

		// Clean up.
		wp_update_user( array(
			'ID'           => self::$users[0],
			'display_name' => '',
		) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_method_should_allow_searching_by_user_login() {
		$affiliate_id = $this->factory->affiliate->create( array(
			'user_id' => $user_id = $this->factory->user->create( array(
				'user_login' => 'foo_bar'
			) )
		) );

		// Get affiliates based on the search term(s).
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'search' => 'foo'
		) );

		// Assert that no results were found.
		$this->assertNotEmpty( $results );

		// Assert that the result we're looking for was found.
		$this->assertNotEmpty( wp_list_filter( $results, array( 'user_id' => $user_id ) ) );

		// Clean up.
		affwp_delete_affiliate( $affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_method_should_allow_searching_either_display_name_or_user_login() {
		$display_name = 'Bar Baz';
		$user_login   = 'foo_bar';

		$user = $this->factory->user->create( array(
			'display_name' => $display_name,
			'user_login'   => 'Scooby Doo'
		) );

		$user2 = $this->factory->user->create( array(
			'display_name' => 'Garfield',
			'user_login'   => $user_login
		) );

		$user3 = $this->factory->user->create( array(
			'display_name' => 'Nemo',
			'user_login'   => 'Dory'
		) );

		$affiliates = array();

		// Add affiliates.
		foreach ( array( $user, $user2, $user3 ) as $id ) {
			$affiliates[] = $this->factory->affiliate->create( array(
				'user_id' => $id
			) );
		}

		// Get affiliates based on the search term(s).
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'search' => 'bar'
		) );

		// Assert two results were found.
		$this->assertCount( 2, $results );

		// Assert that the first user was found.
		$this->assertNotEmpty( wp_list_filter( $results, array( 'user_id' => $user ) ) );

		// Assert that the second user was found.
		$this->assertNotEmpty( wp_list_filter( $results, array( 'user_id' => $user2 ) ) );

		// Assert that the third user wasn't found.
		$this->assertEmpty( wp_list_filter( $results, array( 'user_id' => $user3 ) ) );

		// Clean up.
		foreach ( $affiliates as $affiliate ) {
			affwp_delete_affiliate( $affiliate );
		}
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_with_integer_user_id_should_return_affiliate_for_that_user() {
		// Query affiliates.
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'user_id' => self::$users[0],
			'fields'  => 'ids',
		) );

		$this->assertSame( self::$affiliates[0], $results[0] );
		$this->assertSame( self::$users[0], affwp_get_affiliate_user_id( $results[0] ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_with_array_of_user_ids_should_return_matching_affiliates() {
		// Query affiliates.
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'user_id' => self::$users,
			'fields'  => 'ids',
		) );

		$this->assertEqualSets( self::$affiliates, $results );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_with_integer_affiliate_id_should_return_that_affiliate() {
		// Query affiliates.
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'affiliate_id' => self::$affiliates[0]
		) );

		$this->assertEqualSets( array( self::$affiliates[0] ), wp_list_pluck( $results, 'affiliate_id' ) );
		$this->assertEqualSets( array( self::$users[0] ), wp_list_pluck( $results, 'user_id' ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_with_array_of_affiliate_ids_should_return_matching_affiliates() {
		// Query affiliates.
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'affiliate_id' => self::$affiliates,
			'fields'       => 'ids',
		) );

		$this->assertEqualSets( self::$affiliates, $results );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_default_orderby_should_order_by_affiliate_id() {
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'order'  => 'ASC',
			'fields' => 'ids',
		) );

		// Order should be as created, 0, 1, 2, 3.
		$this->assertEqualSets( self::$affiliates, $results );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_status_should_order_by_status() {
		affwp_update_affiliate( array(
			'affiliate_id' => self::$affiliates[0],
			'status'       => 'active',
		) );

		affwp_update_affiliate( array(
			'affiliate_id' => self::$affiliates[1],
			'status'       => 'rejected',
		) );

		affwp_update_affiliate( array(
			'affiliate_id' => self::$affiliates[2],
			'status'       => 'inactive',
		) );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'status',
			'order'   => 'ASC', // A-Z
			'fields'  => 'ids',
		) );

		$new_order = array(
			self::$affiliates[0],
			self::$affiliates[3],
			self::$affiliates[2],
			self::$affiliates[1],
		);

		// Order should be alphabetical: 0 (active), 3 (active), 2 (inactive), 1 (rejected).
		$this->assertEqualSets( $new_order, $results );

		// Clean up.
		foreach ( self::$affiliates as $affiliate ) {
			affwp_update_affiliate( array(
				'affiliate_id' => $affiliate,
				'status'       => 'active',
			) );
		}
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_date_should_order_by_registered_date() {
		affwp_update_affiliate( array(
			'affiliate_id' => self::$affiliates[0],
			'date_registered' => ( time() - WEEK_IN_SECONDS ),
		) );

		affwp_update_affiliate( array(
			'affiliate_id' => self::$affiliates[1],
			'date_registered' => ( time() + WEEK_IN_SECONDS ),
		) );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'date', // Default 'order' is DESC
			'fields'  => 'ids',
		) );

		$new_order = array(
			self::$affiliates[3],
			self::$affiliates[2],
			self::$affiliates[0],
			self::$affiliates[1]
		);

		// Order should be newest to oldest: 3, 2, 0, 1.
		$this->assertEqualSets( $new_order, $results );

		// Cleanup.
		foreach ( self::$affiliates as $affiliate ) {
			affwp_update_affiliate( array(
				'date_registered' => current_time( 'mysql' )
			) );
		}
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_name_should_order_by_user_display_name() {
		wp_update_user( array(
			'ID'           => self::$users[0],
			'display_name' => 'Bravo',
		) );

		wp_update_user( array(
			'ID'           => self::$users[1],
			'display_name' => 'Alpha',
		) );

		wp_update_user( array(
			'ID'           => self::$users[2],
			'display_name' => 'Charlie',
		) );

		wp_update_user( array(
			'ID'           => self::$users[3],
			'display_name' => 'Delta',
		) );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'name', // Default 'order' is 'DESC'
			'fields'  => 'ids',
		) );

		$new_order = array(
			self::$affiliates[3],
			self::$affiliates[2],
			self::$affiliates[0],
			self::$affiliates[1]
		);

		// Order should be reverse alphabetical: 3 (Delta), 2 (Charlie), 0 (Beta), 1 (Alpha).
		$this->assertEqualSets( $new_order, self::$affiliates );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_username_should_order_by_user_login() {
		wp_update_user( array(
			'ID'         => self::$users[0],
			'user_login' => 'delta',
		) );

		wp_update_user( array(
			'ID'         => self::$users[1],
			'user_login' => 'foxtrot',
		) );

		wp_update_user( array(
			'ID'         => self::$users[2],
			'user_login' => 'echo',
		) );

		wp_update_user( array(
			'ID'         => self::$users[3],
			'user_login' => 'golf',
		) );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'username',
			'order'   => 'ASC',
			'fields'  => 'ids',
		) );

		$new_order = array(
			self::$affiliates[0],
			self::$affiliates[2],
			self::$affiliates[1],
			self::$affiliates[3],
		);

		// Order should be 0 (delta), 2 (echo), 1 (foxtrot), 3 (golf).
		$this->assertEqualSets( $new_order, $results );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_valid_referral_status_should_order_by_that_referral_status_count() {
		// Add 1 'unpaid' referral for affiliate 0.
		$referrals1 = $this->factory->referral->create( array(
			'affiliate_id' => self::$affiliates[0],
			'status'       => 'unpaid'
		) );

		// Add 3 'unpaid' referrals for affiliate 1.
		$referrals2 = $this->factory->referral->create_many( 3, array(
			'affiliate_id' => self::$affiliates[1],
			'status'       => 'unpaid'
		) );

		// Add 2 'paid' referrals for affiliate 2.
		$referrals3 = $this->factory->referral->create_many( 2, array(
			'affiliate_id' => self::$affiliates[2],
			'status'       => 'paid'
		) );

		// Add 2 'unpaid' referral for affiliate 3.
		$referrals4 = $this->factory->referral->create_many( 2, array(
			'affiliate_id' => self::$affiliates[3],
			'status'       => 'unpaid'
		) );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'unpaid',
			'order'   => 'ASC', // Small to large.,
			'fields'  => 'ids',
		) );

		$new_order = array(
			self::$affiliates[2],
			self::$affiliates[0],
			self::$affiliates[3],
			self::$affiliates[1]
		);

		// Order should be 2 (zero paid), 0 (1 unpaid), 3 (2 unpaid), 1 (3 unpaid).
		$this->assertEqualSets( $new_order, $results );

		// Cleanup.
		$referrals = array_merge( (array) $referrals1, $referrals2, $referrals3, $referrals4 );

		$this->factory->referral->cleanup( $referrals );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_invalid_referral_status_should_default_to_order_by_primary_key() {
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => rand_str( 15 ),
			'fields'  => 'ids',
		) );

		// With invalid orderby, should return ordered by affiliate_id, descending.
		$this->assertEqualSets( self::$affiliates, $results );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_earnings_should_order_by_earnings() {
		affwp_update_affiliate( array(
			'affiliate_id' => self::$affiliates[0],
			'earnings'     => '20',
		) );

		affwp_update_affiliate( array(
			'affiliate_id' => self::$affiliates[0],
			'earnings'     => '10',
		) );

		affwp_update_affiliate( array(
			'affiliate_id' => self::$affiliates[2],
			'earnings'     => '30',
		) );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'earnings',
			'order'   => 'ASC',
			'fields'  => 'ids',
		) );

		$new_order = array(
			self::$affiliates[3],
			self::$affiliates[1],
			self::$affiliates[0],
			self::$affiliates[2]
		);

		// Order should least to greatest: 3 (0), 1 (10), 0 (20), 2 (30).
		$this->assertEqualSets( $new_order, $results );

		// Cleanup.
		foreach ( self::$affiliates as $affiliate ) {
			affwp_update_affiliate( array(
				'affiliate_id' => $affiliate,
				'earnings'     => 0
			) );
		}
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_fields_ids_should_return_an_array_of_ids_only() {
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'fields' => 'ids'
		) );

		$this->assertEqualSets( self::$affiliates, $results );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_invalid_fields_arg_should_return_regular_Affiliate_object_results() {
		$affiliates = array_map( 'affwp_get_affiliate', self::$affiliates );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'fields' => 'foo'
		) );

		$this->assertEqualSets( $affiliates, $results );
	}

}
