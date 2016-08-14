<?php
namespace AffWP\Affiliate\Meta;

use AffWP\Tests\UnitTestCase;

/**
 * Affiliate Meta tests
 *
 * @covers \Affiliate_WP_Affiliate_Meta_DB
 * @group affiliates
 * @group meta
 * @group database
 */
class Tests extends UnitTestCase {

	protected $_user_id = 1;
	protected $_affiliate_id = 0;
	protected $_affiliate_id2 = 0;

	function setUp() {
		parent::setUp();

		$args = array(
			'user_id' => 1
		);

		$this->_affiliate_id = affiliate_wp()->affiliates->add( $args );

	}

	function test_add_metadata() {
		$this->assertFalse( affwp_add_affiliate_meta( 0, '', '' ) );
		$this->assertFalse( affwp_add_affiliate_meta( $this->_affiliate_id, '', '' ) );
		$this->assertNotEmpty( affwp_add_affiliate_meta( $this->_affiliate_id, 'test_key', '' ) );
		$this->assertNotEmpty( affwp_add_affiliate_meta( $this->_affiliate_id, 'test_key', '1' ) );
	}

	function test_update_metadata() {
		$this->assertEmpty( affwp_update_affiliate_meta( 0, '', '' ) );
		$this->assertEmpty( affwp_update_affiliate_meta( $this->_affiliate_id, '', ''  ) );
		$this->assertNotEmpty( affwp_update_affiliate_meta( $this->_affiliate_id, 'test_key_2' , '' ) );
		$this->assertNotEmpty( affwp_update_affiliate_meta( $this->_affiliate_id, 'test_key_2', '1' ) );
	}

	function test_get_metadata() {
		$this->assertEmpty( affwp_get_affiliate_meta( $this->_affiliate_id ) );
		$this->assertEmpty( affwp_get_affiliate_meta( $this->_affiliate_id, 'key_that_does_not_exist', true ) );
		affwp_update_affiliate_meta( $this->_affiliate_id, 'test_key_2', '1' );
		$this->assertEquals( '1', affwp_get_affiliate_meta( $this->_affiliate_id, 'test_key_2', true ) );
		$this->assertInternalType( 'array', affwp_get_affiliate_meta( $this->_affiliate_id, 'test_key_2', false ) );
	}

	function test_delete_metadata() {
		affwp_update_affiliate_meta( $this->_affiliate_id, 'test_key', '1' );
		$this->assertTrue( affwp_delete_affiliate_meta( $this->_affiliate_id, 'test_key' ) );
		$this->assertFalse( affwp_delete_affiliate_meta( $this->_affiliate_id, 'key_that_does_not_exist' ) );
	}

}

