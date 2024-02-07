<?php

if ( ! function_exists( 'amnesty_get_referrer' ) ) {
	/**
	 * Retrieve fully-qualified referrer URI
	 *
	 * @return string
	 */
	function amnesty_get_referrer() {
		$raw   = filter_var( $_SERVER['REQUEST_URI'] ?? wp_get_raw_referer(), FILTER_SANITIZE_URL );
		$parts = wp_parse_url( $raw );
		$base  = '//' . wp_parse_url( home_url( '/', 'https' ) )['host'];

		return wp_validate_redirect( set_url_scheme( $base . $parts['path'] ) );
	}
}
