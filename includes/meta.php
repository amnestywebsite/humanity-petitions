<?php

declare( strict_types = 1 );

if ( ! function_exists( 'amnesty_petitions_filter_hidden_from_rest_api' ) ) {
	/**
	 * Modify the petitions query to ensure hidden petitions don't show in feed
	 *
	 * @param array           $args    default query params
	 * @param WP_REST_Request $request original rest request
	 *
	 * @throws \Amnesty\Petitions\Exception error instance
	 *
	 * @return $args
	 */
	function amnesty_petitions_filter_hidden_from_rest_api( array $args, WP_REST_Request $request ): array {
		if ( 'GET' !== $request->get_method() ) {
			return $args;
		}

		if ( $request->get_param( 'show_hidden' ) ) {
			return $args;
		}

		$tax_query   = $args['tax_query'] ?? [];
		$tax_query[] = [
			'taxonomy'         => 'visibility',
			'field'            => 'slug',
			'terms'            => 'hidden',
			'include_children' => false,
			'operator'         => 'NOT IN',
		];

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		$args['tax_query'] = $tax_query;

		return $args;
	}
}

add_filter( 'rest_petition_query', 'amnesty_petitions_filter_hidden_from_rest_api', 99, 2 );
