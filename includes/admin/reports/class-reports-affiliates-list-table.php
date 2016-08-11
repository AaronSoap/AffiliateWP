<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * AffWP_Reports_Affiliates_List_Table class
 *
 * Defines the list table which displays
 * affiliates, in the Reports screen -> Affiliates tab.
 *
 * This class extends the AffWP_Affiliates_Table class.
 *
 * @since 1.9
 */
class AffWP_Reports_Affiliates_List_Table extends AffWP_Affiliates_Table {

	/**
	 * Get things started
	 *
	 * @since 1.9
	 * @uses  parent::get_affiliate_counts()
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular'  => 'affiliate',
			'plural'    => 'affiliates',
			'ajax'      => true
		) );

		$this->get_affiliate_counts();
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since  1.9
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'name'         => __( 'Name', 'affiliate-wp' ),
			'username'     => __( 'Username', 'affiliate-wp' ),
			'affiliate_id' => __( 'Affiliate ID', 'affiliate-wp' ),
			'earnings'     => __( 'Earnings', 'affiliate-wp' ),
			'rate'     	   => __( 'Rate', 'affiliate-wp' ),
			'unpaid'       => __( 'Unpaid Referrals', 'affiliate-wp' ),
			'referrals'    => __( 'Paid Referrals', 'affiliate-wp' ),
			'visits'       => __( 'Visits', 'affiliate-wp' ),
			'status'       => __( 'Status', 'affiliate-wp' ),
			'registered'       => __( 'Registered', 'affiliate-wp' ),
		);

		/**
		 * Specifies an array of table columns to use for this list table.
		 *
		 * @param  $columns The columns for this list table.
		 * @since  1.9
		 */
		return apply_filters( 'affwp_affiliate_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.9
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'name'         => array( 'name', false ),
			'username'     => array( 'username', false ),
			'affiliate_id' => array( 'affiliate_id', false ),
			'earnings'     => array( 'earnings', false ),
			'rate'         => array( 'rate', false ),
			'unpaid'       => array( 'unpaid', false ),
			'referrals'    => array( 'referrals', false ),
			'visits'       => array( 'visits', false ),
			'status'       => array( 'status', false ),
			'registered'   => array( 'registered', false ),
		);
	}

	/**
	 * Render the registered column
	 *
	 * @access public
	 * @since  1.9
	 * @param  array $affiliate Contains all the data for the registered column
	 * @return string Date of user registration
	 */
	function column_registered( $affiliate ) {

		$user_info = get_userdata( $affiliate->user_id );
    	$value     = $user_info->user_registered;

    	/**
		 * Specifies the value of the 'registered' column in the Reports Affiliates list table.
		 *
		 * @param  $value     The value of the registered column.
		 * @param  $affiliate The affiliate to query.
		 *
		 * @since  1.9
		 */
		return apply_filters( 'affwp_affiliate_table_registered', $value, $affiliate );
	}
}
