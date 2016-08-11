<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * AffWP_Reports_Campaigns_List_Table class.
 *
 * Renders the Campaigns table in the Reports screen.
 *
 * @since 1.9
 */
class AffWP_Reports_Campaigns_List_Table extends WP_List_Table {

	/**
	 * Default number of items to show per page
	 *
	 * @var int
	 * @since 1.9
	 */
	public $per_page = 30;

	/**
	 * Total number of campaigns found
	 *
	 * @var int
	 * @since 1.9
	 */
	public $total_count = 0;

	/**
	 * Get things started
	 *
	 * @since 1.9
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular'  => 'campaign',
			'plural'    => 'campaigns',
			'ajax'      => true
		) );
	}

	/**
	 * Show the search field
	 *
	 * @access public
	 * @since 1.9
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
	<?php
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.9
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'campaign'        => __( 'Campaign', 'affiliate-wp' ),
			'affiliate'       => __( 'Affiliate', 'affiliate-wp' ),
			'referrers'       => __( 'Referrers', 'affiliate-wp' ),
			'referrals'       => __( 'Referrals', 'affiliate-wp' ),
			'visits'          => __( 'Visits', 'affiliate-wp' ),
			'unique_visits'   => __( 'Unique Visits', 'affiliate-wp' ),
			'conversion_rate' => __( 'Conversions', 'affiliate-wp' ),
			'date'            => __( 'Date Created', 'affiliate-wp' )
		);

		return apply_filters( 'affwp_campaign_table_columns', $columns );
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
			'campaign'      => array( 'campaign', false ),
			'affiliate'     => array( 'affiliate', false ),
			'visits'        => array( 'visits', false ),
			'unique_visits' => array( 'unique_visits', false ),
			'date'          => array( 'date', false )
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.9
	 *
	 * @param array $item Contains all the data of the campaign
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $campaign, $column_name ) {
		switch( $column_name ) {
			default:
				$value = isset( $campaign->$column_name ) ? $campaign->$column_name : '';
				break;
		}

		return apply_filters( 'affwp_campaign_table_' . $column_name, $value, $campaign );
	}

	/**
	 * Render the campaign column
	 *
	 * @access public
	 * @since 1.9
	 * @param array $referral Contains all the data for the campaign column
	 * @return string The affiliate
	 */
	public function column_campaign( $campaign ) {
		$value = '<span class="campaign-campaign ' . esc_html( $campaign->campaign ) . '"><i></i></span>';
		return apply_filters( 'affwp_campaign_table_campaign', $value, $campaign );
	}

	/**
	 * Render the affiliate column
	 *
	 * @access public
	 * @since 1.9
	 * @param array $affiliate Contains all the data for the affiliate column
	 * @return string The affiliate
	 */
	public function column_affiliate( $campaign ) {
		$affiliate = affwp_get_affiliate_id();
		$value = '<a href="' . esc_url( admin_url( 'admin.php?page=affiliate-wp-campaigns&affiliate=' . $campaign->affiliate_id ) ) . '">' . affiliate_wp()->affiliates->get_affiliate_name( $campaign->affiliate_id ) . '</a>';
		return apply_filters( 'affwp_campaign_table_affiliate', $value, $affiliate );
	}

	/**
	 * Render the urls column.
	 *
	 * Shows the top-referring urls of the campaign.
	 * Shows an attenuated quantity in the list table view.
	 *
	 * @access public
	 * @since  1.9
	 * @param  array $referral Contains all the data for the urls column.
	 * @return string Referring URLS
	 */
	public function column_referrers( $campaign ) {
		$value = ! empty( $campaign->referrer ) ? '<a href="' . esc_url( $campaign->referrer ) . '" target="_blank">' . $campaign->referrer . '</a>' : __( 'Direct traffic', 'affiliate-wp' );
		return apply_filters( 'affwp_campaign_table_urls', $value, $campaign );
	}

	/**
	 * Render the referrals column.
	 *
	 * Shows all referrals generated from this campaign.
	 * Shows an attenuated quntity in the list table view, showing
	 * only the most recent 5 referrals, if any.
	 *
	 * @access public
	 * @since  1.9
	 * @param  array $referral Contains all the data for the referrals.
	 * @return string Referrals
	 */
	public function column_referrals( $campaign ) {
		$referrals = $campaign->referrals;
		$value = '<span class="campaign-referrals ' . esc_html( $campaign->referrals ) . '"><i></i></span>';

		return apply_filters( 'affwp_campaign_table_referrals', $value, $campaign );
	}


	/**
	 * Render the visits column
	 *
	 * @access public
	 * @since 1.9
	 * @param array $visits Contains all the data for the visits.
	 * @return array visits
	 */
	function column_visits( $campaign ) {
		$visits = $campaign->visits;
		$value = '<span class="campaign-converted ' . $visits . '"><i></i></span>';

		return apply_filters( 'affwp_campaign_table_visits', $value, $campaign );
	}

	/**
	 * Render the unique visits column
	 *
	 * @access public
	 * @since 1.9
	 * @param array $visits Contains all the data for unique visits.
	 * @return array visits
	 */
	function column_unique_visits( $campaign ) {
		$unique_visits = $campaign->unique_visits;
		$value  = '<span class="campaign-converted ' . $unique_visits . '"><i></i></span>';

		return apply_filters( 'affwp_campaign_table_unique_visits', $value, $campaign );
	}

	/**
	 * Render the conversions column
	 *
	 * @access public
	 * @since 1.9
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string Converted status icon
	 */
	public function column_conversions( $campaign ) {
		$conversion_rate = $campaign->conversion_rate;
		$value = '<span class="campaign-conversion-rate ' . $conversion_rate . '"><i></i></span>';
		return apply_filters( 'affwp_campaign_table_conversion_rate', $value, $campaign );
	}

	/**
	 * Render the converted column
	 *
	 * @access public
	 * @since 1.9
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string Converted status icon
	 */
	function column_date( $campaign ) {
		// $visit = affiliate_wp()->visits->get_visits( array( 'campaign' => $campaign ) );
		// $date  = $visit->date;
		$date = '';
		$value = '<span class="campaign-date"><i></i>' . $date . '</span>';
		return apply_filters( 'affwp_campaign_table_campaigns', $value, $campaign );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.9
	 * @access public
	 */
	function no_items() {
		_e( 'No campaigns found.', 'affiliate-wp' );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function process_bulk_action() {

	}

	/**
	 * Retrieve all the data for all the campaigns.
	 *
	 * @access public
	 * @since 1.9
	 * @return array $campaigns_data Array of all the data for campaigns.
	 */
	public function campaigns_data() {

		$affiliate_id = affwp_get_affiliate_id();

		$page            = isset( $_GET['paged'] )           ? absint( $_GET['paged'] )                 : 1;
		$campaign        = isset( $_GET['campaign'] )        ? sanitize_text_field( $_GET['campaign'] ) : false;
		$affiliate       = isset( $_GET['affiliate'] )       ? absint( $_GET['affiliate'] )             : false;
		$referrers       = isset( $_GET['referrers'] )       ? absint( $_GET['urls'] )                  : false;
		$referrals       = isset( $_GET['referrals'] )       ? absint( $_GET['referrals'] )             : false;
		$visits          = isset( $_GET['visits'] )          ? absint( $_GET['visits'] )                : false;
		$unique_visits   = isset( $_GET['unique_visits'] )   ? absint( $_GET['unique_visits'] )         : false;
		$conversion_rate = isset( $_GET['conversion_rate'] ) ? absint( $_GET['conversion_rate'] )       : false;
		$order           = isset( $_GET['order'] )           ? $_GET['order']                           : 'DESC';
		$orderby         = isset( $_GET['orderby'] )         ? $_GET['orderby']                         : 'date';
		$search          = isset( $_GET['s'] )               ? sanitize_text_field( $_GET['s'] )        : '';

		$from            = ! empty( $_REQUEST['filter_from'] )   ? $_REQUEST['filter_from']   : '';
		$to              = ! empty( $_REQUEST['filter_to'] )     ? $_REQUEST['filter_to']     : '';
		$status          = ! empty( $_REQUEST['filter_status'] ) ? $_REQUEST['filter_status'] : '';

		$date = array();
		if( ! empty( $from ) ) {
			$date['start'] = $from;
		}
		if( ! empty( $to ) ) {
			$date['end']   = $to . ' 23:59:59';
		}

		if( ! empty( $user_id ) && empty( $affiliate_id ) ) {

			$affiliate_id = affiliate_wp()->affiliates->get_column_by( 'affiliate_id', 'user_id', $user_id );

		}

		if ( strpos( $search, 'referral:' ) !== false ) {
			$referral_id = absint( trim( str_replace( 'referral:', '', $search ) ) );
			$search      = '';
		} elseif ( strpos( $search, 'affiliate:' ) !== false ) {
			$affiliate_id = absint( trim( str_replace( 'affiliate:', '', $search ) ) );
			$search       = '';
		} elseif ( strpos( $search, 'campaign:' ) !== false ) {
			$campaign = trim( str_replace( 'campaign:', '', $search ) );
			$search   = '';
		}

		$per_page = $this->per_page;

		$args = array(
			'number'          => $this->per_page,
			'offset'          => $this->per_page * ( $page - 1 ),
			'search'          => $search,
			'order'           => $order,
			'orderby'         => $orderby,
			'campaign'        => $campaign,
			'affiliate'       => $affiliate_id,
			// 'referrers'       => $referrers,
			// 'referrals'       => $referrals,
			'visits'          => $visits,
			'unique_visits'   => $unique_visits,
			// 'conversion_rate' => $conversion_rate,
			// 'date'            => $date
		);

		// Get total count of campaigns
		global $wpdb;

		$campaign_cache_key = 'affwp_affiliate_campaigns_' . $affiliate_id;

		$campaigns = wp_cache_get( $campaign_cache_key, 'campaigns' );

		$this->total_count = count( $campaigns );

		return affiliate_wp()->campaigns->get_campaigns($affiliate_id);

	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.9
	 * @uses   AffWP_Reports_Campaigns_List_Table::get_columns()
	 * @uses   AffWP_Reports_Campaigns_List_Table::get_sortable_columns()
	 * @uses   AffWP_Reports_Campaigns_List_Table::process_bulk_action()
	 * @uses   AffWP_Reports_Campaigns_List_Table::campaigns_data()
	 * @uses   WP_List_Table::get_pagenum()
	 * @uses   WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'affwp_edit_campaigns_per_page', $this->per_page );

		$columns = $this->get_columns();

		$hidden = array();

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = $this->campaigns_data();

		$current_page = $this->get_pagenum();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $this->total_count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $this->total_count / $per_page )
			)
		);
	}
}
