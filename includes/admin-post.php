<?php
/**
 * Admin code hooked to "Posts" page.
 *
 * @package cac-creative-commons
 */

// Save routine.
add_action( 'save_post', function( $post_id ) {
	if ( isset( $_POST['cac-cc-nonce'] ) ) {
		if ( ! wp_verify_nonce( $_POST['cac-cc-nonce'], 'cac-cc-license' ) ) {
			return;
		}

		// Update license.
		if ( cac_cc_validate_license( $_POST['cac-cc-license'] ) ) {
			update_post_meta( $post_id, 'cac_cc_license', $_POST['cac-cc-license'] );
		}
	}
} );

// Add CSS.
add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style( 'cac-creative-commons-admin-post', CAC_CC_URL . 'assets/admin-post.css', array(), '20200420' );
}, 20 );

/**
 * Adds "License" field to Publish metabox.
 *
 * @since 0.1.0
 *
 * @param WP_Post $post       Post object.
 * @param bool    $show_label Whether to show the 'License:' label.
 */
function cac_cc_post_add_license_to_metabox( $post, $show_label = true ) {
	// Only show our field if the post type supports it.
	if ( false === post_type_supports( $post->post_type, 'cc-license' ) ) {
		return;
	}

	// Fetch the individual post license, if available.
	$filter = function( $retval ) use ( $post ) {
		$post_license = get_post_meta( $post->ID, 'cac_cc_license', true );
		if ( ! empty( $post_license ) ) {
			return $post_license;
		}

		return $retval;
	};

	// Add our closure as a filter.
	add_filter( 'option_cac_cc_default', $filter );

	echo '<div class="misc-pub-section misc-pub-revisions cac-cc-metabox">';

	$logo = cac_cc_get_license_link( array( 'use_logo' => true, 'logo_size' => 'compact' ) );

	if ( $show_label ) {
		$license_label = __( 'License: %s', 'cac-creative-commons' );
		printf( $license_label, $logo );
	} else {
		echo $logo;
	}

	$link_label = esc_html__( 'Edit', 'cac-creative-commons' );
	$a11y_label = esc_html__( 'Edit license', 'cac-creative-commons' );

	cac_cc_button_chooser( array(
		'link_label' => sprintf( '<span aria-hidden="true">%1$s</span> <span class="screen-reader-text">%2$s</span>', $link_label, $a11y_label ),
		'link_class' => 'edit-license hide-if-no-js',
		'link_wrapper_element' => '',
	) );
	echo '</div>';

	// Remove our temporary filter.
	remove_filter( 'option_cac_cc_default', $filter );
}
add_action( 'post_submitbox_misc_actions', 'cac_cc_post_add_license_to_metabox', 1 );

/**
 * Registers our "License" metabox for use with the Block Editor.
 *
 * Note: This is only done for the Block Editor. The Classic Editor uses
 * the older 'post_submitbox_misc_actions' hook for better integration
 * into the Publish metabox.
 *
 * @since 0.1.0
 *
 * @param string $post_type Current post type.
 */
function cac_cc_block_editor_meta_box( $post_type ) {
	// Post type doesn't support license, so bail.
	if ( ! post_type_supports( $post_type, 'cc-license' ) ) {
		return;
	}

	// Don't do this for the Classic Editor or if not WP 5.0+.
	if ( ! function_exists( 'register_block_type' ) || ! get_current_screen()->is_block_editor() ) {
		return;
	}

	add_meta_box(
		'cac-cc-block-editor',
		esc_html__( 'License', 'cac-creative-commons' ),
		'cac_cc_block_editor_meta_box_content',
		$post_type,
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'cac_cc_block_editor_meta_box', 0 );

/**
 * Display callback for our "License" metabox.
 *
 * @since 0.1.0
 *
 * @apram WP_Post Post object.
 */
function cac_cc_block_editor_meta_box_content( $post ) {
	cac_cc_post_add_license_to_metabox( $post, false );
}