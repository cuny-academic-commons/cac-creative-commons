<?php
/**
 * Core functions.
 *
 * @package cac-creative-commons
 */

/**
 * Returns the default Creative Commons license.
 *
 * @since 0.1.0
 *
 * @return string Defaults to 'by', which is 'Attribution 4.0 International'.
 */
function cac_cc_get_default_license() {
	/**
	 * Filters the default license, if one isn't saved into the DB.
	 *
	 * @since 0.1.0
	 *
	 * @param string $retval Short name for license. Defaults to 'by', which is the Attribution
	 *                       International license.
	 * @return string
	 */
	$default = apply_filters( 'cac_cc_default', 'by' );

	return get_option( 'cac_cc_default', $default );
}

/**
 * Returns an array of licenses that the Creative Commons offers.
 *
 * @since 0.1.0
 *
 * @return array
 */
function cac_cc_get_licenses() {
	return array(
		'by'       => sprintf( esc_html__( 'Attribution %s International', 'cac-creative-commons' ), cac_cc_get_license_version() ),
		'by-nd'    => sprintf( esc_html__( 'Attribution-NoDerivatives %s International', 'cac-creative-commons' ), cac_cc_get_license_version() ),
		'by-sa'    => sprintf( esc_html__( 'Attribution-ShareAlike %s International', 'cac-creative-commons' ), cac_cc_get_license_version() ),
		'by-nc'    => sprintf( esc_html__( 'Attribution-NonCommercial %s International', 'cac-creative-commons' ), cac_cc_get_license_version() ),
		'by-nc-nd' => sprintf( esc_html__( 'Attribution-NonCommercial-NoDerivatives %s International', 'cac-creative-commons' ), cac_cc_get_license_version() ),
		'by-nc-sa' => sprintf( esc_html__( 'Attribution-NonCommercial-ShareAlike %s International', 'cac-creative-commons' ), cac_cc_get_license_version() ),
		'zero'     => sprintf( esc_html__( 'CC0 %s Universal Public Domain Dedication', 'cac-creative-commons' ), cac_cc_get_zero_license_version() )
	);
}

/**
 * Check to see if a license exists or not.
 *
 * @since 0.1.0
 *
 * @return bool
 */
function cac_cc_validate_license( $license ) {
	$licenses = cac_cc_get_licenses();
	if ( ! isset( $licenses[$license] ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Returns the current license version for Creative Commons licenses.
 *
 * This covers all licenses except the CC0 license, which uses a different
 * version number.
 *
 * @since 0.1.0
 */
function cac_cc_get_license_version() {
	return '4.0';
}

/**
 * Returns the current license version for the CC0 license.
 *
 * @since 0.1.0
 */
function cac_cc_get_zero_license_version() {
	return '1.0';
}

/**
 * Returns the label used for a Creative Commons license.
 *
 * @since 0.1.0
 *
 * @param  string $license Short name for the license. eg. 'by'.
 * @return string
 */
function cac_cc_get_license_label( $license = '' ) {
	$licenses = cac_cc_get_licenses();
	if ( isset( $licenses[ $license ] ) ) {
		return $licenses[ $license ];
	}

	return '';
}

/**
 * Outputs the link for a Creative Commons license.
 *
 * @since 0.1.0
 *
 * @param array $args See {@link cac_cc_get_license_link()}
 */
function cac_cc_license_link( $args = array() ) {
	echo cac_cc_get_license_link( $args );
}

/**
 * Returns the link for a Creative Commons license.
 *
 * @since 0.1.0
 *
 * @param array $args {
 *     Array of arguments.
 *
 *     @type string $license   License to get link for. Use short name for the CC license. eg. 'by'.
 *     @type string $target    Link target. Default: '_blank'.
 *     @type string $label     Link label. Defaults to license name. eg. 'Attribution 4.0 International'.
 *     @type bool   $use_logo  Whether to use the license logo for the link label. Defaults to false.
 *                             If true, this overrides $label.
 *     @type string $logo_size Logo size for $use_logo. Either 'normal' or 'compact'. Default: 'normal'.
 *     @type bool   $url_only  Whether to return the license URL only. Default: false.
 * }
 * @return string
 */
function cac_cc_get_license_link( $args = array() ) {
	if ( empty( $args['license'] ) ) {
		$args['license'] = cac_cc_get_default_license();
	}

	$args = array_merge( array(
		'url_only'  => false,
		'target'    => '_blank',
		'label'     => cac_cc_get_license_label( $args['license'] ),
		'use_logo'  => false,
		'logo_size' => 'normal',
	), $args );

	if ( 'zero' === $args['license'] ) {
		$version = cac_cc_get_zero_license_version();
	} else {
		$version = cac_cc_get_license_version();
	}

	$url = sprintf( 'https://creativecommons.org/licenses/%1$s/%2$s/', esc_attr( $args['license'] ), $version );
	if ( true === $args['url_only'] ) {
		return $url;
	}

	if( true === $args['use_logo'] ) {
		$args['label'] = cac_cc_get_license_logo( array(
			'license' => $args['license'],
			'size'    => $args['logo_size']
		) );
	}

	if ( ! empty( $args['target'] ) ) {
		$target = ' target="' . esc_attr( $args['target'] ). '"';
	} else {
		$target = '';
	}

	return sprintf( '<a rel="license" data-logo="%4$d" href="%1$s"%2$s>%3$s</a>',
		$url,
		$target,
		$args['label'],
		(int) $args['use_logo']
	);
}

/**
 * Returns the default size to use for the Creative Commons license logo.
 *
 * @since 0.1.0
 *
 * @return string
 */
function cac_cc_get_license_logo_size() {
	return get_option( 'cac_cc_logo_size', 'normal' );
}

/**
 * Outputs the logo for a Creative Commons license.
 *
 * @since 0.1.0
 *
 * @param array $args See {@link cac_cc_get_license_logo()} for arguments.
 */
function cac_cc_license_logo( $args = array() ) {
	echo cac_cc_get_license_logo( $args );
}

/**
 * Returns the logo for a Creative Commons license.
 *
 * @since 0.1.0
 *
 * @param array $args {
 *     Array of arguments.
 *
 *     @type string $license   License to get link for. Use short name for the CC license. eg. 'by'.
 *     @type string $alt       Alt text for the logo.
 *     @type string $logo_size Logo size for $use_logo. Either 'normal' or 'compact'. Default: 'normal'.
 *     @type bool   $url_only  Whether to return the license logo URL only. Default: false.
 * }
 * @return string
 */
function cac_cc_get_license_logo( $args = array() ) {
	$args = array_merge( array(
		'license'  => cac_cc_get_default_license(),
		'alt'      => '',
		'size'     => cac_cc_get_license_logo_size(),
		'url_only' => false
	), $args );

	// Use license label if 'alt' is empty.
	if ( empty( $args['alt'] ) ) {
		$args['alt'] = cac_cc_get_license_label( $args['license'] );
	}

	/**
	 * Filters the license logo arguments.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	$args = apply_filters( 'cac_cc_get_license_logo_args', $args );

	if ( 'compact' === $args['size'] ) {
		$size = '80x15';
	} else {
		$size = '88x31';
	}

	if ( 'zero' === $args['license'] ) {
		$version = cac_cc_get_zero_license_version();
	} else {
		$version = cac_cc_get_license_version();
	}

	$url = sprintf( 'https://licensebuttons.net/l/%1$s/%2$s/%3$s.png', esc_attr( $args['license'] ), $version, $size );
	if ( true === $args['url_only'] ) {
		return $url;
	}

	return sprintf( '<img src="%1$s" data-license="%2$s" data-size="%3$s" alt="%4$s" style="border-width:0" />', $url, $args['license'], $args['size'], esc_attr( $args['alt'] ) );
}