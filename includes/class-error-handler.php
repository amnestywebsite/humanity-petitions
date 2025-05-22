<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

/**
 * Error handler class
 */
class Error_Handler extends Singleton {

	/**
	 * Instance variable
	 *
	 * @var static
	 */
	protected static $instance = null;

	/**
	 * Error store key name
	 *
	 * @var string
	 */
	protected $key = 'amnesty_petitions_errors';

	/**
	 * The stored errors
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Bind hooks
	 */
	protected function __construct() {
		$this->load_errors();

		add_action( 'amnesty_petitions_errors', [ $this, 'render_errors' ] );
	}

	/**
	 * Add an error
	 *
	 * @param \Amnesty\Petitions\Exception $e the thrown exception
	 *
	 * @return void
	 */
	public function add( Exception $e ): void {
		$errors = get_transient( $this->key );

		if ( ! is_array( $errors ) ) {
			$errors = [];
		}

		$errors[] = [
			'message'  => $e->getMessage(),
			'severity' => $e->getSeverity(),
		];

		set_transient( $this->key, $errors );
	}

	/**
	 * Render all errors
	 *
	 * @return void
	 */
	public function render_errors(): void {
		array_map( [ $this, 'render_error' ], $this->errors );

		delete_transient( 'amnesty_petitions_errors' );
	}

	/**
	 * Render an error
	 *
	 * @param array $error the error data
	 *
	 * @return void
	 */
	public function render_error( array $error = [] ): void {
		$html = sprintf(
			'<!-- wp:paragraph {"className":"amnesty-petitions-error severity-%1$s"} -->' .
			'<p class="wp-block-paragraph amnesty-petitions-error severity-%1$s">%2$s</p>' .
			'<!-- /wp:paragraph -->',
			esc_attr( $error['severity'] ),
			esc_html( $error['message'] ),
		);
		echo wp_kses_post( apply_filters( 'amnesty_petition_error_html', $html ) );
	}

	/**
	 * Load the stored errors
	 *
	 * @return void
	 */
	protected function load_errors(): void {
		$this->errors = get_transient( 'amnesty_petitions_errors' ) ?: [];
	}

}
