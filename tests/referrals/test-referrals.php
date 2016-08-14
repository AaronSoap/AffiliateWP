<?php
namespace AffWP\Referral;

use AffWP\Tests\UnitTestCase;

/**
 * Referral tests.
 *
 * @group referrals
 */
class Tests extends UnitTestCase {

	protected static $_affiliate_id = 0;
	protected static $_referral_id = 0;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {

		self::$_affiliate_id = parent::affwp()->affiliate->create();

		self::$_referral_id = parent::affwp()->referral->create( array(
			'affiliate_id' => self::$_affiliate_id,
			'amount'       => 10,
			'status'       => 'pending',
			'context'      => 'tests',
			'custom'       => 4,
			'reference'    => 5
		) );
	}

	/**
	 * Destroy fixtures.
	 */
	public static function wpTearDownAfterClass() {
		affwp_delete_affiliate( self::$_affiliate_id );
		affwp_delete_referral( self::$_referral_id );
	}

	function test_get_referral() {
		$this->assertFalse( affwp_get_referral( 0 ) );
		$this->assertNotEmpty( affwp_get_referral( self::$_referral_id ) );
	}

	function test_get_referral_status() {
		$this->assertEquals( 'pending', affwp_get_referral_status( self::$_referral_id ) );
	}

	function test_get_referral_status_label() {
		$this->assertEquals( 'Pending', affwp_get_referral_status_label( self::$_referral_id ) );
	}

	function test_set_referral_status() {
		$this->assertEquals( 'pending', affwp_get_referral_status( self::$_referral_id ) );
		affwp_set_referral_status( self::$_referral_id, 'unpaid' );
		$this->assertEquals( 'unpaid', affwp_get_referral_status( self::$_referral_id ) );
		affwp_set_referral_status( self::$_referral_id, 'rejected' );
		$this->assertEquals( 'rejected', affwp_get_referral_status( self::$_referral_id ) );
	}

	/**
	 * @covers Affiliate_WP_Referrals_DB::get_referrals()
	 */
	public function test_get_referrals_referral_id() {
		$referrals = affiliate_wp()->referrals->get_referrals( array(
			'referral_id' => self::$_referral_id
		) );

		$this->assertSame( self::$_referral_id, $referrals[0]->referral_id );
	}
}
