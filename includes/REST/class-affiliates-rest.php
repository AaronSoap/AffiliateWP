<?php
namespace AffWP\Affiliate;

use AffWP\REST\Controller as Controller;

/**
 * Implements REST routes and endpoints for Affiliates.
 *
 * @since 1.9
 *
 * @see AffWP\REST\Controller
 */
class REST extends Controller {

	/**
	 * Route base for affiliates.
	 *
	 * @since 1.9
	 * @access public
	 * @var string
	 */
	public $base = 'affiliates';

	/**
	 * Registers Affiliate routes.
	 *
	 * @since 1.9
	 * @access public
	 */
	public function register_routes() {

		// /affiliates/
		register_rest_route( $this->namespace, '/' . $this->base, array(
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_items' ),
			'args'     => $this->get_collection_params(),
		) );

		// /affiliates/ID
		register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>\d+)', array(
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_item' ),
			'args'     => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					}
				),
				'user' => array(
					'validate_callback' => function( $param, $request, $key ) {
						return is_string( $param );
					}
				)
			),
//			'permission_callback' => function( $request ) {
//				return current_user_can( 'manage_affiliates' );
//			}
		) );
	}

	/**
	 * Base endpoint to retrieve all affiliates.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Affiliates response object or \WP_Error object if not found.
	 */
	public function get_items( $request ) {

		$args = array();

		$args['number'] = isset( $request['number'] ) ? $request['number'] : -1;
		$args['order']  = isset( $request['order'] ) ? $request['order'] : 'ASC';

		if ( is_array( $request['filter'] ) ) {
			$args = array_merge( $args, $request['filter'] );
			unset( $request['filter'] );
		}

		/**
		 * Filters the query arguments used to retrieve affiliates in a REST request.
		 *
		 * @since 1.9
		 *
		 * @param array            $args    Arguments.
		 * @param \WP_REST_Request $request Request.
		 */
		$args = apply_filters( 'affwp_rest_affiliates_query_args', $args, $request );

		$affiliates = affiliate_wp()->affiliates->get_affiliates( $args );

		if ( empty( $affiliates ) ) {
			$affiliates = new \WP_Error(
				'no_affiliates',
				'No affiliates were found.',
				array( 'status' => 404 )
			);
		} else {
			$affiliates = array_map( array( $this, 'process_for_output' ), $affiliates );
		}

		return $this->response( $affiliates );
	}

	/**
	 * Endpoint to retrieve an affiliate by ID.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Affiliate object response or \WP_Error object if not found.
	 */
	public function get_item( $request ) {
		if ( ! $affiliate = \affwp_get_affiliate( $request['id'] ) ) {
			$affiliate = new \WP_Error(
				'invalid_affiliate_id',
				'Invalid affiliate ID',
				array( 'status' => 404 )
			);
		} else {
			$user = isset( $args['user'] ) && true == (bool) $args['user'];

			// Populate extra fields and return.
			$affiliate = $this->process_for_output( $affiliate, $user );
		}

		return $this->response( $affiliate );
	}

	/**
	 * Processes an Affiliate object for output.
	 *
	 * Populates non-public properties with derived values.
	 *
	 * @since 1.9
	 * @access protected
	 *
	 * @param \AffWP\Affiliate $affiliate Affiliate object.
	 * @param bool             $user      Optional. Whether to lazy load the user object. Default false.
	 * @return \AffWP\Affiliate Affiliate object.
	 */
	protected function process_for_output( $affiliate, $user = false ) {
		if ( false !== $user ) {
			$affiliate->user = $affiliate->get_user();
		}

		return $affiliate;
	}

	/**
	 * Retrieves the collection parameters for affiliates.
	 *
	 * @since 1.9
	 * @access public
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		/*
		 * Pass top-level args as query vars:
		 * /affiliates/?status=pending&order=desc
		 */
		$params['number'] = array(
			'description'       => __( 'The number of affiliates to query for. Use -1 for all.', 'affiliate-wp' ),
			'sanitize_callback' => 'absint',
			'validate_callback' => 'is_numeric',
		);

		$params['order'] = array(
			'description'       => __( 'How to order results. Accepts ASC (ascending) or DESC (descending).', 'affiliate-wp' ),
			'validate_callback' => function( $param, $request, $key ) {
				return in_array( strtoupper( $param ), array( 'ASC', 'DESC' ) );
			}
		);

		/*
		 * Pass any valid get_creatives() args via filter:
		 * /affiliates/?filter[status]=pending&filter[order]=desc
		 */
		$params['filter'] = array(
			'description' => __( 'Use any get_affiliates() arguments to modify the response.', 'affiliate-wp' )
		);

		return $params;
	}
}
