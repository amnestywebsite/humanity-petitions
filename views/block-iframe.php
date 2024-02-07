<?php

$url = $attributes['iframeUrl'];

if ( $attributes['passUtmParameters'] ) {
	$qsa = wp_parse_url( current_url(), PHP_URL_QUERY );

	if ( $qsa ) {
		$qsa = query_string_to_array( $qsa );
		$qsa = array_filter( $qsa, fn ( string $key ): bool => 0 === strpos( $key, 'utm_' ), ARRAY_FILTER_USE_KEY );
		$url = add_query_arg( $qsa, $url );
	}
}

?>
<iframe width="100%" height=<?php echo esc_attr( $attributes['iframeHeight'] ); ?> src="<?php echo esc_url( $url ); ?>" frameborder="0"></iframe>
