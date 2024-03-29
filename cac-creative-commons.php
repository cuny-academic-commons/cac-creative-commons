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

/**
 * Register license support for certain post types.
 *
 * Currently includes 'post' and 'page'.
 *
 * @since 0.1.0
 */
function cac_cc_register_post_type_license_support() {
	add_post_type_support( 'post', 'cc-license' );
	add_post_type_support( 'page', 'cc-license' );
}
add_action( 'init', 'cac_cc_register_post_type_license_support' );

// Widget.
function cac_cc_widgets_init() {
	require __DIR__ . '/includes/class-cac-creative-commons-widget.php';
	register_widget( 'CAC_Creative_Commons_Widget' );
}
add_action( 'widgets_init', 'cac_cc_widgets_init' );

// Frontend post integration.
add_action( 'get_header', function() {
	require_once __DIR__ . '/includes/functions.php';
	require_once __DIR__ . '/includes/frontend.php';
} );

// BuddyPress integration.
add_action( 'bp_init', function() {
	require __DIR__ . '/includes/buddypress.php';
} );

// Inject widget after switching theme.
add_action( 'after_switch_theme', function() {
	require_once __DIR__ . '/includes/widget-inject.php';
}, 0 );