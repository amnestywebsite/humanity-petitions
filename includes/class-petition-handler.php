<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use WP_Post;

/**
 * Handler class for petition signatures
 */
class Petition_Handler {

	/**
	 * The petition being signed
	 *
	 * @var int
	 */
	protected $petition = 0;

	/**
	 * The petition's parsed block data
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * The parsed petition block
	 *
	 * @var array
	 */
	protected $block = [];

	/**
	 * Logger class instance
	 *
	 * @var AbstractLogger
	 */
	protected $logger = null;

	/**
	 * Error handler instance
	 *
	 * @var Error_Handler
	 */
	protected $errors = null;

	/**
	 * Bind hooks
	 *
	 * @param AbstractLogger $logger logger instance
	 * @param Error_Handler  $errors error handler instance
	 */
	public function __construct( AbstractLogger $logger = null, Error_Handler $errors = null ) {
		$this->logger = $logger;
		$this->errors = $errors;

		add_action( 'template_redirect', [ $this, 'maybe_boot' ] );
		add_action( 'amnesty_sign_petition', [ $this, 'sign_petition' ], 10, 2 );
	}

	/**
	 * Potentially boot petition signature logic
	 *
	 * @return void
	 */
	public function maybe_boot(): void {
		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		// check matched route
		if ( 'true' !== get_query_var( 'sign' ) ) {
			return;
		}

		$post_type = get_option( 'aip_petition_slug' ) ?: 'petition';
		if ( get_query_var( 'post_type' ) !== $post_type ) {
			return;
		}

		$this->boot();

		$this->block = array_values(
			array_filter(
				$this->blocks,
				function ( $block ) {
					return 'amnesty/petition' === $block['blockName'];
				} 
			) 
		)[0] ?? false;

		if ( ! $this->block ) {
			return;
		}

		$this->execute();
	}

	/**
	 * Retrieve petition data
	 *
	 * @return void
	 */
	protected function boot(): void {
		$this->petition = get_the_ID();
		$this->blocks   = parse_blocks( get_post_field( 'post_content' ) );
	}

	/**
	 * Attempt to save petition signature
	 *
	 * @param int   $petition_id the petition being signed
	 * @param array $signature   the signatory data
	 *
	 * @return void
	 */
	public function sign_petition( int $petition_id = 0, array $signature = [] ): void {
		$petition = get_post( $petition_id );

		try {
			call_user_func_array( $this->get_adapter(), [ $petition, $signature ] );
			$this->setcookie( $petition_id );
		} catch ( Exception $e ) {
			$this->logger->error( $e->getMessage(), $e->getCode() );
			$this->errors->add( $e );
			return;
		}

		$settings   = get_option( 'amnesty_petitions_settings' );
		$send_email = $settings['send_email'] ?? 'no';

		if ( 'yes' === $send_email ) {
			$this->send_email( $settings, $petition, $signature );
		}
	}

	/**
	 * Execute petition signature save logic.
	 * Fields are static intentionally.
	 *
	 * @return void
	 */
	protected function execute(): void {
		$post_data = $this->sanitise_post_data();

		$signatory_data = array_filter(
			$post_data,
			function ( $key = '' ): bool {
				return ! preg_match( '/^_.+/', $key );
			},
			ARRAY_FILTER_USE_KEY 
		);

		$this->trigger_signature( $signatory_data );

		$settings = get_option( 'amnesty_petitions_settings' );
		$redirect = $post_data['_wp_http_referer'];

		if ( ! empty( $settings['redirect'] ) ) {
			$redirect = $settings['redirect'];
		}

		if ( ! empty( $this->block['attrs']['thankYouUrl'] ) ) {
			$redirect = $this->block['attrs']['thankYouUrl'];
		}

		$redirect = filter_var( $redirect, FILTER_SANITIZE_URL );

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Verify and sanitize raw $_POST data
	 *
	 * @return array<string,string>
	 */
	protected function sanitise_post_data(): array {
		$post_data = [
			'_wpnonce'         => sanitize_text_field( $_POST['_wpnonce'] ?? '' ),
			'_wp_http_referer' => esc_url_raw( $_POST['_wp_http_referer'] ?? '' ),
			'newsletter'       => sanitize_text_field( $_POST['newsletter'] ?? '' ),
			'first_name'       => sanitize_text_field( $_POST['first_name'] ?? '' ),
			'last_name'        => sanitize_text_field( $_POST['last_name'] ?? '' ),
			'phone'            => sanitize_text_field( $_POST['phone'] ?? '' ),
			'email'            => sanitize_email( $_POST['email'] ?? '' ),
		];

		$nonce = wp_verify_nonce( $post_data['_wpnonce'], 'amnesty_petition_signature' );
		if ( 1 !== $nonce ) {
			$this->errors->add(
				new Exception(
					/* translators: [front] */
					__( 'Something went wrong submitting the form. Please reload the page and try again.', 'aip' ),
					'error'
				)
			);
			wp_safe_redirect( get_permalink() );
			exit;
		}

		$referrer   = wp_parse_url( $post_data['_wp_http_referer'] );
		$permalink  = wp_parse_url( get_permalink() );
		$components = array_flip( [ 'scheme', 'host', 'path' ] );

		$referrer_parts  = array_intersect_key( $referrer, $components );
		$permalink_parts = array_intersect_key( $permalink, $components );

		if ( ! empty( array_diff_assoc( $referrer_parts, $permalink_parts ) ) ) {
			$this->errors->add(
				new Exception(
					/* translators: [front] */
					__( 'Something went wrong submitting the form. Please reload the page and try again.', 'aip' ),
					'error'
				)
			);
			wp_safe_redirect( $post_data['_wp_http_referer'] );
			exit;
		}

		return $post_data;
	}

	/**
	 * Trigger backend petition signature task
	 *
	 * @param array $signature the signature data
	 *
	 * @return void
	 */
	protected function trigger_signature( array $signature = [] ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			do_action( 'amnesty_sign_petition', $this->petition, $signature );
			return;
		}

		if ( ! wp_schedule_single_event( time(), 'amnesty_sign_petition', [ $this->petition, $signature ] ) ) {
			$this->errors->add(
				new Exception(
					/* translators: [front] */
					__( 'Something went wrong submitting the form. Please reload the page and try again.', 'aip' ),
					'error'
				)
			);
			wp_safe_redirect( get_permalink() );
			exit;
		}

		$this->setcookie( $this->petition );
	}

	/**
	 * Retrieve signature recording adapter
	 *
	 * @return string
	 */
	protected function get_adapter(): string {
		$adapter = get_option( 'amnesty_petitions_settings' )['adapter'] ?? Database_Adapter::class;

		if ( ! $adapter || ! is_callable( "{$adapter}::record_signature" ) ) {
			$this->logger->warning( "Method {$adapter}::record_signature is not callable; defaulting to Database Adapter." );
			return Database_Adapter::class . '::record_signature';
		}

		return "{$adapter}::record_signature";
	}

	/**
	 * Send petition signature thank you email
	 *
	 * @param array    $settings  mail settings
	 * @param \WP_Post $petition  the petition that was signed
	 * @param array    $signature the signatory information
	 *
	 * @return void
	 */
	protected function send_email( array $settings = [], WP_Post $petition = null, array $signature = [] ): void {
		$subject = $this->get_mail_subject( $settings, $petition );
		$message = $this->get_mail_message( $settings, $petition, $signature );

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
		wp_mail(
			$this->get_mail_to( $signature ),
			$subject,
			$message,
			[
				sprintf( 'FROM: %s', $this->get_mail_from( $settings ) ),
			] 
		);
	}

	/**
	 * Mark the petition as being signed by the user
	 *
	 * @param integer $petition_id the petition being signed
	 *
	 * @return void
	 */
	protected function setcookie( int $petition_id = 0 ): void {
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

	/**
	 * Get thank you email TO
	 *
	 * @param array $signature the signatory
	 *
	 * @return string
	 */
	protected function get_mail_to( array $signature = [] ): string {
		// translators: [admin] %1$s: first name, %2$s: family name
		$format = _x( '%1$s %2$s', 'User email TO name format', 'aip' );

		$name = sprintf( $format, $signature['first_name'], $signature['last_name'] );
		return sprintf( '%s <%s>', $name, $signature['email'] );
	}

	/**
	 * Get mail FROM
	 *
	 * @param array $settings mail settings
	 *
	 * @return string
	 */
	protected function get_mail_from( array $settings = [] ): string {
		/* translators: [front] */
		$from_name = __( 'Amnesty Petitions', 'aip' );
		$from_mail = sprintf( 'petitions@%s', strtolower( $_SERVER['SERVER_NAME'] ) ); // phpcs:ignore

		if ( ! empty( $settings['mail_from_name'] ) ) {
			$from_name = $settings['mail_from_name'];
		}

		if ( ! empty( $settings['mail_from_address'] ) ) {
			$from_mail = $settings['mail_from_address'];
		}

		return sprintf( '%s <%s>', $from_name, $from_mail );
	}

	/**
	 * Get thank you email subject
	 *
	 * @param array    $settings mail settings
	 * @param \WP_Post $petition the petition that was signed
	 *
	 * @return string
	 */
	protected function get_mail_subject( array $settings = [], WP_Post $petition = null ): string {
		/* translators: [front] */
		$subject = _x( 'Thank you for signing the petition "{petition_title}"', 'Petition signature thank you email subject', 'aip' );

		if ( ! empty( $settings['mail_subject'] ) ) {
			$subject = $settings['mail_subject'];
		}

		return str_replace(
			[ '{site_name}', '{petition_title}' ],
			[ get_bloginfo( 'name' ), $petition->post_title ],
			$subject
		);
	}

	/**
	 * Get thank you email body
	 *
	 * @param array    $settings  mail settings
	 * @param \WP_Post $petition  the petition that was signed
	 * @param array    $signature the signatory
	 *
	 * @return string
	 */
	protected function get_mail_message( array $settings = [], WP_Post $petition = null, array $signature = [] ): string {
		/* translators: [front] */
		$message = _x(
			'Hello {first_name},

		Thank you for taking action.
		One of most important things you can do right now is share this action as widely as possible.
		Whether you share on Twitter, Facebook or Whatsapp - ask your friends and family to sign this petition today.

		Thanks again,
		{from_name}',
			'Petition signature thank you email body',
			'aip' 
		);

		if ( ! empty( $settings['mail_message'] ) ) {
			$message = $settings['mail_message'];
		}

		$from_name = '';
		if ( ! empty( $settings['mail_from_name'] ) ) {
			$from_name = $settings['mail_from_name'];
		}

		return str_replace(
			[ '{first_name}', '{last_name}', '{petition_title}', '{from_name}' ],
			[ $signature['first_name'], $signature['last_name'], $petition->post_title, $from_name ],
			$message
		);
	}

}
