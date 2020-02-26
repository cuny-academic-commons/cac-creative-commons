<?php
/**
 * Widget injection code.
 *
 * @package cac-creative-commons
 */

add_action( 'after_switch_theme', '_cac_cc_widget_inject', 100 );

/**
 * Injects widget after switching theme.
 *
 * We only inject if our widget is not currently added to an existing sidebar
 * and if the widget sidebar's ID contains either 'footer' or 'side'.
 *
 * @since 0.1.0
 */
function _cac_cc_widget_inject() {
	// Don't do this again.
	remove_action( 'switch_theme', '_cac_cc_widget_init', 100 );

	$sidebars_widgets = get_option( 'sidebars_widgets', [] );

	// Sanity check: Check to see if our widget is already added to *any* sidebar.
	$all_widgets = new RecursiveIteratorIterator( new RecursiveArrayIterator( $sidebars_widgets ) );
	foreach ( $all_widgets as $i => $widget ) {
		// Do not pass 'Go', do not collect $200!
		if ( 0 === strpos( $widget, 'cac_creative_commons_widget' ) ) {
			return;
		}
	}

	// Find suitable sidebar; we want first footer or first sidebar.
	$footer = $sidebar = '';
	foreach ( $sidebars_widgets as $_sidebar => $widgets ) {
		if ( 'wp_inactive_widgets' === $_sidebar || 'array_version' === $_sidebar ) {
			continue;
		}

		if ( false !== strpos( $_sidebar, 'footer' ) ) {
			$footer = $_sidebar;
			break;
		}

		/*
		 * Playing it safe... if we wanted to open this up, we'd select the first
		 * available sidebar, but some themes use the sidebar as a menu...
		 */
		if ( '' === $sidebar && false !== strpos( $_sidebar, 'side' ) ) {
			$sidebar = $_sidebar;
		}
	}

	// We prefer footer.
	if ( '' !== $footer ) {
		$sidebar = $footer;
	}

	// Safe safe!
	if ( '' === $sidebar ) {
		return;
	}

	// Injection time!
	$sidebars_widgets[ $sidebar ][] = 'cac_creative_commons_widget-2';
	update_option( 'sidebars_widgets', $sidebars_widgets );

	// Add default widget values.
	$default = [
		2 => [
			'text' => __( 'Except where otherwise noted, content on this site is licensed under a Creative Commons %license_link% license.', 'cac-creative-commons' )
		],
		'_multiwidget' => 1
	];
	update_option( 'widget_cac_creative_commons_widget', $default );
}