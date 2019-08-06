<?php
/**
 * Admin code hooked to the post management screen.
 *
 * @package cac-creative-commons
 */

/* CUSTOM COLUMN *********************************************************/

// Add inline style for our custom column.
add_action( 'admin_enqueue_scripts', function() {
	$post_type = str_replace( 'edit-', '', get_current_screen()->id );
	if ( ! post_type_supports( $post_type, 'cc-license' ) ) {
		return $retval;
	}

	wp_add_inline_style( 'dashicons', '.fixed .column-cc_license{width:90px}' );
} );

add_filter( 'manage_posts_columns', 'cac_cc_admin_register_post_type_column', 10, 2 );
add_filter( 'manage_pages_columns', 'cac_cc_admin_register_post_type_column', 10, 1 );

add_action( 'manage_posts_custom_column', 'cac_cc_admin_display_post_type_column', 10, 2 );
add_action( 'manage_pages_custom_column', 'cac_cc_admin_display_post_type_column', 10, 2 );

/**
 * Registers our custom column in the admin post management screen.
 *
 * @since 0.1.0
 */
function cac_cc_admin_register_post_type_column( $retval, $post_type = 'page' ) {
	if ( post_type_supports( $post_type, 'cc-license' ) ) {
		$retval['cc_license'] = esc_html__( 'License', 'cac-creative-commons' );
	}
	return $retval;
}

/**
 * Outputs our custom column in the admin post management screen.
 *
 * @since 0.1.0
 */
function cac_cc_admin_display_post_type_column( $col_name, $post_id ) {
	if ( 'cc_license' !== $col_name ) {
		return;
	}

	// Fetch the individual post license, if available.
	$filter = function( $retval ) use ( $post_id ) {
		$post_license = get_post_meta( $post_id, 'cac_cc_license', true );
		if ( ! empty( $post_license ) ) {
			return $post_license;
		}

		return $retval;
	};

	// Add our closure as a filter.
	add_filter( 'option_cac_cc_default', $filter );

	cac_cc_license_link( array( 'use_logo' => true, 'logo_size' => 'compact' ) );

	// Remove our temporary filter.
	remove_filter( 'option_cac_cc_default', $filter );
}
