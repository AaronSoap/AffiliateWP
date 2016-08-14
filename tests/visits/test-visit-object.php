<?php
namespace AffWP\Visit\Object;

use AffWP\Tests\UnitTestCase;
use AffWP\Visit;

/**
 * Tests for AffWP\Visit
 *
 * @covers AffWP\Visit
 * @covers AffWP\Object
 *
 * @group visits
 * @group objects
 */
class Tests extends UnitTestCase {

	/**
	 * @covers AffWP\Object::get_instance()
	 */
	public function test_get_instance_with_invalid_visit_id_should_return_false() {
		$this->assertFalse( Visit::get_instance( 0 ) );
	}

	/**
	 * @covers AffWP\Object::get_instance()
	 */
	public function test_get_instance_with_visit_id_should_return_Visit_object() {
		$visit_id = $this->factory->visit->create( array(
			'referral_id'  => $this->factory->referral->create()
		) );

		$visit = Visit::get_instance( $visit_id );

		$this->assertInstanceOf( 'AffWP\Visit', $visit );
	}
}
