<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use WP_List_Table;
use WP_Post;

/**
 * List table class for listing signatures
 */
class Signatures_List_Table extends WP_List_Table {

	/**
	 * Petitions post type slug
	 *
	 * @var string
	 */
	protected $post_type = 'petition';

	/**
	 * Setup list table
	 */
	public function __construct() {
		$this->post_type = get_option( 'aip_petition_slug' ) ?: 'petition';

		parent::__construct(
			[
				/* translators: [admin] */
				'singular' => __( 'Signature', 'aip' ),
				/* translators: [admin] */
				'plural'   => __( 'Signatures', 'aip' ),
				'screen'   => 'amnesty_petitions_signatures_per_page',
				'ajax'     => false,
			] 
		);
	}

	/**
	 * Column list
	 *
	 * @return array
	 */
	public function get_columns(): array {
		return [
			/* translators: [front/admin] input placeholder text */
			'first_name'   => __( 'First Name', 'aip' ),
			/* translators: [front/admin] input placeholder text */
			'last_name'    => __( 'Last Name', 'aip' ),
			/* translators: [front/admin] input placeholder text */
			'email'        => __( 'Email Address', 'aip' ),
			/* translators: [front/admin] input placeholder text */
			'phone'        => __( 'Telephone', 'aip' ),
			/* translators: [front/admin] input placeholder text */
			'newsletter'   => __( 'Newsletter opt-in', 'aip' ),
			/* translators: [front/admin] input placeholder text */
			'created_date' => __( 'Date Signed', 'aip' ),
		];
	}

	/**
	 * Default column value renderer
	 *
	 * @param mixed  $signature the signature to render
	 * @param string $column    the column name
	 *
	 * @return string
	 */
	public function column_default( $signature, $column ): string {
		if ( $signature instanceof WP_Post ) {
			return get_post_meta( $signature->ID, $column, true ) ?: '-';
		}

		$signature = (array) $signature;
		return $signature[ $column ] ?? '-';
	}

	/**
	 * Render the signature date column value
	 *
	 * @param mixed $signature the signature to render
	 *
	 * @return string
	 */
	public function column_created_date( $signature ): string {
		if ( $signature instanceof WP_Post ) {
			return $signature->post_date;
		}

		return $this->column_default( $signature, 'created_date' );
	}

	/**
	 * Retrieve items to render
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$adapter  = get_option( 'amnesty_petitions_settings' )['adapter'] ?? Database_Adapter::class;
		$petition = get_post( filter_input( INPUT_GET, $this->post_type, FILTER_SANITIZE_NUMBER_INT ) );

		$per_page = $this->get_items_per_page( 'amnesty_petitions_signatures_per_page' );
		$page     = $this->get_pagenum();
		$count    = $adapter::count_signatures( $petition );

		$this->set_pagination_args(
			[
				'total_items' => $count,
				'per_page'    => $per_page,
			] 
		);

		$this->items = $adapter::get_signatures( $petition, $per_page, $page );
	}

}
