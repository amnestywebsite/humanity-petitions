<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

declare( strict_types = 1 );

namespace Amnesty\Petitions;

/*
Plugin Name:       Humanity Petitions
Plugin URI:        https://github.com/amnestywebsite/humanity-petitions
Description:       Enable petitions support, with interface for synchronising data to a CRM. CRM integrations not included.
Version:           2.0.0
Author:            Amnesty International
Author URI:        https://www.amnesty.org
License:           GPLv2
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       aip
Domain Path:       /languages
Network:           true
Requires PHP:      8.2.0
Requires at least: 5.8.0
Tested up to:      6.6.2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/abstract-class-singleton.php';
require_once __DIR__ . '/includes/interface-adapter.php';
require_once __DIR__ . '/includes/interface-logger.php';
require_once __DIR__ . '/includes/abstract-class-logger.php';
require_once __DIR__ . '/includes/class-database-adapter.php';
require_once __DIR__ . '/includes/class-database-logger.php';
require_once __DIR__ . '/includes/class-error-handler.php';
require_once __DIR__ . '/includes/class-exception.php';
require_once __DIR__ . '/includes/class-logs-list-table.php';
require_once __DIR__ . '/includes/class-signatures-list-table.php';
require_once __DIR__ . '/includes/class-post-type-petition.php';
require_once __DIR__ . '/includes/class-post-type-signatory.php';
require_once __DIR__ . '/includes/class-petition-handler.php';
require_once __DIR__ . '/includes/class-register-block.php';
require_once __DIR__ . '/includes/class-page-logs.php';
require_once __DIR__ . '/includes/class-page-settings.php';
require_once __DIR__ . '/includes/class-page-signatures.php';
require_once __DIR__ . '/includes/class-taxonomy-visibility.php';
require_once __DIR__ . '/includes/meta.php';

register_activation_hook(
	__FILE__,
	function () {
		Database_Logger::up();
		add_option( 'amnesty_petitions_settings', [ 'adapter' => Database_Adapter::class ] );
	}
);

register_deactivation_hook(
	__FILE__,
	function () {
		Database_Logger::down();
		delete_option( 'amnesty_petitions_settings' );
		delete_option( 'aip_petition_slug' );
	}
);

/**
 * Plugin instantiation class
 */
class Init {

	/**
	 * Plugin data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Static reference to this file
	 *
	 * @var string
	 */
	public static $file = __FILE__;

	/**
	 * Bind hooks and instantiate pages
	 */
	public function __construct() {
		$this->data = get_plugin_data( __FILE__ );

		add_filter( 'register_translatable_package', [ $this, 'register_translatable_package' ], 12 );

		add_action( 'all_admin_notices', [ $this, 'check_dependencies' ] );

		add_action( 'plugins_loaded', [ $this, 'textdomain' ] );
		add_action( 'init', [ $this, 'boot' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		new Page_Settings( $this::get_logger() );
		new Page_Logs( $this::get_logger() );
		new Page_Signatures();
		new Taxonomy_Visibility();
	}

	/**
	 * Register this plugin as a translatable package
	 *
	 * @param array<int,array<string,string>> $packages existing packages
	 *
	 * @return array<int,array<string,string>>
	 */
	public function register_translatable_package( array $packages = [] ): array {
		$packages[] = [
			'id'     => 'humanity-petitions',
			'path'   => realpath( __DIR__ ),
			'pot'    => realpath( __DIR__ ) . '/languages/aip.pot',
			'domain' => 'aip',
		];

		return $packages;
	}

	/**
	 * Output warning & deactivate if dependent plugins aren't active
	 *
	 * @return void
	 */
	public function check_dependencies(): void {
		if ( function_exists( 'cmb2_bootstrap' ) ) {
			return;
		}

		$missing = 'CMB2';
		$message = sprintf(
			// translators: [admin] %s: list of missing plugins
			__( 'The Amnesty International Petitions plugin requires these plugins to be active: %s', 'aip' ),
			$missing
		);

		printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $message ) );
		deactivate_plugins( plugin_basename( __FILE__ ), false, is_multisite() );
	}

	/**
	 * Register textdomain
	 *
	 * @return void
	 */
	public function textdomain(): void {
		load_plugin_textdomain( 'aip', false, basename( __DIR__ ) . '/languages' );
	}

	/**
	 * Instantiate required classes
	 *
	 * @return void
	 */
	public function boot(): void {
		new Post_Type_Petition();
		new Post_Type_Signatory();
		new Petition_Handler( $this::get_logger(), Error_Handler::instance() );
		new Register_Block();
	}

	/**
	 * Enqueue assets for the settings page
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		// v no need for nonce verification
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$post_type = get_option( 'aip_petition_slug' ) ?: 'petition';
		$get_type  = sanitize_text_field( $_GET['post_type'] ?? '' );
		$the_page  = sanitize_text_field( $_GET['page'] ?? '' );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( $post_type !== $get_type && 'config' !== $the_page ) {
			return;
		}

		wp_enqueue_script( 'amnesty-petitions-settings', plugins_url( 'assets/petitions-options.js', __FILE__ ), [ 'jquery' ], $this->data['Version'], true );
	}

	/**
	 * Retrieve logger instance
	 *
	 * @return AbstractLogger
	 */
	public static function get_logger(): AbstractLogger {
		$user_logger = apply_filters( 'amnesty_petitions_logger', Database_Logger::class );

		if ( is_callable( "{$user_logger}::instance" ) && is_subclass_of( $user_logger, AbstractLogger::class ) ) {
			return $user_logger::instance();
		}

		$logger = Database_Logger::instance();
		$logger->error( sprintf( '%s should extend %s; defaulting to %s', $user_logger, AbstractLogger::class, get_class( $logger ) ) );

		return $logger;
	}

}

new Init();
