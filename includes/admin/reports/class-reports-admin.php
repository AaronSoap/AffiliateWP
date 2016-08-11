<?php
/**
 * Reports Admin class.
 *
 * This class renders the Reports screen of AffiliateWP.
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Affiliates
 * @since       1.9
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AffWP_Reports_Admin {

	public function __construct(){
		add_action( 'affwp_reports_tab_affiliates', array( $this, 'affiliates' ) );
		add_action( 'affwp_reports_tab_referrals', array( $this, 'referrals' ) );
		add_action( 'affwp_reports_tab_visits', array( $this, 'visits' ) );
		add_action( 'affwp_reports_tab_registrations', array( $this, 'registrations' ) );

	}

	/**
	 * Render the admin area.
	 *
	 * @since  1.9
	 *
	 * @return void
	 */
	public function display() {

		$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], $this->get_reports_tabs() ) ? $_GET[ 'tab' ] : 'referrals';

	?>
		<div class="wrap">

			<?php do_action( 'affwp_reports_page_top' ); ?>

			<h2 class="nav-tab-wrapper">
				<?php
				$tabs = $this->get_reports_tabs();
				foreach(  $tabs as $tab_id => $tab_name ) {

					$tab_url = add_query_arg( array(
						'settings-updated' => false,
						'tab'              => $tab_id,
						'affwp_notice'     => false
					) );

					$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
						echo esc_html( $tab_name );
					echo '</a>';
				}
				?>
			</h2>

			<?php do_action( 'affwp_reports_page_middle' ); ?>

			<div id="tab_container">
				<?php do_action( 'affwp_reports_tab_' . $active_tab ); ?>
			</div><!-- #tab_container-->

			<?php do_action( 'affwp_reports_page_bottom' ); ?>

		</div>
	<?php
	}

	/**
	 * Retrieve reports tabs
	 *
	 * @since  1.9
	 * @return array $tabs
	 */
	public function get_reports_tabs() {

		$tabs                = array();
		$tabs['affiliates']  = __( 'Affiliates',  'affiliate-wp' );
		$tabs['referrals']   = __( 'Referrals',  'affiliate-wp' );
		$tabs['visits']      = __( 'Visits',     'affiliate-wp' );
		$tabs['campaigns']   = __( 'Campaigns',  'affiliate-wp' );
		// Registration to be located in Affiliates tab
		// $tabs['registrations'] = __( 'Registrations', 'affiliate-wp' );

		return apply_filters( 'affwp_reports_tabs', $tabs );
	}

	/**
	 * Display the referrals reports tab
	 *
	 * @since 1.9
	 * @return void
	 */
	public function affiliates() {
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/reports/class-reports-affiliates-list-table.php';
		$affiliate_list_table = new AffWP_Reports_Affiliates_List_Table;
		$affiliate_list_table->prepare_items();
		$affiliate_list_table->display();
	}

	/**
	 * Display the referrals reports tab
	 *
	 * @since 1.9
	 * @return void
	 */
	public function referrals() {
	?>
		<table id="affwp_total_earnings" class="affwp_table">

			<thead>

				<tr>

					<th><?php _e( 'Paid Earnings', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Paid Earnings This Month', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Paid Earnings Today', 'affiliate-wp' ); ?></th>

				</tr>

			</thead>

			<tbody>

				<tr>
					<td><?php echo affiliate_wp()->referrals->paid_earnings(); ?></td>
					<td><?php echo affiliate_wp()->referrals->paid_earnings( 'month' ); ?></td>
					<td><?php echo affiliate_wp()->referrals->paid_earnings( 'today' ); ?></td>
				</tr>

			</tbody>

		</table>

		<table id="affwp_unpaid_earnings" class="affwp_table">

			<thead>

				<tr>

					<th><?php _e( 'Unpaid Earnings', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Unpaid Earnings This Month', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Unpaid Earnings Today', 'affiliate-wp' ); ?></th>

				</tr>

			</thead>

			<tbody>

				<tr>
					<td><?php echo affiliate_wp()->referrals->unpaid_earnings(); ?></td>
					<td><?php echo affiliate_wp()->referrals->unpaid_earnings( 'month' ); ?></td>
					<td><?php echo affiliate_wp()->referrals->unpaid_earnings( 'today' ); ?></td>
				</tr>

			</tbody>

		</table>

		<table id="affwp_unpaid_counts" class="affwp_table">

			<thead>

				<tr>

					<th><?php _e( 'Unpaid Referrals', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Unpaid Referrals This Month', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Unpaid Referrals Today', 'affiliate-wp' ); ?></th>

				</tr>

			</thead>

			<tbody>

				<tr>
					<td><?php echo affiliate_wp()->referrals->unpaid_count(); ?></td>
					<td><?php echo affiliate_wp()->referrals->unpaid_count( 'month' ); ?></td>
					<td><?php echo affiliate_wp()->referrals->unpaid_count( 'today' ); ?></td>
				</tr>

			</tbody>

		</table>

		<?php
		$graph = new Affiliate_WP_Referrals_Graph;
		$graph->set( 'x_mode', 'time' );
		$graph->display();

	}


	/**
	 * Display the visits reports tab
	 *
	 * @since 1.9
	 * @return void
	 */
	public function visits() {

		$graph = new Affiliate_WP_Visits_Graph;
		$graph->set( 'x_mode',   'time' );
		$graph->set( 'currency', false  );

	?>
		<table id="affwp_total_earnings" class="affwp_table">

			<thead>

				<tr>

					<th><?php _e( 'Visits', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Successful Conversions', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Conversion Rate', 'affiliate-wp' ); ?></th>

				</tr>

			</thead>

			<tbody>

				<tr>
					<td><?php echo absint( $graph->total ); ?></td>
					<td><?php echo absint( $graph->converted ); ?></td>
					<td><?php echo $graph->get_conversion_rate(); ?>%</td>
				</tr>

			</tbody>

		</table>
	<?php
		$graph->display();

	}


	/**
	 * Display the affiliate registration reports tab
	 *
	 * @since 1.9
	 * @return void
	 */
	public function registrations() {

		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/reports/class-registrations-graph.php';

		$graph = new Affiliate_WP_Registrations_Graph;
		$graph->set( 'x_mode',   'time' );
		$graph->set( 'currency', false  );
		$graph->display();

	}


}

function affwp_reports_admin() {

	$affwp_reports_admin = new AffWP_Reports_Admin;
	$affwp_reports_admin->display();
}
