<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

/**
 * Signatory post type class
 */
class Post_Type_Signatory {

	/**
	 * Register the post type
	 */
	public function __construct() {
		register_post_type( 'signatory', $this->args() );
	}

	/**
	 * Retrieve post type registration arguments
	 *
	 * @return array
	 */
	protected function args(): array {
		return [
			'labels'              => $this->labels(),
			/* translators: [admin] */
			'label'               => __( 'Signatory', 'aip' ),
			/* translators: [admin] */
			'description'         => __( 'Signatory post type', 'aip' ),
			'supports'            => [ 'title', 'editor', 'revisions' ],
			'menu_icon'           => 'dashicons-welcome-write-blog',
			'menu_position'       => 5,
			'capability_type'     => 'page',
			'hierarchical'        => false,
			'public'              => false,
			'rewrite'             => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_in_rest'        => true,
			'codename'            => 'signatory',
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
			'name'                  => _x( 'Signatories', 'Post Type General Name', 'aip' ),
			/* translators: [admin] */
			'singular_name'         => _x( 'Signatory', 'Post Type Singular Name', 'aip' ),
			/* translators: [admin] */
			'menu_name'             => __( 'Signatories', 'aip' ),
			/* translators: [admin] */
			'name_admin_bar'        => __( 'Signatory', 'aip' ),
			/* translators: [admin] */
			'archives'              => __( 'Signatory Archives', 'aip' ),
			/* translators: [admin] */
			'attributes'            => __( 'Signatory Attributes', 'aip' ),
			/* translators: [admin] */
			'parent_item_colon'     => __( 'Parent Signatory:', 'aip' ),
			/* translators: [admin] */
			'all_items'             => __( 'All Signatories', 'aip' ),
			/* translators: [admin] */
			'add_new_item'          => __( 'Add New Signatory', 'aip' ),
			/* translators: [admin] */
			'add_new'               => __( 'Add New', 'aip' ),
			/* translators: [admin] */
			'new_item'              => __( 'New Signatory', 'aip' ),
			/* translators: [admin] */
			'edit_item'             => __( 'Edit Signatory', 'aip' ),
			/* translators: [admin] */
			'update_item'           => __( 'Update Signatory', 'aip' ),
			/* translators: [admin] */
			'view_item'             => __( 'View Signatory', 'aip' ),
			/* translators: [admin] */
			'view_items'            => __( 'View Signatories', 'aip' ),
			/* translators: [admin] */
			'search_items'          => __( 'Search Signatory', 'aip' ),
			/* translators: [admin] */
			'not_found'             => __( 'No Signatories Found', 'aip' ),
			/* translators: [admin] */
			'not_found_in_trash'    => __( 'No Signatories Found in Trash', 'aip' ),
			/* translators: [admin] */
			'featured_image'        => __( 'Featured Image', 'aip' ),
			/* translators: [admin] */
			'set_featured_image'    => __( 'Set featured image', 'aip' ),
			/* translators: [admin] */
			'remove_featured_image' => __( 'Remove featured image', 'aip' ),
			/* translators: [admin] */
			'use_featured_image'    => __( 'Use as featured image', 'aip' ),
			/* translators: [admin] */
			'insert_into_item'      => __( 'Insert into Signatory', 'aip' ),
			/* translators: [admin] */
			'uploaded_to_this_item' => __( 'Uploaded to this petition', 'aip' ),
			/* translators: [admin] */
			'items_list'            => __( 'Signatories list', 'aip' ),
			/* translators: [admin] */
			'items_list_navigation' => __( 'Signatories list navigation', 'aip' ),
			/* translators: [admin] */
			'filter_items_list'     => __( 'Filter Signatories', 'aip' ),
		];
	}

}
