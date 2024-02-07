<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

/**
 * Register the Visibility taxonomy
 */
class Taxonomy_Visibility {

	/**
	 * Taxonomy slug
	 *
	 * @var string
	 */
	protected $slug = 'visibility';

	/**
	 * Taxonomy registration arguments
	 *
	 * @var array
	 */
	protected $args = [
		'hierarchical'      => false,
		'meta_box_cb'       => false,
		'public'            => false,
		'query_var'         => false,
		'rewrite'           => false,
		'show_admin_column' => false,
		'show_in_menu'      => false,
		'show_in_nav_menus' => false,
		'show_in_rest'      => true,
		'show_ui'           => false,
	];

	/**
	 * Bind hooks
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Register the taxonomy
	 *
	 * @return void
	 */
	public function register(): void {
		$post_types = [ get_option( 'aip_petition_slug' ) ?: 'petition' ];
		$arguments  = array_merge( [ 'labels' => $this->labels() ], $this->args );

		register_taxonomy( $this->slug, $post_types, $arguments );

		$exists = 'term_exists';
		if ( function_exists( 'wpcom_vip_term_exists' ) ) {
			$exists = 'wpcom_vip_term_exists';
		}

		if ( ! $exists( 'hidden', $this->slug ) ) {
			wp_insert_term( __( 'Hidden', 'aip' ), $this->slug, [ 'slug' => 'hidden' ] );
		}
	}

	/**
	 * Declare the taxonomy labels
	 *
	 * @return array
	 */
	protected function labels(): array {
		return [
			/* translators: [admin] */
			'name'                  => _x( 'Visibilities', 'taxonomy general name', 'amnesty' ),
			/* translators: [admin] */
			'singular_name'         => _x( 'Visibility', 'taxonomy singular name', 'amnesty' ),
			/* translators: [admin] */
			'search_items'          => __( 'Search Visibilities', 'amnesty' ),
			/* translators: [admin] */
			'all_items'             => __( 'All Visibilities', 'amnesty' ),
			/* translators: [admin] */
			'parent_item'           => __( 'Parent Visibility', 'amnesty' ),
			/* translators: [admin] */
			'parent_item_colon'     => __( 'Parent Visibility:', 'amnesty' ),
			/* translators: [admin] */
			'edit_item'             => __( 'Edit Visibility', 'amnesty' ),
			/* translators: [admin] */
			'view_item'             => __( 'View Visibility', 'amnesty' ),
			/* translators: [admin] */
			'update_item'           => __( 'Update Visibility', 'amnesty' ),
			/* translators: [admin] */
			'add_new_item'          => __( 'Add New Visibility', 'amnesty' ),
			/* translators: [admin] */
			'new_item_name'         => __( 'New Visibility', 'amnesty' ),
			/* translators: [admin] */
			'add_or_remove_items'   => __( 'Add or remove Visibilities', 'amnesty' ),
			/* translators: [admin] */
			'choose_from_most_used' => __( 'Choose from most frequently used Visibilities', 'amnesty' ),
			/* translators: [admin] */
			'not_found'             => __( 'No Visibilities found.', 'amnesty' ),
			/* translators: [admin] */
			'no_terms'              => __( 'No Visibilities', 'amnesty' ),
			/* translators: [admin] */
			'items_list_navigation' => __( 'Visibilities list navigation', 'amnesty' ),
			/* translators: [admin] */
			'items_list'            => __( 'Visibilities list', 'amnesty' ),
			/* translators: [admin] Tab heading when selecting from the most used terms. */
			'most_used'             => _x( 'Most Used', 'Visibilities', 'amnesty' ),
			/* translators: [admin] */
			'back_to_items'         => __( '&larr; Back to Visibilities', 'amnesty' ),
		];
	}

}
