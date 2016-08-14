<?php
namespace AffWP\Capabilities;

use AffWP\Tests\UnitTestCase;

/**
 * User capabilities tests.
 *
 * @covers Affiliate_WP_Capabilities
 * @group capabilities
 * @group users
 */
class Tests extends UnitTestCase {

	/**
	 * User fixture.
	 *
	 * @access protected
	 * @var int
	 * @static
	 */
	protected static $user_id = 0;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		self::$user_id = parent::affwp()->user->create( array(
			'role' => 'administrator'
		) );

		// Flush the $wp_roles global.
		parent::_flush_roles();
	}

	/**
	 * @covers Affiliate_WP_Capabilities
	 */
	public function test_admin_has_caps() {

		$roles = new \Affiliate_WP_Capabilities;
		$roles->add_caps();

		$user = get_user_by( 'id', self::$user_id );

		$this->assertTrue( $user->has_cap( 'view_affiliate_reports' ) );
		$this->assertTrue( $user->has_cap( 'export_affiliate_data' ) );
		$this->assertTrue( $user->has_cap( 'export_referral_data' ) );
		$this->assertTrue( $user->has_cap( 'manage_affiliate_options' ) );
		$this->assertTrue( $user->has_cap( 'manage_affiliates' ) );
		$this->assertTrue( $user->has_cap( 'manage_referrals' ) );
		$this->assertTrue( $user->has_cap( 'manage_visits' ) );
		$this->assertTrue( $user->has_cap( 'manage_creatives' ) );

	}

}

