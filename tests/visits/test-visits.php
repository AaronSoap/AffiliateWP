<?php
namespace AffWP\Visit;

use AffWP\Tests\UnitTestCase;

/**
 * Visit tests
 *
 * @group visits
 */
class Visit_Tests extends UnitTestCase {

	protected $domain;

	public function setUp() {
		parent::setUp();

		$this->reset__SERVER();

		$this->domain = 'http://' . WP_TESTS_DOMAIN;
	}

	function test_long_campaign() {

		// The 2 should get trimmed off as it is the 51st character
		$campaign = '111111111111111111111111111111111111111111111111112';
		$visit_id = affiliate_wp()->visits->add( array( 'campaign' => $campaign, 'affiliate_id' => 1 ) );
		$visit    = affiliate_wp()->visits->get_object( $visit_id );

		$this->assertEquals( 50, strlen( $visit->campaign ) );
		$this->assertEquals( '11111111111111111111111111111111111111111111111111', $visit->campaign );

	}

	function test_sanitize_visit_url() {
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

	/**
	 * @covers Affiliate_WP_Tracking::get_current_page_url()
	 */
	public function test_get_current_page_url_should_return_non_front_page_url_if_not_front_page() {
		$page_id = $this->factory->post->create( array(
			'post_type' => 'page'
		) );

		$page_url = $this->mock_page_url( $page_id );

		$current_page = affiliate_wp()->tracking->get_current_page_url();

		$this->assertSame( $page_url, $current_page );
	}

	/**
	 * @covers Affiliate_WP_Tracking::get_current_page_url()
	 */
	public function test_get_current_page_url_should_respect_ssl_for_non_front_page_url() {
		$_SERVER['HTTPS'] = 'on';

		$page_id = $this->factory->post->create( array(
			'post_type' => 'page'
		) );

		$page_url = $this->mock_page_url( $page_id );

		$current_page = affiliate_wp()->tracking->get_current_page_url();

		$this->assertSame( true, is_ssl() );
		$this->assertSame( $page_url, $current_page );
	}

	/**
	 * @covers Affiliate_WP_Tracking::get_current_page_url()
	 */
	public function test_get_current_page_url_should_respect_ssl_for_non_front_page_and_not_contain_port() {
		$_SERVER['HTTPS'] = 'on';

		$page_id = $this->factory->post->create( array(
			'post_type' => 'page'
		) );

		$page_url = $this->mock_page_url( $page_id );

		$current_page = affiliate_wp()->tracking->get_current_page_url();

		$this->assertSame( true, is_ssl() );
		$this->assertFalse( strpos( $current_page, ':443' ) );
	}


	/**
	 * Utility method to reset $_SERVER values.
	 *
	 * @since 1.8.6
	 */
	protected function reset__SERVER() {
		$_SERVER['HTTP_HOST']       = WP_TESTS_DOMAIN;
		$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
		$_SERVER['REQUEST_METHOD']  = 'GET';
		$_SERVER['REQUEST_URI']     = '';
		$_SERVER['SERVER_NAME']     = WP_TESTS_DOMAIN;
		$_SERVER['SERVER_PORT']     = '80';
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

		unset( $_SERVER['HTTP_REFERER'] );
		unset( $_SERVER['HTTPS'] );
	}

	/**
	 * Utility method to mock a page URL.
	 *
	 * @since 1.8.6
	 *
	 * @param int $page_id Page ID.
	 * @return string Mocked page permalink.
	 */
	protected function mock_page_url( $page_id ) {
		$request_uri = $_SERVER['REQUEST_URI'];
		$_SERVER['REQUEST_URI'] = add_query_arg( 'page_id', $page_id, $request_uri );

		return set_url_scheme( $this->domain . $_SERVER['REQUEST_URI'] );
	}
}
