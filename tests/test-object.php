<?php
namespace AffWP\Object;

use AffWP\Tests\UnitTestCase;

/**
 * AffWP\Object tests
 *
 * @covers AffWP\Object
 */
class Tests extends UnitTestCase {

	/**
	 * Affiliate fixture.
	 *
	 * @access protected
	 * @var int
	 * @static
	 */
	protected static $affiliate_id = 0;

	/**
	 * Referral fixture.
	 *
	 * @access protected
	 * @var int
	 * @static
	 */
	protected static $referral_id = 0;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {

		self::$affiliate_id = parent::affwp()->affiliate->create();

		self::$referral_id = parent::affwp()->referral->create( array(
			'affiliate_id' => self::$affiliate_id
		) );
	}

	/**
	 * Destroy fixtures.
	 */
	public static function wpTearDownAfterClass() {
		affwp_delete_referral( self::$referral_id );
		affwp_delete_affiliate( self::$affiliate_id );
	}

	/**
	 * @covers AffWP\Object::__get()
	 */
	public function test__get_with_real_property_should_return_property_value() {
		$status = affwp_get_affiliate_status( self::$affiliate_id );

		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			$this->assertSame( $status, $affiliate->status );
		}
	}

	/**
	 * @covers AffWP\Object::__get()
	 */
	public function test__get_with_fake_property_should_return_null() {
		$key = rand_str( 8 );

		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			$this->assertNull( $affiliate->{$key} );
		}
	}

	/**
	 * @covers AffWP\Object::set()
	 */
	public function test_set_should_return_always_true_if_save_is_default_false() {
		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			$this->assertTrue( $affiliate->set( 'foo', 'bar' ) );
		}
	}

	/**
	 * @covers AffWP\Object::__set()
	 */
	public function test__set_magic_method_should_set_property_in_memory_only() {
		$key   = rand_str( 5 );
		$value = rand_str( 10 );

		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			$affiliate->{$key} = $value;

			$this->assertSame( $value, $affiliate->{$key} );
			$this->assertNull( affwp_get_affiliate( self::$affiliate_id )->{$key} );
		}
	}

	/**
	 * @covers AffWP\Object::set()
	 */
	public function test_set_should_set_property_in_memory_only() {
		$key   = rand_str( 5 );
		$value = rand_str( 10 );

		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			$affiliate->set( $key, $value );

			$this->assertSame( $value, $affiliate->{$key} );
			$this->assertNull( affwp_get_affiliate( self::$affiliate_id )->{$key} );
		}
	}

	/**
	 * @covers AffWP\Object::set()
	 */
	public function test_set_with_save_parameter_true_and_fake_key_should_return_false() {
		$key   = rand_str( 5 );
		$value = rand_str( 10 );
		
		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			$this->assertFalse( $affiliate->set( $key, $value, $save = true ) );
		}
	}

	/**
	 * @covers AffWP\Object::set()
	 */
	public function test_set_with_save_parameter_true_and_real_key_should_return_true() {
		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			// Initial status should be 'active'.
			$this->assertSame( 'active', $affiliate->status );

			// Update status to 'pending'.
			$this->assertTrue( $affiliate->set( 'status', 'pending', $save = true ) );

			// Saved status should be 'pending'.
			$this->assertSame( 'pending', affwp_get_affiliate( self::$affiliate_id )->status );
		}

	}

	/**
	 * @covers AffWP\Object::save()
	 */
	public function test_save_on_success_should_return_true() {
		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			$affiliate->set( 'status', 'pending' );

			$this->assertTrue( $affiliate->save() );
		}
	}

	/**
	 * @covers AffWP\Object::save()
	 */
	public function test_save_on_failure_should_return_false() {
		if ( $affiliate = affwp_get_affiliate( self::$affiliate_id ) ) {
			// Delete affiliate to force the update process to fail.
			affwp_delete_affiliate( self::$affiliate_id );

			$this->assertFalse( $affiliate->save() );
		}
	}
}
