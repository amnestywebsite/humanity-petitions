<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions;

use WP_Error;
use WP_REST_Posts_Controller;

/**
 * REST Controller class for the signatory post type
 */
class Signatory_REST_Controller extends WP_REST_Posts_Controller {

	/**
	 * Checks if a given request has access to read posts.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		$post_type = get_post_type_object( $this->post_type );

		if ( ! isset( $post_type->cap->edit_posts ) ) {
			return new WP_Error(
				'rest_invalid',
				__( 'Something went wrong.', 'default' ),
				[ 'status' => 500 ],
			);
		}

		if ( ! current_user_can( $post_type->cap->edit_posts ) ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to edit posts in this post type.', 'default' ),
				[ 'status' => rest_authorization_required_code() ],
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a post.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|\WP_Error True if the request has read access for the item, WP_Error object or false otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$post_type = get_post_type_object( $this->post_type );

		if ( ! isset( $post_type->cap->edit_posts ) ) {
			return new WP_Error(
				'rest_invalid',
				__( 'Something went wrong.', 'default' ),
				[ 'status' => 500 ],
			);
		}

		return current_user_can( $post_type->cap->edit_posts );
	}

}
