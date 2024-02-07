<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

/**
 * Logs page class
 */
class Page_Logs {

	/**
	 * Required settings management capability
	 *
	 * @var string
	 */
	protected $cap = 'manage_options';

	/**
	 * Logger class instance
	 *
	 * @var \Amnesty\Petitions\AbstractLogger
	 */
	protected $logger = null;

	/**
	 * List table class instance
	 *
	 * @var \WP_List_Table
	 */
	protected $list_table = null;

	/**
	 * Bind hooks
	 *
	 * @param AbstractLogger $logger logger class instance
	 */
	public function __construct( AbstractLogger $logger = null ) {
		$this->logger = $logger;

		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_filter( 'set-screen-option', [ $this, 'set_screen_options' ], 10, 3 );
		add_action( 'load-petition_page_petitions_logs', [ $this, 'add_screen_options' ] );
	}

	/**
	 * Register settings pages
	 *
	 * @return void
	 */
	public function register_page(): void {
		/* translators: [admin] */
		$title = __( 'Petitions Logs', 'aip' );
		add_submenu_page(
			'edit.php?post_type=petition',
			$title,
			$title,
			$this->cap,
			'petitions_logs',
			[ $this, 'render' ],
			25
		);
	}

	/**
	 * Render the logs page
	 *
	 * @return void
	 */
	public function render(): void {
		$this->list_table = new Logs_List_Table();

		require_once dirname( __DIR__ ) . '/views/logs.php';
	}

	/**
	 * Undocumented function
	 *
	 * @param bool   $status whether to keep option value. Unused here
	 * @param string $option the option name. Unused here
	 * @param int    $value  the option value
	 *
	 * @return int
	 */
	public function set_screen_options( bool $status = false, string $option = '', int $value = 10 ): int {
		$status = wp_validate_boolean( $status );
		$option = sanitize_key( $option );

		return $value;
	}

	/**
	 * Register screen option(s)
	 *
	 * @return void
	 */
	public function add_screen_options(): void {
		add_screen_option(
			'per_page',
			[
				/* translators: [admin] */
				'label'   => __( 'Petition Logs Per Page', 'aip' ),
				'option'  => 'amnesty_petitions_logs_per_page',
				'default' => 20,
			] 
		);
	}

}
