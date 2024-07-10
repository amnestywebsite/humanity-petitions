<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use Exception;

/**
 * Block registration and management class
 */
class Register_Block {

	/**
	 * Plugin version string
	 *
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Block attributes
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Bind hooks
	 */
	public function __construct() {
		$this->version = get_plugin_data( Init::$file, false, false )['Version'] ?? $this->version;

		add_action( 'wp_enqueue_scripts', [ $this, 'register_frontend_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_editor_assets' ] );
		add_action( 'amnesty_register_features', [ $this, 'register_features' ], 10, 2 );
		$this->boot();
	}

	/**
	 * Register the block
	 *
	 * @return void
	 */
	public function boot(): void {
		register_block_type(
			'amnesty/petition',
			[
				'render_callback' => [ $this, 'render' ],
				'attributes'      => $this->get_attributes(),
			]
		);

		register_block_type(
			'amnesty/petition-template',
			[
				'render_callback' => [ $this, 'template' ],
			]
		);
	}

	/**
	 * Register assets for frontend
	 *
	 * @return void
	 */
	public function register_frontend_assets(): void {
		if ( ! is_singular( get_option( 'aip_petition_slug' ) ?: 'petition' ) ) {
			return;
		}

		wp_enqueue_style( 'amnesty-petitions-style', plugins_url( 'assets/styles/frontend.css', __DIR__ ), [], $this->version, 'all' );
		wp_enqueue_script( 'amnesty-petitions-script', plugins_url( 'assets/scripts/frontend.js', __DIR__ ), [], $this->version, true );
	}

	/**
	 * Register features
	 *
	 * @param mixed  $settings the settings object
	 * @param string $group    the settings group
	 *
	 * @return void
	 */
	public function register_features( $settings, $group ): void {
		$settings->add_group_field(
			$group,
			[
				'id'   => 'petitions_form',
				/* translators: [admin] */
				'name' => __( 'Petitions Form', 'aip' ),
				'type' => 'checkbox',
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'      => 'petitions_default_type',
				/* translators: [admin] */
				'name'    => __( 'Default petition type', 'aip' ),
				'type'    => 'select',
				/* translators: [admin] */
				'desc'    => __( 'Note: only select forms if you have enabled forms in the above option', 'aip' ),
				'options' => [
					/* translators: [admin] */
					'form'   => __( 'Form', 'aip' ),
					/* translators: [admin] */
					'iframe' => __( 'Iframe', 'aip' ),
				],
			]
		);
	}

	/**
	 * Register assets for use in gutenberg
	 *
	 * @return void
	 */
	public function register_editor_assets(): void {
		$js_deps = [ 'wp-blocks', 'wp-components' ];

		wp_enqueue_style( 'amnesty-petitions-style', plugins_url( 'assets/styles/frontend.css', __DIR__ ), [], $this->version, 'all' );
		wp_enqueue_style( 'amnesty-petitions-editor', plugins_url( 'assets/styles/editor.css', __DIR__ ), [ 'amnesty-petitions-style' ], $this->version, 'all' );
		wp_enqueue_script( 'amnesty-petitions-editor', plugins_url( 'assets/scripts/editor.js', __DIR__ ), $js_deps, $this->version, true );
		wp_set_script_translations( 'amnesty-petitions-editor', 'aip', dirname( Init::$file ) . '/languages' );

		$settings = get_option( 'amnesty_petitions_settings' ) ?: [];

		$data = [
			'terms'        => wp_kses_post( wpautop( $settings['terms_text'] ?? '' ) ),
			'mailingList'  => esc_html( $settings['mailing_list_text'] ?? '' ),
			'redirect'     => esc_url( $settings['redirect'] ?? home_url() ),
			'locale'       => str_replace( '_', '-', get_locale() ),
			'defaultType'  => $this->default_block_type(),
			'formEnabled'  => $this->form_enabled(),
			'postTypeSlug' => esc_js( get_option( 'aip_petition_slug' ) ?: 'petition' ),
		];

		if ( get_the_ID() ) {
			try {
				if ( ! isset( $settings['adapter'] ) ) {
					$settings['adapter'] = Database_Adapter::class;
				}

				$data['signatures'] = call_user_func( $settings['adapter'] . '::count_signatures', get_post() );
			} catch ( Exception $e ) {
				// something went wrong fetching count
				$data['signatures'] = 0;
			} catch ( \Error $e ) {
				// something went wrong calling adapter method
				$data['signatures'] = call_user_func( Database_Adapter::class . '::count_signatures', get_post() );
			}
		}

		wp_localize_script( 'amnesty-petitions-editor', 'amnestyPetitions', $data );
	}

	/**
	 * Render the block
	 *
	 * @param array $attributes the block's attributes
	 *
	 * @return string
	 */
	public function render( array $attributes = [] ): string {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return '';
		}

		$attributes = wp_parse_args(
			$attributes,
			array_map(
				function ( $a ) {
					return $a['default'];
				},
				$this->get_attributes()
			)
		);

		$render_type = $attributes['petitionSource'];
		if ( ! in_array( $render_type, [ 'form', 'iframe' ], true ) ) {
			$render_type = $this->default_block_type();
		}

		$template = sprintf( '%s/views/block-%s.php', dirname( Init::$file ), $render_type );
		$template = apply_filters( 'amnesty_petition_view', $template, $render_type );

		if ( 'iframe' === $render_type ) {
			ob_start();
			require $template;
			return apply_filters( 'amnesty_petition_view_markup', ob_get_clean(), $render_type );
		}

		$settings = get_option( 'amnesty_petitions_settings' );

		$adapter = $settings['adapter'] ?? Database_Adapter::class;
		if ( ! class_exists( $adapter, false ) ) {
			$adapter = Database_Adapter::class;
		}

		$signatures = 0;
		try {
			$signatures = call_user_func( [ $adapter, 'count_signatures' ], get_post() );
		} catch ( Exception $e ) {
			Init::get_logger()->error( $e->getMessage(), $e->getCode() );
		}

		$signatures = absint( $signatures );
		$percentage = max( ( $signatures / $attributes['targetCount'] ) * 100, 1 );

		// phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
		$user_has_signed = sanitize_text_field( $_COOKIE['amnesty_petitions'] ?? '' );
		if ( $user_has_signed ) {
			$user_has_signed = array_map( 'absint', explode( ',', $user_has_signed ) );
		}

		if ( ! is_array( $user_has_signed ) ) {
			$user_has_signed = [];
		}

		$user_has_signed = in_array( get_the_ID(), $user_has_signed, true );

		ob_start();
		require $template;
		return apply_filters( 'amnesty_petition_view_markup', ob_get_clean() );
	}

	/**
	 * Render the template
	 *
	 * @param array  $attributes the block's attributes (empty)
	 * @param string $content    the inner content
	 *
	 * @return string
	 */
	public function template( array $attributes = [], string $content = '' ): string {
		$template = dirname( Init::$file ) . '/views/template.php';
		$template = apply_filters( 'amnesty_petition_container', $template );

		$attributes = wp_unslash( $attributes );
		$content    = wp_unslash( $content );

		ob_start();
		require $template;
		return apply_filters( 'amnesty_petition_container_markup', ob_get_clean() );
	}

	/**
	 * Retrieve block attributes
	 *
	 * @return array
	 */
	protected function get_attributes(): array {
		if ( $this->attributes ) {
			return $this->attributes;
		}

		$settings = get_option( 'amnesty_petitions_settings' );

		$this->attributes = [
			'title'                      => $this->string( /* translators: [front] petitions call to action button */ __( 'Act Now', 'aip' ) ),
			'subtitle'                   => $this->string(),
			'className'                  => $this->string(),
			'contentTitle'               => $this->string(),
			'content'                    => $this->string(),
			'buttonText'                 => $this->string( /* translators: [admin/front] */ __( 'Sign the petition', 'aip' ) ),
			'thankYouUrl'                => $this->string( $settings['redirect'] ?? '' ),
			'thankYouTitle'              => $this->string( $settings['thank_you_title'] ?? '' ),
			'thankYouSubTitle'           => $this->string( $settings['thank_you_sub_title'] ?? '' ),
			'thankYouContent'            => $this->string( $settings['thank_you_content'] ?? '' ),
			'targetReachedText'          => $this->string(),
			'targetCount'                => $this->int( 10000 ),
			'showContent'                => $this->bool(),
			'showNewsletter'             => $this->bool(),
			'showTargetReachedText'      => $this->bool(),
			'contentIsExpanded'          => $this->bool( false ),
			'replaceProgressWithMessage' => $this->bool( false ),
			'newsletterAcceptText'       => $this->string(
				/* translators: [admin/front] whether the user wants to sign up to the newsletter when signing a petition */
				__( 'Yes, I agree', 'aip' )
			),
			'newsletterRejectText'       => $this->string(
				/* translators: [admin/front] whether the user wants to sign up to the newsletter when signing a petition */
				__( 'No, I do not agree', 'aip' )
			),
			'petitionSource'             => $this->string( $this->default_block_type() ),
			'iframeUrl'                  => $this->string(),
			'iframeHeight'               => $this->int(),
			'passUtmParameters'          => $this->bool( false ),
		];

		return $this->attributes;
	}

	/**
	 * Check whether the form variant of the block is enabled
	 *
	 * @return boolean
	 */
	protected function form_enabled(): bool {
		if ( ! function_exists( 'amnesty_feature_is_enabled' ) ) {
			return true;
		}

		return amnesty_feature_is_enabled( 'petitions_form' );
	}

	/**
	 * Retrieve the default block type
	 *
	 * @return string
	 */
	protected function default_block_type() {
		if ( ! function_exists( 'amnesty_get_feature_value' ) ) {
			return 'form';
		}

		return amnesty_get_feature_value( 'petitions_default_type', 'form' );
	}

	/**
	 * Return attribute type definition
	 *
	 * @param string $default_value the attribute default value
	 *
	 * @return array
	 */
	protected function string( string $default_value = '' ): array {
		return [
			'type'    => 'string',
			'default' => $default_value,
		];
	}

	/**
	 * Return attribute type definition
	 *
	 * @param boolean $default_value the attribute default value
	 *
	 * @return array
	 */
	protected function bool( bool $default_value = true ): array {
		return [
			'type'    => 'boolean',
			'default' => $default_value,
		];
	}

	/**
	 * Return attribute type definition
	 *
	 * @param integer $default_value the attribute default value
	 *
	 * @return array
	 */
	protected function int( int $default_value = 0 ): array {
		return [
			'type'    => 'integer',
			'default' => $default_value,
		];
	}

}
