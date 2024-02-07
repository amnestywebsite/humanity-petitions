<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use WP_Post;

/**
 * CRM adapter interface
 */
interface Adapter {

	/**
	 * Record a petition signature
	 *
	 * @param \WP_Post $petition  the signatory's petition
	 * @param array    $signature the sanitised signatory data
	 *
	 * @throws \Amnesty\Petitions\Exception error instance
	 *
	 * @return int
	 */
	public static function record_signature( WP_Post $petition, array $signature = [] ): int;

	/**
	 * Get signatures for a petition
	 *
	 * @param \WP_Post $petition the petition to get signatures for
	 * @param int      $per_page the signatures per page
	 * @param int      $page the page number
	 *
	 * @return array
	 */
	public static function get_signatures( WP_Post $petition, int $per_page = 10, int $page = 1 ): array;

	/**
	 * Count recorded signatures for a petition
	 *
	 * @param \WP_Post $petition the petition to count signatures for
	 *
	 * @return int
	 */
	public static function count_signatures( WP_Post $petition ): int;

}
