<?php
namespace AffWP\Tools;

use AffWP\Tests\UnitTestCase;

require_once( AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/tools.php' );

/**
 * Tests for functions in includes/admin/tools/tools.php
 *
 * @group tools
 */
class Tests extends UnitTestCase {

	/**
	 * @covers ::affwp_get_current_tools_tab()
	 */
	public function test_get_current_tools_tab_default_should_return_export_import() {
		$this->assertSame( 'export_import', affwp_get_current_tools_tab() );
	}

	/**
	 * @covers ::affwp_get_current_tools_tab()
	 */
	public function test_get_current_tools_tab_valid_GET_tab_value_should_return_value() {
		$_GET['tab'] = 'recount';

		$this->assertSame( 'recount', affwp_get_current_tools_tab() );
	}

	/**
	 * @covers ::affwp_get_current_tools_tab()
	 */
	public function test_get_current_tools_tab_invalid_GET_tab_value_should_return_export_import() {
		$_GET['tab'] = 'foobar';

		$this->assertSame( 'export_import', affwp_get_current_tools_tab() );
	}
}
