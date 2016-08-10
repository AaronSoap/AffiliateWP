<?php
/**
 * Tests for Affiliate_WP_Payouts_DB class
 *
 * @covers Affiliate_WP_Payouts_DB
 * @group database
 * @group payouts
 */
class Payouts_DB_Tests extends AffiliateWP_UnitTestCase {

	/**
	 * @covers Affiliate_WP_Payouts_DB::add()
	 */
	public function test_add_should_return_false_if_affiliate_id_undefined() {
		$this->assertFalse( affiliate_wp()->affiliates->payouts->add() );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::add()
	 */
	public function test_add_should_return_false_if_invalid_affiliate_id() {
		$this->assertFalse( affiliate_wp()->affiliates->payouts->add( array(
			'affiliate_id' => rand( 500, 5000 )
		) ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::add()
	 */
	public function test_add_should_return_false_if_no_referrals_defined() {
		$affiliate_id = $this->affwp->affiliate->create();

		$this->assertFalse( affiliate_wp()->affiliates->payouts->add( array(
			'affiliate_id' => $affiliate_id
		) ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::add()
	 */
	public function test_add_should_convert_array_of_referral_ids_to_comma_separated_string() {
		$payout_id = $this->affwp->payout->create();

		$referrals = affiliate_wp()->affiliates->payouts->get_referral_ids( $payout_id );
		$referrals = implode( ',', $referrals );

		$this->assertSame( $referrals, affiliate_wp()->affiliates->payouts->get_column( 'referrals', $payout_id ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::payout_exists()
	 */
	public function test_payout_exists_should_return_false_if_payout_does_not_exist() {
		$this->assertFalse( affiliate_wp()->affiliates->payouts->payout_exists( 0 ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::payout_exists()
	 */
	public function test_payout_exists_should_return_true_if_payout_exists() {
		$payout_id = $this->affwp->payout->create();

		$this->assertTrue( affiliate_wp()->affiliates->payouts->payout_exists( $payout_id ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::payout_exists()
	 */
	public function test_column_defaults_should_return_zero_for_payout_id() {
		$defaults = affiliate_wp()->affiliates->payouts->get_column_defaults();

		$this->assertSame( 0, $defaults['payout_id'] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::payout_exists()
	 */
	public function test_column_defaults_should_return_paid_status() {
		$defaults = affiliate_wp()->affiliates->payouts->get_column_defaults();

		$this->assertSame( 'paid', $defaults['status'] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::payout_exists()
	 */
	public function test_column_defaults_should_return_the_current_date_for_date() {
		$defaults = affiliate_wp()->affiliates->payouts->get_column_defaults();

		$this->assertSame( date( 'Y-m-d H:i:s' ), $defaults['date'] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_columns()
	 */
	public function test_get_columns_should_return_all_columns() {
		$columns = affiliate_wp()->affiliates->payouts->get_columns();

		$expected = array(
			'payout_id'     => '%d',
			'affiliate_id'  => '%d',
			'referrals'     => '%s',
			'amount'        => '%s',
			'payout_method' => '%s',
			'status'        => '%s',
			'date'          => '%s',
		);

		$this->assertEqualSets( $expected, $columns );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_object()
	 */
	public function test_get_object_should_return_false_if_invalid_payout_id() {
		$this->assertFalse( affiliate_wp()->affiliates->payouts->get_object( 0 ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_object()
	 */
	public function test_get_object_should_return_payout_object_if_valid_payout_id() {
		$payout_id = $this->affwp->payout->create();

		$this->assertInstanceOf( 'AffWP\Affiliate\Payout', affiliate_wp()->affiliates->payouts->get_object( $payout_id ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_referral_ids()
	 */
	public function test_get_referral_ids_should_return_empty_array_if_invalid_payout_id() {
		$this->assertSame( array(), affiliate_wp()->affiliates->payouts->get_referral_ids( 0 ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_referral_ids()
	 */
	public function test_get_referral_ids_should_return_empty_array_if_invalid_payout_object() {
		$this->assertSame( array(), affiliate_wp()->affiliates->payouts->get_referral_ids( new \stdClass() ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_referral_ids()
	 */
	public function test_get_referral_ids_should_return_an_array_of_referral_ids() {
		$affiliate_id = $this->affwp->affiliate->create();

		$payout_id = $this->affwp->payout->create( array(
			'affiliate_id' => $affiliate_id,
			'referrals'    => $referrals = $this->affwp->referral->create_many( 3, array(
				'affiliate_id' => $affiliate_id
			) )
		) );

		$this->assertEqualSets( $referrals, affiliate_wp()->affiliates->payouts->get_referral_ids( $payout_id ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_number_should_return_number_if_available() {
		$this->affwp->payout->create_many( 5 );

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'number' => 3
		) );

		$this->assertSame( 3, count( $payouts ) );
		$this->assertTrue( count( $payouts ) <= 3 );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_offset_should_offset_number_given() {
		$this->affwp->payout->create_many( 5 );

		$all_payouts = affiliate_wp()->affiliates->payouts->get_payouts();

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'number' => 3,
			'offset' => 2,
		) );

		$this->assertEqualSets( $payouts, array_slice( $all_payouts, 2, 3 ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_single_payout_id_should_return_that_payout() {
		$single = $this->affwp->payout->create();
		$this->affwp->payout->create_many( 3 );

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'payout_id' => $single
		) );

		$this->assertCount( 1, $payouts );
		$this->assertSame( $single, $payouts[0]->payout_id );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_multiple_payout_ids_should_return_those_payouts() {
		$payouts = $this->affwp->payout->create_many( 3 );

		$to_query = array( $payouts[0], $payouts[2] );

		$results = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'payout_id' => $to_query,
			'order'     => 'ASC', // Default descending.
		) );

		$this->assertCount( 2, $results );
		$this->assertSame( $to_query, wp_list_pluck( $results, 'payout_id' ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_single_affiliate_id_should_return_payouts_for_that_affiliate_only() {
		// Total of 5 payouts, two different affiliates.
		$this->affwp->payout->create_many( 3 );

		$this->affwp->payout->create_many( 2, array(
			'affiliate_id' => $affiliate_id = $this->affwp->affiliate->create()
		) );

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'affiliate_id' => $affiliate_id
		) );

		$this->assertSame( 2, count( $payouts ) );
		$this->assertSame( array( $affiliate_id ), array_unique( wp_list_pluck( $payouts, 'affiliate_id' ) ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_multiple_affiliate_ids_should_return_payouts_for_multiple_assuming_number() {
		// Total of 4 payouts, two different affiliates.
		$this->affwp->payout->create_many( 2, array(
			'affiliate_id' => $affiliate1 = $this->affwp->affiliate->create()
		) );

		$this->affwp->payout->create_many( 2, array(
			'affiliate_id' => $affiliate2 = $this->affwp->affiliate->create()
		) );

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'affiliate_id' => array( $affiliate1, $affiliate2 )
		) );

		$affiliates = wp_list_pluck( $payouts, 'affiliate_id' );

		$this->assertTrue(
			in_array( $affiliate1, $affiliates, true )
			&& in_array( $affiliate2, $affiliates, true )
		);
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_single_paid_referral_id_should_return_the_payout_for_that_referral() {
		$this->affwp->payout->create_many( 3 );
		$payout = $this->affwp->payout->create( array(
			'affiliate_id' => $affiliate_id = $this->affwp->affiliate->create(),
			'referrals'    => $referral_id = $this->affwp->referral->create( array(
				'affiliate_id' => $affiliate_id,
				'status'       => 'paid',
			) )
		) );

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'referrals' => $referral_id
		) );

		$this->assertCount( 1, $payouts );

		$payout_referrals = affiliate_wp()->affiliates->payouts->get_referral_ids( $payouts[0] );

		$this->assertSame( array( $referral_id ), $payout_referrals );
	}

	public function test_get_payouts_with_multiple_paid_referrals_should_return_the_payouts_for_those_referrals() {
		$this->affwp->payout->create_many( 3 );
		$payout = $this->affwp->payout->create( array(
			'affiliate_id' => $affiliate_id = $this->affwp->affiliate->create(),
			'referrals'    => $referrals = $this->affwp->referral->create_many( 3, array(
				'affiliate_id' => $affiliate_id,
				'status'       => 'paid'
			) )
		) );

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'referrals' => $referrals
		) );

		$this->assertCount( 1, $payouts );

		$payout_referrals = affiliate_wp()->affiliates->payouts->get_referral_ids( $payouts[0] );

		$this->assertEqualSets( $referrals, $payout_referrals );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_single_unpaid_referral_id_should_ignore_referrals_arg() {
		$this->affwp->payout->create_many( 3 );
		$payout_id = $this->affwp->payout->create( array(
			'affiliate_id' => $affiliate = $this->affwp->affiliate->create(),
			'referrals'    => $referral = $this->affwp->referral->create( array(
				'affiliate_id' => $affiliate,
				'status'       => 'unpaid'
			) )
		) );

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'referrals' => $referral
		) );

		$this->assertFalse( 1 === count( $payouts ) );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_multiple_unpaid_referrals_should_ignore_referrals_arg() {
		$this->affwp->payout->create();

		$this->affwp->payout->create( array(
			'affiliate_id' => $affiliate = $this->affwp->affiliate->create(),
			'referalls'    => $referrals = $this->affwp->referral->create_many( 2, array(
				'affiliate_id' => $affiliate
			) )
		) );

		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'referrals' => $referrals
		) );

		$this->assertCount( 2, $payouts );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_should_default_to_all_statuses() {
		$payouts = $this->affwp->payout->create_many( 3 );

		$payout_ids = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts(), 'payout_id' );

		$this->assertEqualSets( $payouts, $payout_ids );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_paid_status_should_return_only_paid_status_payouts() {
		$paid_payouts   = $this->affwp->payout->create_many( 3 );
		$failed_payouts = $this->affwp->payout->create_many( 3, array(
			'status' => 'failed'
		) );

		$payout_ids = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'status' => 'paid'
		) ), 'payout_id' );

		$this->assertEqualSets( $paid_payouts, $payout_ids );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_failed_status_should_return_only_failed_status_payouts() {
		$paid_payouts   = $this->affwp->payout->create_many( 3 );
		$failed_payouts = $this->affwp->payout->create_many( 3, array(
			'status' => 'failed'
		) );

		$payout_ids = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'status' => 'failed'
		) ), 'payout_id' );

		$this->assertEqualSets( $failed_payouts, $payout_ids );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_with_invalid_status_should_default_to_paid_status() {
		$paid   = $this->affwp->payout->create_many( 3 );
		$failed = $this->affwp->payout->create_many( 2, array( 'status' => 'failed' ) );

		$payout_ids = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'status' => 'foo'
		) ), 'payout_id' );

		$this->assertEqualSets( $paid, $payout_ids );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_amount_only_should_return_payouts_matching_that_amount() {
		$payout_id = $this->affwp->payout->create( array(
			'amount' => '5.00'
		) );
		$this->affwp->payout->create_many( 3 );

		$payouts = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'amount' => 5
		) ), 'payout_id' );

		$this->assertCount( 1, $payouts );
		$this->assertSame( $payout_id, $payouts[0] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_amount_min_and_max_should_return_payouts_between_them() {
		$one   = $this->affwp->payout->create( array( 'amount' => '1.00' ) );
		$three = $this->affwp->payout->create( array( 'amount' => '3.00' ) );
		$five  = $this->affwp->payout->create( array( 'amount' => '5.00' ) );

		$payouts = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'amount' => array(
				'min' => 2,
				'max' => 4
			)
		) ), 'payout_id' );

		$this->assertSame( $three, $payouts[0] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_amount_with_greater_than_compare_should_return_payouts_greater_than_amount() {
		// Default payout is 3.00 (3 referrals x 1.00 each).
		$three = $this->affwp->payout->create_many( 2 );
		$four  = $this->affwp->payout->create( array(
			'amount' => '4.00'
		) );

		$payouts = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'amount'         => 3,
			'amount_compare' => '>',
		) ), 'payout_id' );

		$this->assertSame( $four, $payouts[0] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_amount_with_less_than_compare_should_return_payouts_less_than_amount() {
		// Default payout is 3.00 (3 referrals x 1.00 each).
		$three = $this->affwp->payout->create();
		$four  = $this->affwp->payout->create_many( 2, array(
			'amount' => '4.00'
		) );

		$payouts = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'amount'         => 4,
			'amount_compare' => '<',
		) ), 'payout_id' );

		$this->assertSame( $three, $payouts[0] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_amount_with_greater_than_equals_should_return_payouts_greater_than_or_equal() {
		// Default payout is 3.00 (3 referrals x 1.00 each).
		$three = $this->affwp->payout->create_many( 2 );
		$four  = $this->affwp->payout->create( array(
			'amount' => '5.00'
		) );

		$payouts = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'amount'         => 4,
			'amount_compare' => '>=',
		) ), 'payout_id' );

		$this->assertSame( $four, $payouts[0] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_amount_with_less_than_equal_should_return_payouts_less_than_or_equal() {
		// Default payout is 3.00 (3 referrals x 1.00 each).
		$three = $this->affwp->payout->create();
		$four  = $this->affwp->payout->create_many( 2, array(
			'amount' => '5.00'
		) );

		$payouts = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'amount'         => 4,
			'amount_compare' => '<=',
		) ), 'payout_id' );

		$this->assertSame( $three, $payouts[0] );
	}

	/**
	 * @covers Affiliate_WP_Payouts_DB::get_payouts()
	 */
	public function test_get_payouts_amount_with_not_equal_should_return_payouts_not_equal() {
		// Default payout is 3.00 (3 referrals x 1.00 each).
		$three = $this->affwp->payout->create();
		$four  = $this->affwp->payout->create_many( 2, array(
			'amount' => '5.00'
		) );

		$payouts = wp_list_pluck( affiliate_wp()->affiliates->payouts->get_payouts( array(
			'amount'         => 5,
			'amount_compare' => '!=',
		) ), 'payout_id' );

		$this->assertSame( $three, $payouts[0] );
	}
}
