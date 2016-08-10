<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * AffWP_List_Table class
 *
 * Defines the base AffiliateWP list table,
 * which is then extended by all list tables
 * in AffiliateWP.
 *
 * @since 1.9
 */
abstract class AffWP_List_Table extends WP_List_Table {

	/**
	 * Default number of items to show per page
	 *
	 * @var string
	 * @since 1.9
	 */
	public $per_page = 30;

	/**
	 * Defines the context of the class,
	 * such as 'affiliates', or 'creatives'.
	 *
	 * For list tables extending this class,
	 * the plural object name should be defined here,
	 * such as 'affiliates', or 'creatives'.
	 *
	 * @var string
	 * @since 1.9
	 */
	public $singular = 'base';

	/**
	 * Defines the context of the class,
	 * such as 'affiliates', or 'creatives'.
	 *
	 * For list tables extending this class,
	 * the plural object name should be defined here,
	 * such as 'affiliates', or 'creatives'.
	 *
	 * @var string
	 * @since 1.9
	 */
	public $plural = 'base';

	/**
	 * Get things started
	 *
	 * @since 1.9
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular'  => $this->singular,
			'plural'    => $this->plural,
			'ajax'      => false
		) );

	}

	/**
	 * Show the search field.
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
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}

		?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
	<?php
	}

	/**
	 * Set the views available to the get_views() method.
	 *
	 * Provide an array of arrays of views.
	 *
	 * For each view, four parameters may be specified:
	 *
	 * $key             The key used for queries and identidication of this view,
	 *                  such as 'all' or 'paid'.
	 *
	 * $key => $value   The term used in the column header, specified as the value of the first key
	 *                  in the array. Typically with the first letter capitalized.
	 *
	 * 'add_query_arg'  Boolean. If set to true, the link within sprintf will render add_query_arg.
	 *                  If false, remove_query_arg will be rendered instead.
	 *
	 * 'data'           The data parameter to use for this view. $this->get_views() will check for
	 *                  a defined property with which to get this data, in the followin order:
	 *
	 *                      1. An existing local variable defined within the method.
	 *                      2. An existing variable defined within the list table class.
	 *                      3. An existing method defined within the list table class.
	 *
	 *                  If all of these checks fail, the $data_property will return false,
	 *                  If $this-debug is set to true, a failure of these check will also
	 *                  generate a notice in the error_log.
	 *
	 *                  The 'data' parameter will be wrapped in a <span> with a class of "count".
	 *
	 * Example:
	 *
	 *    $args = array(
	 *		'all' => array(
	 *			'all'           => __( 'All', 'affiliate-wp' ),
	 *			'add_query_arg' => true,
	 *			'data'          => $total_count
	 *			),
	 *      'paid' => array(
	 *			'paid'          => __( 'Paid', 'affiliate-wp' ),
	 *			'add_query_arg' => false,
	 *			'data'          => $paid_count
	 *			),
     *
     *		);
     *
     *     return $args;
	 *
	 * @since  1.9
	 *
	 * @return  array An array of views available.
	 */
	public function set_views() {

		$args = array(
			'all' => array(
				'all'          => __( 'All', 'affiliate-wp' ),
				'add_query_arg' => true,
				'data' => $total_count
				),

			);
		return $args;
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.9
	 * @return array $views All the views available for this class.
	 */
	public function get_views() {

		$base    = admin_url( 'admin.php?page=affiliate-wp-' . $this->plural );
		$current = isset( $_GET['status'] ) ? $_GET['status'] : '';

		$views   = array();
		$args    = $this->set_views();

		foreach ( $args as $key => $value ) {

			$term = $value[ $key ];
			$data = $value[ 'data' ];

			// Check if a variable is locally defined matching the $data name.
			if ( isset( $data ) ) {
				$data_property = $data;
			// Check if a variable is defined in this class matching the $data name.
			} else if ( $this->{$value[ 'data' ]} ) {
				$data_property = $this->{$value[ 'data' ]};
			// Check if a method is defined in this class matching the $data name.
			} else if ( method_exists( $this, $data ) ) {
				$data_property = $this->{$data}();
			} else {
				$data_property = '';
				error_log( 'Unable to locate variable or method for ' . $data . 'property' );
			}

			$data_display = '&nbsp;<span class="count">(' . $data_property  . ')</span>';
			$add          = add_query_arg( 'status',    $base );
			$remove       = remove_query_arg( 'status', $base );
			$query_arg    = ( $value[ 'add_query_arg' ] ) ? $add: $remove;

			$view = array(
				$key => sprintf( '<a href="%s"%s>%s</a>', esc_url( $query_arg ), $current === $key || $current == '' ? ' class="current"' : '', $term . $data_display )
				);

			array_push( $views, $view );
		}

		return $views;
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @access public
	 * @since  1.9
	 * @return array $columns Array of all the list table columns.
	 * @uses   $this->_get_columns()
	 */
	public function get_columns() {
		$columns = $this->_get_columns();

		return $columns;

	}

	/**
	 * Set the table columns.
	 *
	 * @access public
	 * @since  1.9
	 * @return array $columns Array of all the list table columns.
	 */
	private function _get_columns() {
		$columns = array();

		/**
		 * This filter defines the table columns for this list table.
		 *
		 * The filter name is structured as:
		 *    1. Prefix: affwp_list_table.
		 *    2. The singular form of the object name, eg 'affiliate'.
		 *    3. Suffix: _table_columns
		 *
		 *    Example:
		 *    affwp_list_table_affiliate_table_columns
		 *
		 * @since  1.9
		 */
		return apply_filters( 'affwp_list_table_' . $this->singular . '_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns.
	 *
	 * @access public
	 * @since  1.9
	 * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {
		$sortable_columns = $this->_get_sortable_columns();

		return $sortable_columns;
	}

	/**
	 * Set the table's sortable columns.
	 *
	 * @access public
	 * @since  1.9
	 * @return array Array of all the sortable columns.
	 */
	private function _get_sortable_columns() {
		$sortable_columns = array();

		/**
		 * This filter defines the sortable columns for the list table.
		 *
		 * The filter name is structured as:
		 *    1. Prefix: affwp_list_table.
		 *    2. The singular form of the object name, eg 'affiliate'.
		 *    3. Suffix: _sortable_columns
		 *
		 *    Example:
		 *    affwp_list_table_affiliate_sortable_columns
		 *
		 * @since  1.9
		 */
		return apply_filters( 'affwp_list_table_' . $this->singular . '_sortable_columns', $sortable_columns );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.9
	 *
	 * @param  string $column_name The name of the column.
	 *
	 * @return string The column name.
	 */
	public function column_default( $item, $column_name ) {

		$item        = '';
		$column_name = '';

		switch( $column_name ) {

			default:
				$value = isset( $column_name ) ? $column_name : '';
				break;
		}

		/**
		 * Specifies a filter for each column name in this list table.
		 *
		 * The filter name is structured as:
		 *     1. Prefix: affwp_list_table.
		 *     2. The singular form of the object name, eg 'affiliate'.
		 *     3. The name of the column.
		 *
		 *     Example:
		 *     affwp_list_table_affiliate_username
		 *
		 * @since  1.9
		 */
		return apply_filters( 'affwp_list_table_' . $this->singular . '_' . $column_name, $value );
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 1.9
	 * @access public
	 */
	function no_items() {
		_e( 'No data found.',
			'The message returned when no items are found in a list table view.',
			'affiliate-wp'
		);
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since  1.9
	 * @return array $actions Array of the bulk actions available for this list table.
	 */
	public function get_bulk_actions() {
		$actions = array();

		/**
		 * Bulk actions.
		 *
		 * Defines a filter to adjust the bulk actions available for the list table.
		 * The filter name is formatted as:
		 *
		 * The filter name is structured as:
		 *     1. Prefix: affwp_list_table.
		 *     2. The singular term of the object name, eg 'affiliate'.
		 *     3. Suffix: _bulk_actions
		 *
		 *     Example:
		 *     affwp_list_table_affiliate_bulk_actions
		 *
		 * @since  1.9
		 */
		return apply_filters( 'affwp_list_table_' . $this->singular . '_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions defined for this list table.
	 *
	 * @access public
	 * @since  1.9
	 * @return void
	 */
	abstract public function process_bulk_action();

	/**
	 * Retrieve all list table data.
	 *
	 * @access public
	 * @since  1.9
	 * @return array $table_data All list table data for this list table.
	 */
	abstract public function data();

	/**
	 * Prepares the final data for the list table.
	 *
	 * @access public
	 * @since  1.9
	 * @uses   AffWP_List_Table::get_columns()
	 * @uses   AffWP_List_Table::get_sortable_columns()
	 * @uses   AffWP_List_Table::process_bulk_action()
	 * @uses   AffWP_List_Table::table_data()
	 * @uses   WP_List_Table::get_pagenum()
	 * @uses   WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'affwp_list_table_' . $this->singular, $this->per_page );

		$columns  = $this->get_columns();

		$hidden   = array();

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		// List tables should define the
		// query in the data() method.
		$data = $this->data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			)
		);
	}
}
