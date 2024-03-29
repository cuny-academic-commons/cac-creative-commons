<?php
/**
 * Frontend code.
 *
 * @package cac-creative-commons
 */

/**
 * Should the Creative Commons license display or not?
 *
 * Defaults to true if singular page and post type supports 'cc-license'.
 *
 * @return bool
 */
function cac_cc_frontend_should_display() {
	/**
	 * Filter to display the Creative Commons license after the post.
	 *
	 * @since 0.1.0
	 *
	 * @param bool $retval Defaults to true when on a single page and if the post type supports it.
	 *                     Otherwise, false.
	 */
	$show = apply_filters( 'cac_cc_display_license_after_post', is_singular() && post_type_supports( get_post_type(), 'cc-license' ) );

	return $show;
}

/**
 * Add our custom inline CSS style.
 */
function _cac_cc_add_inline_style() {
	if ( ! cac_cc_frontend_should_display() ) {
		return;
	}

	// Hack to use inline CSS without an external stylesheet.
	wp_register_style( 'cac-creative-commons', false );
	wp_enqueue_style( 'cac-creative-commons' );

	// Our inline CSS.
	wp_add_inline_style( 'cac-creative-commons', '.entry-meta-creative-commons {clear:both; margin-top:1em}' );
}
add_action( 'wp_enqueue_scripts', '_cac_cc_add_inline_style', 999 );

/**
 * Appends license to the post content.
 *
 * @since 0.1.0
 *
 * @param  string $retval Post content.
 * @return string
 */
function _cac_cc_append_to_the_post( $retval = '') {
	// Don't show on BuddyPress pages.
	if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {
		return $retval;
	}

	/*
	 * Do not show if:
	 *   1. Our site license widget is already showing AND if the post license is
	 *      the same as the site license.
	 *   2. Our site license widget is already showing AND a post license hasn't
	 *      been selected.
	 *   3. Our site license widget is not showing AND a post license hasn't been
	 *      been selected.
	 */
	$post_license = get_post_meta( get_post()->ID, 'cac_cc_license', true );
	if ( is_active_widget( false, false, 'cac_creative_commons_widget' ) ) {
		if ( get_option( 'cac_cc_default' ) === $post_license || empty( $post_license ) ) {
			return $retval;
		}
	} elseif ( empty( $post_license ) ) {
		return $retval;
	}

	if ( ! cac_cc_frontend_should_display() ) {
		return $retval;
	}

	add_filter( 'option_cac_cc_default', '_cac_cc_fetch_post_license' );
	add_filter( 'default_option_cac_cc_default', '_cac_cc_fetch_post_license' );

	$template = cac_cc_locate_template( 'post-footer.php', true, false, true );
	if ( ! empty( $template ) ) {
		$retval .= $template;
	}

	remove_filter( 'option_cac_cc_default', '_cac_cc_fetch_post_license' );
	remove_filter( 'default_option_cac_cc_default', '_cac_cc_fetch_post_license' );

	return $retval;
}
add_filter( 'the_content', '_cac_cc_append_to_the_post', 999 );

/**
 * Fetch the post license, if available.
 *
 * Filters 'option_cac_cc_default'.
 *
 * @since 0.1.0
 *
 * @param  string $retval Default license.
 * @return string
 */
function _cac_cc_fetch_post_license( $retval ) {
	$p = get_post();
	if ( empty( $p ) ) {
		return $retval;
	}

	$post_license = get_post_meta( $p->ID, 'cac_cc_license', true );
	if ( ! empty( $post_license ) ) {
		return $post_license;
	}

	return $retval;
}

/**
 * Locate templates used by our plugin.
 *
 * Essentially a wrapper function for {@link locate_template()} but supports
 * our custom template directory and a new parameter, $buffer.
 *
 * @see locate_template() for parameter documentation
 */
function cac_cc_locate_template( $template_names, $load = false, $require_once = true, $buffer = false ) {
	if ( ! is_array( $template_names ) ) {
		$template_names = (array) $template_names;
	}

	// Prefix our directory to each template.
	$template_names = array_map( function( $retval ) {
		return 'creative-commons/' . $retval;
	}, $template_names );

	// Check WP first
	$located = locate_template( $template_names, false );

	// Fallback to bundled template on failure
	if ( empty( $located ) ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}

			if ( file_exists( CAC_CC_DIR . '/templates/' . $template_name ) ) {
				$located = CAC_CC_DIR . '/templates/' . $template_name;
				break;
			}
		}
	}

	if ( true === (bool) $load && '' !== $located ) {
		if ( $buffer ) {
			ob_start();
		}

		load_template( $located, $require_once );

		// Return template contents now when object buffering is enabled.
		if ( $buffer ) {
			$retval = trim( ob_get_clean() );
			return $retval;
		}

	} else {
		return $located;
	}
}

/**
 * Load a template part.
 *
 * Essentially a duplicate of {@link get_template_part()} but supports
 * our custom template directory.
 *
 * @see get_template_part() for parameter documentation
 */
function cac_cc_get_template_part( $slug, $name = null ) {
	$templates = array();
	$name = (string) $name;
	if ( '' !== $name ) {
		$templates[] = "{$slug}-{$name}.php";
	}

	$templates[] = "{$slug}.php";

	cac_cc_locate_template( $templates, true, false );
}