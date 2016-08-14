<?php
namespace AffWP\Visit\Functions;

use AffWP\Tests\UnitTestCase;

/**
 * Tests for Visits functions in visit-functions.php.
 *
 * @group visits
 * @group functions
 */
class Tests extends UnitTestCase {

	protected static $affiliate_id = 0;

	protected static $referral_id = 0;

	protected static $visits = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$affiliate_id = parent::affwp()->affiliate->create();

		self::$visits = parent::affwp()->visit->create_many( 4, array(
			'affiliate_id' => self::$affiliate_id
		) );

		self::$referral_id = parent::affwp()->referral->create( array(
			'affiliate_id' => self::$affiliate_id,
			'visit_id'     => self::$visits[0]
		) );

	}

	/**
	 * Destroy fixtures.
	 */
	public static function wpTearDownAfterClass() {
		affwp_delete_affiliate( self::$affiliate_id );

		foreach ( self::$visits as $visit ) {
			affwp_delete_visit( $visit );
		}

		affwp_delete_referral( self::$referral_id );
	}

	/**
	 * @covers ::affwp_get_visit()
	 */
	public function test_get_visit_with_no_visit_should_return_false() {
		$this->assertFalse( affwp_get_visit() );
	}

	/**
	 * @covers ::affwp_get_visit()
	 */
	public function test_get_visit_with_an_invalid_visit_id_should_return_false() {
		$this->assertFalse( affwp_get_visit( 0 ) );
	}

	/**
	 * @covers ::affwp_get_visit()
	 */
	public function test_get_visit_with_a_valid_visit_id_should_return_a_visit() {
		$this->assertInstanceOf( 'AffWP\Visit', affwp_get_visit( self::$visits[0] ) );
	}

	/**
	 * @covers ::affwp_get_visit()
	 */
	public function test_get_visit_with_an_invalid_visit_object_should_return_false() {
		$this->assertFalse( affwp_get_visit( new \stdClass() ) );
	}

	/**
	 * @covers ::affwp_get_visit()
	 */
	public function test_get_visit_with_a_valid_visit_object_should_return_a_visit() {
		$visit = affwp_get_visit( self::$visits[0] );

		$this->assertInstanceOf( 'AffWP\Visit', affwp_get_visit( $visit ) );
	}

	/**
	 * @covers ::affwp_count_visits()
	 */
	public function test_count_visits_with_no_affiliate_should_return_zero() {
		$this->assertSame( 0, affwp_count_visits( affwp_get_affiliate() ) );
	}

	/**
	 * @covers ::affwp_count_visits()
	 */
	public function test_count_visits_with_an_invalid_affiliate_id_should_return_zero() {
		$this->assertSame( 0, affwp_count_visits( 0 ) );
	}

	/**
	 * @covers ::affwp_count_visits()
	 */
	public function test_count_visits_with_a_valid_affiliate_id_should_return_a_count() {
		$this->assertSame( 4, affwp_count_visits( self::$affiliate_id ) );
	}

	/**
	 * @covers ::affwp_count_visits()
	 */
	public function test_count_visits_with_an_invalid_affiliate_object_should_return_zero() {
		$this->assertSame( 0, affwp_count_visits( affwp_get_affiliate() ) );
	}

	/**
	 * @covers ::affwp_count_visits()
	 */
	public function test_count_visits_with_a_valid_affiliate_object_should_return_a_count() {
		$visit = affwp_get_visit( self::$visits[0] );

		$this->assertSame( 4, affwp_count_visits( $visit ) );
	}

	/**
	 * @covers ::affwp_delete_visit()
	 */
	public function test_delete_visit_with_invalid_visit_id_should_return_false() {
		$this->assertFalse( affwp_delete_visit( 0 ) );
	}

	/**
	 * @covers ::affwp_delete_visit()
	 */
	public function test_delete_visit_with_valid_visit_id_should_return_true() {
		$visit_id = $this->factory->visit->create( array(
			'affiliate_id' => 1
		) );

		$this->assertTrue( affwp_delete_visit( $visit_id ) );

		// Clean up.
		affwp_delete_visit( $visit_id );
	}

	/**
	 * @covers ::affwp_delete_visit()
	 */
	public function test_delete_visit_with_invalid_visit_object_should_return_false() {
		$this->assertFalse( affwp_delete_visit( new \stdClass() ) );
	}

	/**
	 * @covers ::affwp_delete_visit()
	 */
	public function test_delete_visit_with_valid_visit_object_should_return_true() {
		$visit_id = affiliate_wp()->visits->add( array(
			'affiliate_id' => 1
		) );

		$visit = affiliate_wp()->visits->get( $visit_id );

		$this->assertTrue( affwp_delete_visit( $visit ) );

		// Clean up.
		affwp_delete_visit( $visit_id );
	}

	/**
	 * @covers ::affwp_delete_visit()
	 */
	public function test_delete_visit_with_non_object_non_numeric_should_return_false() {
		$this->assertFalse( affwp_delete_visit( 'foo' ) );
	}

	/**
	 * @covers ::affwp_delete_visit()
	 */
	public function test_delete_visit_should_decrease_affiliate_visit_count() {
		$visit_ids = $this->factory->visit->create_many( 3, array(
			'affiliate_id' => $affiliate_id = $this->factory->affiliate->create()
		) );

		$this->assertEquals( 3, affwp_get_affiliate_visit_count( $affiliate_id ) );

		// 3 becomes 2.
		affwp_delete_visit( $visit_ids[0] );

		$this->assertEquals( 2, affwp_get_affiliate_visit_count( $affiliate_id ) );

		// 2 becomes 1.
		affwp_delete_visit( $visit_ids[1] );

		$this->assertEquals( 1, affwp_get_affiliate_visit_count( $affiliate_id ) );

		// Clean up.
		affwp_delete_affiliate( $affiliate_id );

		foreach ( $visit_ids as $visit_id ) {
			affwp_delete_visit( $visit_id );
		}
	}

	public function test_sanitize_visit_url() {
		$referral_var = affiliate_wp()->tracking->get_referral_var();

		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/' . $referral_var . '/pippin/query_var' ), 'https://affiliatewp.com/query_var' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1' ), 'https://affiliatewp.com/sample-page/query_var/1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1/query_var2/2' ), 'https://affiliatewp.com/sample-page/query_var/1/query_var2/2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/' . $referral_var . '/pippin?query_var=1' ), 'https://affiliatewp.com?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1' ), 'https://affiliatewp.com/sample-page?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1&query_var2=2' ), 'https://affiliatewp.com/sample-page?query_var=1&query_var2=2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/' . $referral_var . '/pippin/query_var' ), 'https://www.affiliatewp.com/query_var' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1' ), 'https://www.affiliatewp.com/sample-page/query_var/1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1/query_var2/2' ), 'https://www.affiliatewp.com/sample-page/query_var/1/query_var2/2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/' . $referral_var . '/pippin?query_var=1' ), 'https://www.affiliatewp.com?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1' ), 'https://www.affiliatewp.com/sample-page?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1&query_var2=2' ), 'https://www.affiliatewp.com/sample-page?query_var=1&query_var2=2' );
	}


}
