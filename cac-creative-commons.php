<?php
/*
Plugin Name: Creative Commons License
Description: Display your Creative Commons license across your site, post content and BuddyPress groups.
Version: 0.1-alpha
Author: CUNY Academic Commons
Plugin URI: https://dev.commons.gc.cuny.edu
License: GPLv2 or later
*/

// Some pertinent defines.
define( 'CAC_CC_DIR', __DIR__ );
define( 'CAC_CC_URL', plugins_url( basename( __DIR__ ) ) . '/' );

// Admin code.
add_action( 'admin_menu', function() {
	require __DIR__ . '/includes/admin.php';
} );

// Widget.
function cac_cc_widgets_init() {
	require __DIR__ . '/includes/class-cac-creative-commons-widget.php';
	register_widget( 'CAC_Creative_Commons_Widget' );
}
add_action( 'widgets_init', 'cac_cc_widgets_init' );

// Frontend post integration.
add_action( 'loop_start', function() {
	// Only do this if the header has run.
	if ( ! did_action( 'get_header' ) ) {
		return;
	}

	require_once __DIR__ . '/includes/functions.php';
	require_once __DIR__ . '/includes/frontend.php';
} );

// BuddyPress integration.
add_action( 'bp_init', function() {
	// Group creation and settings.
	if ( is_user_logged_in() &&
		( bp_is_group_admin_page() && bp_is_action_variable( 'edit-details', 0 ) ||
			bp_is_group_create() && bp_is_action_variable( 'group-settings', 1 ) )
	) {
		require __DIR__ . '/includes/frontend-buddypress-groups-admin.php';
	}

	// Group frontend.
	if ( bp_is_group() ) {
		require __DIR__ . '/includes/frontend-buddypress-groups.php';
	}

	// Blog create.
	if ( is_user_logged_in() && bp_is_create_blog() ) {
		require __DIR__ . '/includes/frontend-buddypress-blog-create.php';
	}
} );