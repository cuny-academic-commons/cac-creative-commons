<?php
/**
 * BuddyPress integration.
 *
 * @package cac-creative-commons
 */

/**
 * Filter to enable integration into the BuddyPress groups component.
 *
 * @since 0.1.0
 *
 * @param bool $retval Defaults to false.
 */
$enable_groups = apply_filters( 'cac_cc_enable_buddypress_groups', false );

/**
 * Filter to enable integration into BP blog creation process.
 *
 * @since 0.1.0
 *
 * @param bool $retval Defaults to true.
 */
$enable_blog_creation = apply_filters( 'cac_cc_enable_blog_creation', true );

/**
 * Filter to enable integration for BP Groupblog into group creation process.
 *
 * @since 0.1.0
 *
 * @param bool $retval Defaults to true.
 */
$enable_groupblog = apply_filters( 'cac_cc_enable_groupblog', true );

// Groups.
if ( true === $enable_groups && bp_is_groups_component() ) {
	// Group creation and settings.
	if ( is_user_logged_in() &&
		( bp_is_group_admin_page() && bp_is_action_variable( 'edit-details', 0 ) ||
			bp_is_group_create() && bp_is_action_variable( 'group-settings', 1 ) )
	) {
		require __DIR__ . '/frontend-buddypress-groups-admin.php';
	}

	// Group frontend.
	if ( bp_is_group() ) {
		require __DIR__ . '/frontend-buddypress-groups.php';
	}
}

// Blog create (also handles BP Groupblog).
if ( is_user_logged_in() &&
	( ( bp_is_create_blog() && true === $enable_blog_creation ) ||
		( bp_is_group_create() && bp_is_action_variable( 'group-blog', 1 ) && true === $enable_groupblog )
	)
) {
	require __DIR__ . '/frontend-buddypress-blog-create.php';
}