<?php
/**
 * Admin core functions.
 *
 * @package cac-creative-commons
 */

/**
 * Registers assets used for the plugin.
 *
 * @since 0.1.0
 */
function cac_cc_register_scripts() {
	wp_register_script( 'cac-surveyjs', 'https://unpkg.com/survey-jquery@1.9.33/survey.jquery.js', array( 'jquery' ), '1.9.33' );
	wp_register_style( 'cac-surveyjs', 'https://unpkg.com/survey-jquery@1.9.33/survey.min.css', array( 'thickbox' ), '1.9.33' );

	//wp_register_script( 'cac-showdown', 'https://cdnjs.cloudflare.com/ajax/libs/showdown/1.6.4/showdown.min.js' );

	wp_register_script( 'cac-creative-commons', CAC_CC_URL . 'assets/js.js', array( 'cac-surveyjs', 'media-upload' ), '20190812' );

	wp_localize_script( 'cac-creative-commons', 'CAC_Creative_Commons', array(
		'licenses' => cac_cc_get_licenses(),
		'chooser' => array(
			// 'derivative' key
			'yes' => array(
				// 'commerical' key
				'yes' => 'by',
				'no'  => 'by-nc'
			),
			'share' => array(
				'yes' => 'by-sa',
				'no'  => 'by-nc-sa'
			),
			'no' => array(
				'yes' => 'by-nd',
				'no'  => 'by-nc-nd'
			)
		),
		'questions' => array(
			'publicDomain'     => esc_html__( 'Do you want to use a public domain license?', 'cac-creative-commons' ),
			'publicDomainDesc' => esc_html__( 'If you want to share your work with no conditions, meaning anyone can use it however they like, select Yes. If you want to share your work, but with some restrictions, select No.', 'cac-creative-commons' ),
			'derivative'       => esc_html__( 'Allow adaptations of your work to be shared?', 'cac-creative-commons' ),
			'derivativeDesc'   => esc_html__( "If you select No, only unaltered copies of the work can be used by the licensee. If you select the Share Alike option, you permit others to distribute derivative works only under the same license or a compatible one.", 'cac-creative-commons' ),
			'commercial'       => esc_html__( 'Allow commercial uses of your work?', 'cac-creative-commons' ),
			'commercialDesc'   => esc_html__( 'If you select No, licensees may not use the work for commercial purposes unless they get your permission to do so.', 'cac-creative-commons' )
		),
		'answers' => array(
			'yes'   => esc_html__( 'Yes', 'cac-creative-commons' ),
			'no'    => esc_html__( 'No', 'cac-creative-commons' ),
			'share' => esc_html__( 'Yes, as long as others share alike', 'cac-creative-commons' )
		),
		'sizes' => array(
			'normal'  => '88x31',
			'compact' => '80x15'
		),
		'text' => array(
			'intro'          => esc_html__( 'Creative Commons licenses help you share your work while keeping your copyright. Other people can copy and distribute your work provided they give you credit -- and only on the conditions you specify here. This page helps you choose those conditions.', 'cac-creative-commons' ),
			'intro2'         => esc_html__( 'To license a work, you must be its copyright holder or have express authorization from its copyright holder to do so.', 'cac-creative-commons' ),
			'selected'       => esc_html__( 'Selected License', 'cac-creative-commons' ),
			'freeCulture'    => esc_html__( 'This is a Free Culture License', 'cac-creative-commons' ),
			'notFreeCulture' => esc_html__( 'This is not a Free Culture License', 'cac-creative-commons' )
		),
		'versions' => array(
			'current' => cac_cc_get_license_version(),
			'zero'    => cac_cc_get_zero_license_version()
		),
		'defaults' => array(
			'license'    => cac_cc_get_default_license(),
			'logoUrl'    => cac_cc_get_license_logo( array( 'url_only' => true, 'size' => 'compact' ) ),
			'licenseUrl' => cac_cc_get_license_link( array( 'url_only' => true ) )
		)
	) );

	wp_enqueue_script( 'cac-creative-commons' );
	wp_enqueue_style( 'cac-surveyjs' );
}
add_action( 'admin_enqueue_scripts', 'cac_cc_register_scripts' );

/**
 * Outputs the license chooser markup.
 *
 * @since 0.1.0
 */
function cac_cc_button_chooser( $args = array() ) {
	echo cac_cc_get_button_chooser( $args );
}

/**
 * Returns the license chooser markup.
 *
 * @since 0.1.0
 *
 * @param array $args {
 *     Array of arguments.
 *
 *     @type string $modal_title           Thickbox modal title.
 *     @type string $link_label            Label for the link.
 *     @type string $link_class            CSS classes for the link. Default: 'button button-secondary'.
 *     @type string $link_wrapper_element  Element to wrap the link with. Default: 'p'.
 *     @type bool   $output_nonce          Whether to output the default nonce. Default: true
 *     @type string $parent                jQuery parent selector where the current license logo resides.
 * }
 * @return string
 */
function cac_cc_get_button_chooser( $args = array() ) {
	// Our script must be enqueued before this function returns anything.
	if ( ! wp_script_is( 'cac-creative-commons', 'enqueued' ) ) {
		return '';
	}

	static $instance = false;

	$args = array_merge( array(
		'modal_title' => __( 'Choose a License', 'cac-creative-commons' ),
		'link_label'  => __( 'Choose license', 'cac-creative-commons' ),
		'link_class'  => 'button button-secondary',
		'link_wrapper_element' => 'p',
		'output_nonce' => true,
		'parent' => ''
	), $args );

	$license_val = esc_attr( cac_cc_get_default_license() );

	$args['link_class']  = strip_tags( $args['link_class'] );
	$args['modal_title'] = esc_html( $args['modal_title'] );

	if ( empty( $args['link_wrapper_element'] ) ) {
		$wrapper_start = $wrapper_end = '';
	} else {
		$wrapper_start = sprintf( '<%s>', strip_tags( $args['link_wrapper_element'] ) );
		$wrapper_end   = sprintf( '</%s>', strip_tags( $args['link_wrapper_element'] ) );
	}

	$survey = '';

	$chooser = sprintf(
		'%1$s<a class="thickbox %2$s" title="%3$s" href="#TB_inline?width=600&height=550&inlineId=cac-cc-survey">%4$s</a>%5$s',
		$wrapper_start, $args['link_class'], $args['modal_title'], $args['link_label'], $wrapper_end
	);

	if ( false === $instance ) {
		$instance      = true;
		$parent_marker = '' !== $args['parent'] ? ' data-parent="' . strip_tags( $args['parent'] ). '"' : '';

		$survey = sprintf( '<input type="hidden" id="cac-cc-license" name="cac-cc-license" value="%1$s" /><div id="cac-cc-survey" style="display:none"%2$s></div>', $license_val, $parent_marker );
	}

	if ( true === $args['output_nonce'] ) {
		return wp_nonce_field( 'cac-cc-license', 'cac-cc-nonce', false, false ) . $chooser . $survey;
	} else {
		return $chooser . $survey;
	}
}
