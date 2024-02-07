<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

/**
 * Signatures admin page render class
 */
class Page_Signatures {

	/**
	 * Required settings management capability
	 *
	 * @var string
	 */
	protected $cap = 'manage_options';

	/**
	 * List table class instance
	 *
	 * @var \WP_List_Table
	 */
	protected $list_table = null;

	/**
	 * Petition post type slug
	 *
	 * @var string
	 */
	protected $post_type = 'petition';

	/**
	 * Bind hooks
	 */
	public function __construct() {
		$this->post_type = get_option( 'aip_petition_slug' ) ?: 'petition';

		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_filter( 'set-screen-option', [ $this, 'set_screen_options' ], 10, 3 );
		add_action( "load-{$this->post_type}_page_{$this->post_type}_signatures", [ $this, 'add_screen_options' ] );
	}

	/**
	 * Register settings pages
	 *
	 * @return void
	 */
	public function register_page(): void {
		$petition = get_post( filter_input( INPUT_GET, $this->post_type, FILTER_SANITIZE_NUMBER_INT ) );
		if ( ! $petition ) {
			return;
		}

		/* translators: [admin] */
		$title = __( 'Petition Signatures', 'aip' );
		add_submenu_page(
			sprintf( 'edit.php?post_type=%s', $this->post_type ),
			$title,
			$title,
			$this->cap,
			sprintf( '%s_signatures', $this->post_type ),
			[ $this, 'render' ],
			25
		);
	}

	/**
	 * Render the page
	 *
	 * @return void
	 */
	public function render(): void {
		$this->list_table = new Signatures_List_Table();

		require_once dirname( __DIR__ ) . '/views/signatures.php';
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
				'label'   => __( 'Signatures Per Page', 'aip' ),
				'option'  => 'amnesty_petitions_signatures_per_page',
				'default' => 20,
			] 
		);
	}

}
