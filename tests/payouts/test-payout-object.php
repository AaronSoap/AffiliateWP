<?php
namespace AffWP\Payout\Object;

use AffWP\Tests\UnitTestCase;
use AffWP\Affiliate\Payout;

/**
 * Tests for AffWP\Affiliate\Payout
 *
 * @covers AffWP\Affiliate\Payout
 * @covers AffWP\Object
 *
 * @group payouts
 * @group objects
 */
class Tests extends UnitTestCase {

	/**
	 * @covers AffWP\Object::get_instance()
	 */
	public function test_get_instance_with_invalid_payout_id_should_return_false() {
		$this->assertFalse( Payout::get_instance( 0 ) );
	}

	/**
	 * @covers AffWP\Object::get_instance()
	 */
	public function test_get_instance_with_payout_id_should_return_Payout_object() {
		$payout_id = $this->factory->payout->create();

		$payout = Payout::get_instance( $payout_id );

		$this->assertInstanceOf( 'AffWP\Affiliate\Payout', $payout );

		// Clean up.
		affwp_delete_payout( $payout_id );
	}
}
