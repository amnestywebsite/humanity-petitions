<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use WP_Post;
use WP_Query;

/**
 * Petition post type class
 */
class Post_Type_Petition {

	/**
	 * The post type slug
	 *
	 * @var string
	 */
	protected $post_type = 'petition';

	/**
	 * The initial post type slug
	 *
	 * @var string
	 */
	protected $original = 'petition';

	/**
	 * Bind hooks
	 */
	public function __construct() {
		$this->post_type = get_option( 'aip_petition_slug' ) ?: $this->post_type;

		register_post_type( $this->post_type, $this->args() );

		add_rewrite_rule(
			sprintf( '%s/([^/]+)/sign/?$', $this->post_type ),
			sprintf( 'index.php?%s=$matches[1]&sign=true', $this->post_type ),
			'top'
		);

		add_action( 'amnesty_permalink_settings_save', [ $this, 'save_setting' ] );

		add_action( 'load-options-permalink.php', [ $this, 'register_setting' ] );
		add_filter( 'body_class', [ $this, 'add_body_class' ] );
		add_filter( 'admin_body_class', [ $this, 'add_admin_body_class' ] );
		add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
		add_action( 'pre_get_posts', [ $this, 'register_for_list_queries' ] );
		add_filter( 'amnesty_list_query_post_types', [ $this, 'list_query_post_types' ] );
		add_action( 'pre_get_posts', [ $this, 'alter_archive_query_limit' ] );
		add_action( 'pre_get_posts', [ $this, 'filter_hidden_from_queries' ] );
		add_filter( 'post_row_actions', [ $this, 'actions' ], 10, 2 );
		add_filter( 'post_type_link', [ $this, 'fix_permalinks' ], 10, 2 );
	}

	/**
	 * Potentially save new post type slug
	 *
	 * @param array<string,mixed> $postarray raw $_POST data
	 *
	 * @return void
	 */
	public function save_setting( array $postarray ): void {
		if ( ! isset( $postarray['aip_petition_slug'] ) ) {
			return;
		}

		$slug = sanitize_title_with_dashes( $postarray['aip_petition_slug'] );

		if ( ! $slug ) {
			return;
		}

		$old_slug = get_option( 'aip_petition_slug' );

		if ( $slug === $old_slug ) {
			return;
		}

		update_option( 'aip_petition_slug', $slug );

		$this->post_type = $slug;

		// rename existing post type
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$GLOBALS['wpdb']->update( $wpdb->posts, [ 'post_type' => $slug ], [ 'post_type' => $old_slug ] );
	}

	/**
	 * Register the setting to allow custom petitions permalink slug
	 *
	 * @return void
	 */
	public function register_setting(): void {
		add_settings_field(
			'aip_petition_slug',
			/* translators: [admin] */
			__( 'Petitions URL slug', 'aip' ),
			[ $this, 'render_setting' ],
			'permalink',
			'optional'
		);
	}

	/**
	 * Render the settings field for the permalink slug
	 *
	 * @return void
	 */
	public function render_setting(): void {
		printf(
			'<input id="%1$s" class="regular-text" type="text" name="%1$s" value="%2$s">',
			'aip_petition_slug',
			esc_attr( $this->post_type )
		);
	}

	/**
	 * Add petition body class regardless of chosen post type slug
	 *
	 * @param array $classes existing body classes
	 *
	 * @return array
	 */
	public function add_body_class( array $classes = [] ): array {
		if ( is_singular( $this->post_type ) ) {
			$classes[] = 'single-petition';
		}

		if ( is_post_type_archive( $this->post_type ) ) {
			$classes[] = 'archive-petitions';
		}

		return $classes;
	}

	/**
	 * Add petition body class in admin regardless of chosen post type slug
	 *
	 * @param string $classes existing body classes
	 *
	 * @return string
	 */
	public function add_admin_body_class( string $classes = '' ): string {
		if ( $this->post_type === $GLOBALS['typenow'] ) {
			return $classes . ' post-type-petition';
		}

		return $classes;
	}

	/**
	 * Register query var
	 *
	 * @param array $vars current query vars
	 *
	 * @return array
	 */
	public function register_query_vars( array $vars = [] ): array {
		$vars[] = 'sign';
		return $vars;
	}

	/**
	 * Add petitions to post type options in list queries
	 *
	 * @param \WP_Query $query the current WP_Query instance
	 *
	 * @return void
	 */
	public function register_for_list_queries( WP_Query $query ): void {
		if ( is_admin() ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_home() && ! $query->is_category() && ! $query->is_search() ) {
			return;
		}

		$types = $query->get( 'post_type' ) ? (array) $query->get( 'post_type' ) : [ 'page', 'post' ];
		$types = $this->list_query_post_types( $types );

		$query->set( 'post_type', $types );
	}

	/**
	 * Add petitions to post types supported by amnesty list queries
	 *
	 * @param array<int,string> $types existing supported post types
	 *
	 * @return array
	 */
	public function list_query_post_types( array $types ): array {
		return array_values( array_filter( array_unique( array_merge( $types, [ $this->post_type ] ) ) ) );
	}

	/**
	 * Modify petitions archive query to retrieve all posts
	 *
	 * @param \WP_Query $query the current WP_Query instance
	 *
	 * @return void
	 */
	public function alter_archive_query_limit( WP_Query $query ): void {
		if ( is_admin() ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_post_type_archive( $this->post_type ) ) {
			return;
		}

		$query->set( 'posts_per_page', -1 );
	}

	/**
	 * Filter hidden petitions from list queries
	 *
	 * @param \WP_Query $query the current WP_Query instance
	 *
	 * @return void
	 */
	public function filter_hidden_from_queries( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( ! is_home() && ! is_archive() ) {
			return;
		}

		$post_types = (array) $query->get( 'post_type' );

		if ( ! in_array( $this->post_type, $post_types, true ) ) {
			return;
		}

		$tax_query = $query->get( 'tax_query' ) ?: [];

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		$tax_query[] = [
			'taxonomy'         => 'visibility',
			'field'            => 'slug',
			'terms'            => 'hidden',
			'include_children' => false,
			'operator'         => 'NOT IN',
		];

		$query->set( 'tax_query', $tax_query );
	}

	/**
	 * Render item actions
	 *
	 * @param array    $actions existing actions list
	 * @param \WP_Post $post    the current petition
	 *
	 * @return array
	 */
	public function actions( array $actions, WP_Post $post ): array {
		if ( $this->post_type !== $post->post_type ) {
			return $actions;
		}

		$actions['signatures'] = sprintf(
			'<a href="%s">View Signatures</a>',
			add_query_arg(
				[
					$this->post_type => $post->ID,
					'page'           => sprintf( '%s_signatures', $this->post_type ),
					'post_type'      => $this->post_type,
				],
				admin_url( '/edit.php' )
			)
		);

		return $actions;
	}

	/**
	 * Fix the permalinks for this post type
	 *
	 * WP doesn't seem to know how to build them correctly
	 *
	 * @param string   $permalink the generated permalink
	 * @param \WP_Post $post      the post object
	 *
	 * @return string
	 */
	public function fix_permalinks( string $permalink, WP_Post $post ): string {
		$slug = get_option( 'aip_petition_slug' ) ?: 'petition';

		if ( $post->post_type !== $slug ) {
			return $permalink;
		}

		// drafts don't have slugs
		if ( ! $post->post_name ) {
			return $permalink;
		}

		global $wp_post_types;

		$rewrite   = $wp_post_types[ $slug ]->rewrite['slug'] ?? $slug;
		$post_link = sprintf( '%s/%s/', $rewrite, $post->post_name );

		return esc_url( home_url( $post_link ) );
	}

	/**
	 * Retrieve post type registration arguments
	 *
	 * @return array
	 */
	protected function args(): array {
		$template = apply_filters( 'amnesty_petitions_template', [ 'amnesty/petition-template' ] );

		return [
			'labels'              => $this->labels(),
			/* translators: [admin] */
			'label'               => __( 'Petition', 'aip' ),
			/* translators: [admin] */
			'description'         => __( 'Petition post type', 'aip' ),
			'supports'            => [
				'custom-fields',
				'editor',
				'excerpt',
				'revisions',
				'thumbnail',
				'title',
			],
			'menu_icon'           => 'dashicons-welcome-write-blog',
			'menu_position'       => 21,
			'capability_type'     => 'page',
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => $this->post_type,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_in_rest'        => true,
			'template'            => $template,
			'template_lock'       => 'all',
			'codename'            => 'petition',
			'rewrite'             => [
				'with_front' => false,
				'slug'       => $this->post_type,
			],
		];
	}

	/**
	 * Retrieve post type labels
	 *
	 * @return array
	 */
	protected function labels(): array {
		return [
			/* translators: [admin] */
			'name'                  => _x( 'Petitions', 'Post Type General Name', 'aip' ),
			/* translators: [admin] */
			'singular_name'         => _x( 'Petition', 'Post Type Singular Name', 'aip' ),
			/* translators: [admin] */
			'menu_name'             => __( 'Petitions', 'aip' ),
			/* translators: [admin] */
			'name_admin_bar'        => __( 'Petition', 'aip' ),
			/* translators: [admin] */
			'archives'              => __( 'Petition Archives', 'aip' ),
			/* translators: [admin] */
			'attributes'            => __( 'Petition Attributes', 'aip' ),
			/* translators: [admin] */
			'parent_item_colon'     => __( 'Parent Petition:', 'aip' ),
			/* translators: [admin] */
			'all_items'             => __( 'All Petitions', 'aip' ),
			/* translators: [admin] */
			'add_new_item'          => __( 'Add New Petition', 'aip' ),
			/* translators: [admin] */
			'add_new'               => __( 'Add New', 'aip' ),
			/* translators: [admin] */
			'new_item'              => __( 'New Petition', 'aip' ),
			/* translators: [admin] */
			'edit_item'             => __( 'Edit Petition', 'aip' ),
			/* translators: [admin] */
			'update_item'           => __( 'Update Petition', 'aip' ),
			/* translators: [admin] */
			'view_item'             => __( 'View Petition', 'aip' ),
			/* translators: [admin] */
			'view_items'            => __( 'View Petitions', 'aip' ),
			/* translators: [admin] */
			'search_items'          => __( 'Search Petition', 'aip' ),
			/* translators: [admin] */
			'not_found'             => __( 'No Petitions Found', 'aip' ),
			/* translators: [admin] */
			'not_found_in_trash'    => __( 'No Petitions Found in Trash', 'aip' ),
			/* translators: [admin] */
			'featured_image'        => __( 'Featured Image', 'aip' ),
			/* translators: [admin] */
			'set_featured_image'    => __( 'Set featured image', 'aip' ),
			/* translators: [admin] */
			'remove_featured_image' => __( 'Remove featured image', 'aip' ),
			/* translators: [admin] */
			'use_featured_image'    => __( 'Use as featured image', 'aip' ),
			/* translators: [admin] */
			'insert_into_item'      => __( 'Insert into Petition', 'aip' ),
			/* translators: [admin] */
			'uploaded_to_this_item' => __( 'Uploaded to this petition', 'aip' ),
			/* translators: [admin] */
			'items_list'            => __( 'Petitions list', 'aip' ),
			/* translators: [admin] */
			'items_list_navigation' => __( 'Petitions list navigation', 'aip' ),
			/* translators: [admin] */
			'filter_items_list'     => __( 'Filter Petitions', 'aip' ),
		];
	}

}
