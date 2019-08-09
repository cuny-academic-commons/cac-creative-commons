<?php
/**
 * Admin code hooked to the post management screen.
 *
 * @package cac-creative-commons
 */

// Add inline CSS.
add_action( 'admin_enqueue_scripts', function() {
	$post_type = str_replace( 'edit-', '', get_current_screen()->id );
	if ( ! post_type_supports( $post_type, 'cc-license' ) ) {
		return $retval;
	}

	$css = <<<CSS

.fixed .column-cc_license{width:90px}
fieldset.bulk-edit-cc-license a[rel] {display:inline-block; margin-top:8px;}
span.inline-edit-cc-license {display:block; float:left; width:6.2em; line-height:2.5;}
fieldset.bulk-edit-cc-license span[aria-hidden] {position:relative; top:-2px; margin-left:8px;}

CSS;

	wp_add_inline_style( 'dashicons', $css );
} );

/* CUSTOM COLUMN *********************************************************/

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

/* BULK EDIT ************************************************************/

add_action( 'bulk_edit_custom_box', 'cac_cc_admin_bulk_edit_box', 100, 2 );

/**
 * Callback to add our bulk edit functionality to the admin panel.
 *
 * @since 0.1.0
 *
 * @param string $column_name Current column name.
 * @param string $post_type   Current post type.
 */
function cac_cc_admin_bulk_edit_box( $column_name, $post_type ) {
	if ( ! post_type_supports( $post_type, 'cc-license' ) ) {
		return;
	}
	
	if ( 'cc_license' !== $column_name ) {
		return;
	}

	$license_label = esc_html__( 'License', 'cac-creative-commons' );

	printf( '<fieldset class="inline-edit-col-left bulk-edit-cc-license"><div class="inline-edit-col"><span class="title inline-edit-cc-license">%s</span>', $license_label );

	cac_cc_license_link( array( 'use_logo' => true, 'logo_size' => 'compact' ) );

	$link_label = esc_html__( 'Edit', 'cac-creative-commons' );
	$a11y_label = esc_html__( 'Edit license', 'cac-creative-commons' );

	cac_cc_button_chooser( array(
		'link_label' => sprintf( '<span aria-hidden="true">%1$s</span> <span class="screen-reader-text">%2$s</span>', $link_label, $a11y_label ),
		'link_class' => 'edit-license hide-if-no-js',
		'link_wrapper_element' => '',
		'output_nonce' => false,
		'parent' => '#bulk-edit'
	) );

	echo '</div></fieldset>';
}

// Save handler for our bulk edit functionality.
add_action( 'save_post', function( $post_id, $post, $update ) {
	// Ensure this is a bulk edit.
	if ( ! isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	// Piggyback off bulk posts nonce.
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-posts' ) ) {
		return;
	}

	// Sanity checks.
	if ( ! $update || empty( $_REQUEST['cac-cc-license'] ) ) {
		return;
	}

	// Update license.
	if ( cac_cc_validate_license( $_REQUEST['cac-cc-license'] ) ) {
		update_post_meta( $post_id, 'cac_cc_license', $_REQUEST['cac-cc-license'] );
	}
	
}, 10, 3 );
