<?php
/**
 * Tests for Payout functions in payout-functions.php.
 *
 * @group payouts
 * @group functions
 */
class Payout_Function_Tests extends AffiliateWP_UnitTestCase {

	/**
	 * @covers affwp_get_payout()
	 */
	public function test_get_payout_with_an_invalid_payout_id_should_return_false() {
		$this->assertFalse( affwp_get_payout( 0 ) );
	}

	/**
	 * @covers affwp_get_payout()
	 */
	public function test_get_payout_with_a_valid_payout_id_should_return_a_payout_object() {
		$payout_id = $this->affwp->payout->create();

		$this->assertInstanceOf( 'AffWP\Affiliate\Payout', affwp_get_payout( $payout_id ) );
	}

	/**
	 * @covers affwp_get_payout()
	 */
	public function test_get_payout_with_an_invalid_payout_object_should_return_false() {
		$this->assertFalse( affwp_get_payout( new \stdClass() ) );
	}

	/**
	 * @covers affwp_get_payout()
	 */
	public function test_get_payout_with_a_valid_payout_object_should_return_a_payout_object() {
		$payout = $this->affwp->payout->create_and_get();

		$this->assertInstanceOf( 'AffWP\Affiliate\Payout', affwp_get_payout( $payout ) );
	}

	/**
	 * @covers affwp_add_payout()
	 */
	public function test_add_payout_without_affiliate_id_should_return_false() {
		$this->assertFalse( affwp_add_payout( array(
			'referrals' => range( 1, 3 )
		) ) );
	}

	/**
	 * @covers affwp_add_payout()
	 */
	public function test_add_payout_with_empty_referrals_should_return_false() {
		$this->assertFalse( affwp_add_payout( array(
			'affiliate_id' => 1
		) ) );
	}

	/**
	 * @covers affwp_add_payout()
	 */
	public function test_add_payout_should_return_payout_id_on_success() {
		$this->assertTrue( false !== affwp_add_payout( array(
			'affiliate_id' => $affiliate_id = $this->affwp->affiliate->create(),
			'referrals'    => $this->affwp->referral->create( array(
				'affiliate_id' => $affiliate_id
			) )
		) ) );
	}

	/**
	 * @covers affwp_add_payout()
	 */
	public function test_add_payout_should_return_false_on_failure() {
		$this->assertFalse( affwp_add_payout( array(
			'affiliate_id' => 1,
			'referrals'    => range( 1, 2 )
		) ) );
	}

	/**
	 * @covers affwp_delete_payout()
	 */
	public function test_delete_payout_should_return_false_if_invalid_payout_id() {
		$this->assertFalse( affwp_delete_payout( 0 ) );
	}

	/**
	 * @covers affwp_delete_payout()
	 */
	public function test_delete_payout_should_return_false_if_invalid_payout_object() {
		$this->assertFalse( affwp_delete_payout( new \stdClass() ) );
	}

	/**
	 * @covers affwp_delete_payout()
	 */
	public function test_delete_payout_should_return_true_if_payout_deleted_successfully() {
		$payout_id = $this->affwp->payout->create();

		$this->assertTrue( affwp_delete_payout( $payout_id ) );
	}

	/**
	 * @covers affwp_delete_payout()
	 */
	public function test_delete_payout_should_reset_paid_referral_status_to_unpaid() {

	}

	/**
	 * @covers affwp_get_payout_referrals()
	 */
	public function test_get_payout_referrals_should_return_false_if_invalid_payout() {
		$this->assertFalse( affwp_get_payout_referrals( 0 ) );
		$this->assertFalse( affwp_get_payout_referrals( new \stdClass() ) );
	}

	/**
	 * @covers affwp_get_payout_referrals()
	 */
	public function test_get_payout_referrals_should_return_array_of_referral_objects() {
		$affiliate_id = $this->affwp->affiliate->create();

		$payout_id = $this->affwp->payout->create( array(
			'affiliate_id' => $affiliate_id,
			'referrals'    => $referrals = $this->affwp->referral->create_many( 3, array(
				'affiliate_id' => $affiliate_id
			) )
		) );

		$payout_referrals = affwp_get_payout_referrals( $payout_id );

		$this->assertSame( $referrals, wp_list_pluck( $payout_referrals, 'referral_id' ) );
		$this->assertInstanceOf( 'AffWP\Referral', $payout_referrals[0] );
	}

	/**
	 * @covers affwp_get_payout_status_label()
	 */
	public function test_get_payout_status_label_should_return_false_if_invalid_payout() {
		$this->assertFalse( affwp_get_payout_status_label( 0 ) );
		$this->assertFalse( affwp_get_payout_status_label( new \stdClass() ) );
	}

	/**
	 * @covers affwp_get_payout_status_label()
	 */
	public function test_get_payout_status_label_should_return_paid_status_by_default() {
		$payout_id = $this->affwp->payout->create();

		$this->assertSame( 'Paid', affwp_get_payout_status_label( $payout_id ) );
	}

	/**
	 * @covers affwp_get_payout_status_label()
	 */
	public function test_get_payout_status_label_should_return_payout_status_label() {
		$payout_id = $this->affwp->payout->create( array(
			'status' => 'failed',
		) );

		$this->assertSame( 'Failed', affwp_get_payout_status_label( $payout_id ) );
	}

	/**
	 * @covers affwp_get_payout_status_label()
	 */
	public function test_get_payout_status_label_should_return_paid_if_invalid_status() {
		$payout_id = $this->affwp->payout->create( array(
			'status' => 'foo'
		) );

		$this->assertSame( 'Paid', affwp_get_payout_status_label( $payout_id ) );
	}
}
