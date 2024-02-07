<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use WP_List_Table;

/**
 * Logs list table class
 */
class Logs_List_Table extends WP_List_Table {

	/**
	 * Setup list table
	 */
	public function __construct() {
		parent::__construct(
			[
				/* translators: [admin] */
				'singular' => __( 'Petition', 'aip' ),
				/* translators: [admin] */
				'plural'   => __( 'Petitions', 'aip' ),
				'screen'   => 'amnesty_petitions_logs_per_page',
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
			/* translators: [admin] */
			'id'        => __( 'ID', 'aip' ),
			/* translators: [admin] */
			'timestamp' => __( 'Timestamp', 'aip' ),
			/* translators: [admin] */
			'severity'  => __( 'Severity', 'aip' ),
			/* translators: [admin] */
			'message'   => __( 'Message', 'aip' ),
		];
	}

	/**
	 * Render the ID column value
	 *
	 * @param array $log the log to render
	 *
	 * @return string
	 */
	public function column_id( array $log = [] ): string {
		return $log['id'];
	}

	/**
	 * Render the timestamp column value
	 *
	 * @param array $log the log to render
	 *
	 * @return string
	 */
	public function column_timestamp( array $log = [] ): string {
		return $log['timestamp'];
	}

	/**
	 * Render the severity column value
	 *
	 * @param array $log the log to render
	 *
	 * @return string
	 */
	public function column_severity( array $log = [] ): string {
		return ucfirst( $log['severity'] ?? 'error' );
	}

	/**
	 * Render the log message column value
	 *
	 * @param array $log the log to render
	 *
	 * @return string
	 */
	public function column_message( array $log = [] ): string {
		return $log['message'] ?? '-';
	}

	/**
	 * Retrieve logs to render
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$logger = Init::get_logger();

		$per_page = $this->get_items_per_page( 'amnesty_petitions_logs_per_page' );
		$page     = $this->get_pagenum();
		$count    = $logger->count();

		$this->set_pagination_args(
			[
				'total_items' => $count,
				'per_page'    => $per_page,
			] 
		);


		$this->items = $logger->get( $per_page, $page );
	}

}
