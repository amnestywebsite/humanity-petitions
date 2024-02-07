<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use WP_Post;
use WP_Query;

/**
 * Database adapter class
 */
class Database_Adapter implements Adapter {

	/**
	 * Record a petition signature
	 *
	 * @param \WP_Post $petition  the signatory's petition
	 * @param array    $signature the sanitised signatory data
	 *
	 * @throws \Amnesty_Petitions\Exception exception thrown on error
	 *
	 * @return int
	 */
	public static function record_signature( WP_Post $petition, array $signature = [] ): int {
		$title = $signature['first_name'] . ' ' . $signature['last_name'];

		if ( static::post_exists( $petition, $signature ) ) {
			static::setcookie( $petition->ID );
			throw new Exception(
				/* translators: [front] */
				esc_html__( 'You have already signed this petition', 'aip' )
			);
		}

		$inserted = wp_insert_post(
			[
				'post_type'     => 'signatory',
				'post_parent'   => $petition->ID,
				'post_content'  => $signature['email'],
				'post_title'    => $title,
				'meta_input'    => $signature,
				'post_status'   => 'publish',
				'post_date'     => gmdate( 'Y-m-d H:i:s', time() ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', time() ),
			],
			true
		);

		if ( is_wp_error( $inserted ) ) {
			throw new Exception( esc_html( $inserted->get_error_message() ), 'error' );
		}

		return $inserted;
	}

	/**
	 * Get signatures for a petition
	 *
	 * @param \WP_Post $petition the petition to get signatures for
	 * @param int      $per_page the signatures per page
	 * @param int      $page the page number
	 *
	 * @return array
	 */
	public static function get_signatures( WP_Post $petition, int $per_page = 10, int $page = 1 ): array {
		$query = new WP_Query(
			[
				'post_type'      => 'signatory',
				'post_parent'    => $petition->ID,
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'orderby'        => 'date',
				'order'          => 'DESC',
			]
		);

		return $query->posts;
	}

	/**
	 * Count recorded signatures for a petition
	 *
	 * @param \WP_Post $petition the petition to count signatures for
	 *
	 * @global \wpdb $wpdb
	 *
	 * @return int
	 */
	public static function count_signatures( WP_Post $petition ): int {
		global $wpdb;

		// phpcs:ignore
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %d",
				'signatory',
				$petition->ID
			)
		);

		return absint( $count );
	}

	/**
	 * Check whether the signatory has signed this petition before
	 *
	 * @param \WP_Post            $petition  the petition post object
	 * @param array<string,mixed> $signature the signatory data
	 *
	 * @return bool
	 */
	protected static function post_exists( WP_Post $petition, array $signature ): bool {
		$title = $signature['first_name'] . ' ' . $signature['last_name'];
		$query = new WP_Query(
			[
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'post_parent'    => $petition->ID,
				'post_status'    => 'publish',
				'post_type'      => 'signatory',
				'posts_per_page' => 10,
				'title'          => $title,
			]
		);

		if ( ! $query->have_posts() ) {
			return false;
		}

		while ( $query->have_posts() ) {
			$query->the_post();

			if ( trim( get_the_content() ) !== $signature['email'] ) {
				continue;
			}

			wp_reset_postdata();
			return true;
		}

		wp_reset_postdata();
		return false;
	}

	/**
	 * Mark the petition as being signed by the user
	 *
	 * @param integer $petition_id the petition being signed
	 *
	 * @return void
	 */
	protected static function setcookie( int $petition_id = 0 ): void {
		// phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
		$cookieval = sanitize_text_field( $_COOKIE['amnesty_petitions'] ?? '' );
		if ( $cookieval ) {
			$cookieval = array_map( 'absint', explode( ',', $cookieval ) );
		}

		if ( ! is_array( $cookieval ) ) {
			$cookieval = [];
		}

		if ( ! in_array( $petition_id, $cookieval, true ) ) {
			$cookieval[] = $petition_id;
		}

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
		setcookie( 'amnesty_petitions', implode( ',', $cookieval ), 0, '/', wp_parse_url( home_url(), PHP_URL_HOST ), true, true );
	}

}
