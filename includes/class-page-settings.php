<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use CMB2_hookup;

/**
 * Settings page class
 */
class Page_Settings {

	/**
	 * Settings identifier
	 *
	 * @var string
	 */
	protected $id = 'amnesty_petitions_settings';

	/**
	 * Admin page slug
	 *
	 * @var string
	 */
	protected $slug = 'config';

	/**
	 * Required settings management capability
	 *
	 * @var string
	 */
	protected $cap = 'manage_options';

	/**
	 * Settings pages list
	 *
	 * @var array
	 */
	protected $pages = [];

	/**
	 * The logger object
	 *
	 * @var AbstractLogger
	 */
	protected AbstractLogger $logger;

	/**
	 * Bind hooks
	 *
	 * @param AbstractLogger $logger the logger object
	 */
	public function __construct( AbstractLogger $logger ) {
		$this->logger = $logger;

		add_action( 'admin_init', [ $this, 'boot' ], 100 );
		add_action( 'admin_head', [ $this, 'register_help' ] );
		add_action( 'admin_menu', [ $this, 'register_pages' ] );
		add_action( 'cmb2_admin_init', [ $this, 'register_settings' ], 100 );
		add_action( 'current_screen', [ $this, 'maybe_save_settings' ] );
	}

	/**
	 * Boot the settings config
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->pages = apply_filters(
			'amnesty_petitions_settings_tabs',
			[
				[
					/* translators: [admin] */
					'title' => __( 'Config', 'aip' ),
					'id'    => $this->id,
					'slug'  => $this->slug,
				],
			]
		);
	}

	/**
	 * Register the settings
	 *
	 * @return void
	 */
	public function register_settings(): void {
		$settings = new_cmb2_box(
			[
				'id'           => $this->id,
				/* translators: [admin] */
				'title'        => __( 'Petitions Settings', 'aip' ),
				'object_types' => [ 'options-page' ],
				'hookup'       => false,
			]
		);

		$settings->add_field(
			[
				'id'      => 'adapter',
				/* translators: [admin] */
				'name'    => __( 'Signatory data destination', 'aip' ),
				/* translators: [admin] */
				'desc'    => __( 'Set the destination for signatory data', 'aip' ),
				'type'    => 'radio',
				'default' => Database_Adapter::class,
				'options' => $this->get_adapters(),
			]
		);

		$settings->add_field(
			[
				'id'   => 'redirect',
				/* translators: [admin] */
				'name' => __( 'Thank You URL', 'aip' ),
				/* translators: [admin] */
				'desc' => __( 'Redirect to this URL after signing a petition (can be overriden in the petition itself). Leave blank to redirect back to the petition.', 'aip' ),
				'type' => 'text_url',
			]
		);

		$settings->add_field(
			[
				'id'   => 'thank_you_title',
				/* translators: [admin] */
				'name' => __( 'Thank You Title', 'aip' ),
				/* translators: [admin] */
				'desc' => __( 'Display this title after signing a petition (can be overriden in the petition itself)', 'aip' ),
				'type' => 'text',
			]
		);

		$settings->add_field(
			[
				'id'   => 'thank_you_sub_title',
				/* translators: [admin] */
				'name' => __( 'Thank You Sub-title', 'aip' ),
				/* translators: [admin] */
				'desc' => __( 'Display this sub-title after signing a petition (can be overriden in the petition itself)', 'aip' ),
				'type' => 'text',
			]
		);

		$settings->add_field(
			[
				'id'   => 'thank_you_content',
				/* translators: [admin] */
				'name' => __( 'Thank You Content', 'aip' ),
				/* translators: [admin] */
				'desc' => __( 'Display this content after signing a petition (can be overriden in the petition itself)', 'aip' ),
				'type' => 'wysiwyg',
			]
		);

		$settings->add_field(
			[
				'id'   => 'mailing_list_text',
				/* translators: [admin] */
				'name' => __( 'Mailing list text', 'aip' ),
				/* translators: [admin] */
				'desc' => __( 'Text to show on a petition to encourage users to sign up to the mailing list', 'aip' ),
				'type' => 'textarea',
			]
		);

		$settings->add_field(
			[
				'id'      => 'send_email',
				/* translators: [admin] */
				'name'    => __( 'Send Thank You email', 'aip' ),
				/* translators: [admin] */
				'desc'    => __( 'Whether to send a thank you email to the user signing the petition', 'aip' ),
				'type'    => 'radio',
				'default' => 'no',
				'options' => [
					/* translators: [admin] */
					'no'  => __( 'No', 'aip' ),
					/* translators: [admin] */
					'yes' => __( 'Yes', 'aip' ),
				],
			]
		);

		$settings->add_field(
			[
				'id'         => 'mail_from_name',
				/* translators: [admin] */
				'name'       => __( 'Mail FROM name', 'aip' ),
				/* translators: [admin] */
				'desc'       => __( 'Thank You email FROM name', 'aip' ),
				'type'       => 'text',
				'attributes' => [
					'data-send-email' => 'yes',
				],
			]
		);

		$settings->add_field(
			[
				'id'         => 'mail_from_address',
				/* translators: [admin] */
				'name'       => __( 'Mail FROM address', 'aip' ),
				/* translators: [admin] */
				'desc'       => __( 'Thank You email FROM address', 'aip' ),
				'type'       => 'text',
				'attributes' => [
					'data-send-email' => 'yes',
				],
			]
		);

		$settings->add_field(
			[
				'id'         => 'mail_subject',
				/* translators: [admin] */
				'name'       => __( 'Email subject', 'aip' ),
				/* translators: [admin] */
				'desc'       => __( 'Thank You email message subject; dynamic variables available: {site_name}, {petition_title}', 'aip' ),
				'type'       => 'text',
				'attributes' => [
					'data-send-email' => 'yes',
				],
			]
		);

		$settings->add_field(
			[
				'id'         => 'mail_message',
				/* translators: [admin] */
				'name'       => __( 'Email text', 'aip' ),
				/* translators: [admin] */
				'desc'       => __( 'Thank You email message body; dynamic variables available: {first_name}, {last_name}, {petition_title}, {from_name}', 'aip' ),
				'type'       => 'textarea',
				'attributes' => [
					'data-send-email' => 'yes',
				],
			]
		);

		$settings->add_field(
			[
				'id'   => 'terms_text',
				/* translators: [admin] */
				'name' => __( 'Terms & Conditions text', 'aip' ),
				/* translators: [admin] */
				'desc' => __( 'This text will appear on all petition forms', 'aip' ),
				'type' => 'wysiwyg',
			]
		);
	}

	/**
	 * Register contextual help tab(s) for the settings page
	 *
	 * @return void
	 */
	public function register_help(): void {
		$screen = get_current_screen();

		if ( "petition_page_{$this->slug}" !== $screen->id ) {
			return;
		}

		ob_start();
		require_once dirname( Init::$file ) . '/views/help.php';
		$content = ob_get_clean();

		$screen->add_help_tab(
			[
				'id'       => 'petition-help',
				/* translators: [admin] */
				'title'    => __( 'Overview', 'aip' ),
				'content'  => $content,
				'priority' => 1,
			]
		);
	}

	/**
	 * Register settings pages
	 *
	 * @return void
	 */
	public function register_pages(): void {
		/* translators: [admin] */
		$title     = __( 'Petitions Settings', 'aip' );
		$post_type = get_option( 'aip_petition_slug' ) ?: 'petition';

		add_submenu_page(
			sprintf( 'edit.php?post_type=%s', $post_type ),
			$title,
			$title,
			$this->cap,
			$this->slug,
			[ $this, 'render' ],
			20
		);

		foreach ( $this->pages as $page ) {
			add_submenu_page(
				sprintf( 'edit.php?post_type=%s&page=config', $post_type ),
				$page['title'],
				$page['title'],
				$this->cap,
				$page['slug'],
				[ $this, 'render' ]
			);
		}
	}

	/**
	 * Potentially trigger settings save
	 *
	 * @return void
	 */
	public function maybe_save_settings(): void {
		if ( ! isset( $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		// phpcs:ignore
		if ( wp_get_raw_referer() !== home_url( $_SERVER['REQUEST_URI'], 'https' ) ) {
			return;
		}

		add_filter( 'whitelist_options', [ $this, 'save_settings' ] );
	}

	/**
	 * Save settings fields
	 *
	 * @param array $whitelist whitelist options - used for return only
	 *
	 * @return array
	 */
	public function save_settings( array $whitelist = [] ): array {
		// v no need for nonce verification
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = sanitize_key( $_GET['tab'] ?? $this->slug ) ?: $this->slug;
		$tab = array_values(
			array_filter(
				$this->pages,
				function ( $page ) use ( $tab ) {
					return $page['slug'] === $tab;
				}
			)
		)[0] ?? $this->pages[0];

		$cmb = cmb2_get_metabox( $tab['id'] );

		if ( $cmb ) {
			$hook = new CMB2_hookup( $cmb );

			if ( $hook->can_save( 'options-page' ) ) {
				// phpcs:ignore
				$cmb->save_fields( $tab['id'], 'options-page', $_POST );
			}
		}

		remove_filter( 'whitelist_optons', [ $this, 'save_settings' ] );

		return $whitelist;
	}

	/**
	 * Render the settings page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! function_exists( 'new_cmb2_box' ) ) {
			/* translators: [admin] */
			$message = __( 'Modifying Petitions settings requires that the CMB2 plugin is active.', 'aip' );

			$this->logger->error( $message, 500 );
			echo esc_html( $message );
			return;
		}

		// v no need for nonce verification
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = sanitize_key( $_GET['tab'] ?? $this->slug ) ?: $this->slug;
		$tab = array_values(
			array_filter(
				$this->pages,
				function ( $page ) use ( $tab ) {
					return $page['slug'] === $tab;
				}
			)
		)[0] ?? $this->pages[0];

		add_filter( 'cmb2_get_metabox_form_format', [ $this, 'form_format' ] );
		require_once dirname( __DIR__ ) . '/views/settings.php';
		remove_filter( 'cmb2_get_metabox_form_format', [ $this, 'form_format' ] );
	}

	/**
	 * Wrap the standard CMB2 form submit button in <p> tag
	 *
	 * @return string
	 */
	public function form_format(): string {
		return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<p class="submit"><input type="submit" name="submit-cmb" value="%4$s" class="button-primary"></p></form>';
	}

	/**
	 * Retrieve list of registered adapters for saving submissions
	 *
	 * @return array
	 */
	protected function get_adapters(): array {
		$adapters = apply_filters( 'amnesty_petitions_adapters', [] );
		$adapters = [
			/* translators: [admin] */
			Database_Adapter::class => __( 'Database', 'aip' ),
		] + $adapters;

		return $adapters;
	}

}
