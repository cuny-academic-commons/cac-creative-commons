<?php
/**
 * Frontend integration to a BuddyPress group page.
 *
 * Displays a group's license in the group footer.
 *
 * @package cac-creative-commons
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/frontend.php';

add_action( 'bp_after_group_home_content', function() {
	// No need to show the footer license in the admin area.
	if ( bp_is_group_admin_page() ) {
		return;
	}

	// Only show a license if one was set for the group.
	$license = groups_get_groupmeta( bp_get_current_group_id(), 'cac_cc_license' );
	if ( empty( $license ) ) {
		return;
	}

	cac_cc_get_template_part( 'bp-group-footer' );
} );
