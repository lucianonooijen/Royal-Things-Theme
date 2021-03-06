<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'generate_copyright_customize_register' ) ) :
/**
 * Add our copyright options to the Customizer
 */
add_action( 'customize_register', 'generate_copyright_customize_register' );
function generate_copyright_customize_register( $wp_customize ) 
{
	// Get our custom control
	require_once GP_LIBRARY_DIRECTORY . 'customizer-helpers.php';
	
	// Register our custom control
	if ( method_exists( $wp_customize,'register_control_type' ) ) {
		$wp_customize->register_control_type( 'GeneratePress_Copyright_Customize_Control' );
	}
	
	// Add our section
	$wp_customize->add_section( 'generate_copyright' , array(
		'title' => __( 'Copyright', 'generate-copyright' ),
		'priority' => 60
	) );
	
	// Copyright
	$wp_customize->add_setting(
		'generate_copyright',
		array(
			'default' => '',
			'type' => 'theme_mod',
			'sanitize_callback' => 'wp_kses_post',
			'transport' => 'postMessage'
		)
	);

	$wp_customize->add_control( 
		new GeneratePress_Copyright_Customize_Control( 
		$wp_customize, 
		'generate_copyright', 
		array(
			'label'      => '',
			'section'    => 'generate_copyright',
			'settings'   => 'generate_copyright'
		) ) 
	);
	
	// Initiate selective refresh
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'generate_copyright', array(
			'selector' => '.copyright-bar',
			'settings' => array( 'generate_copyright' ),
			'render_callback' => 'generate_copyright_selective_refresh',
		) );
	}
}
endif;

if ( ! function_exists( 'generate_copyright_selective_refresh' ) ) :
/**
 * Return our copyright on selective refresh
 */
function generate_copyright_selective_refresh()
{
	$options = array(
		'%current_year%',
		'%copy%'
	);
	$replace = array(
		date('Y'),
		'&copy;'
	);
		
	$new_copyright = get_theme_mod( 'generate_copyright' );
	$new_copyright = str_replace( $options, $replace, get_theme_mod( 'generate_copyright' ) );
	
	return do_shortcode( $new_copyright );
}
endif;

if ( ! function_exists( 'generate_copyright_remove_default' ) ) :
/**
 * Remove the default copyright
 * @since 0.1
 * @deprecated GP 1.3.42
 */
add_action('wp','generate_copyright_remove_default');
function generate_copyright_remove_default()
{
	// As of 1.3.42, we no longer need to do this
	// We use a nice little filter instead
	if ( ! function_exists( 'generate_add_login_attribution' ) )
		return;
	
	if ( get_theme_mod( 'generate_copyright' ) && '' !== get_theme_mod( 'generate_copyright' ) ) :
		remove_action( 'generate_credits', 'generate_add_footer_info' );
		remove_action( 'generate_copyright_line','generate_add_login_attribution' );
	endif;
}
endif;

if ( ! function_exists( 'generate_copyright_add_custom' ) ) :
/**
 * Add the custom copyright
 * @since 0.1
 * @deprecated GP 1.3.42
 */
add_action('generate_credits','generate_copyright_add_custom');
function generate_copyright_add_custom()
{
	// As of 1.3.42, we no longer need to do this
	// We use a nice little filter instead
	if ( ! function_exists( 'generate_add_login_attribution' ) )
		return;
	
	$options = array(
		'%current_year%',
		'%copy%'
	);
	$replace = array(
		date('Y'),
		'&copy;'
	);
		
	$new_copyright = get_theme_mod( 'generate_copyright' );
	$new_copyright = str_replace( $options, $replace, get_theme_mod( 'generate_copyright' ) );
		
	if ( get_theme_mod( 'generate_copyright' ) && '' !== get_theme_mod( 'generate_copyright' ) ) :
		echo do_shortcode( $new_copyright );
	endif;

}
endif;

if ( ! function_exists( 'generate_apply_custom_copyright' ) ) :
/**
 * Add the custom copyright
 * @since 1.2.92
 */
add_filter('generate_copyright','generate_apply_custom_copyright');
function generate_apply_custom_copyright( $copyright )
{
	// This will only work if GP >= 1.3.42 and the below function doesn't exist
	if ( function_exists( 'generate_add_login_attribution' ) )
		return;
	
	$options = array(
		'%current_year%',
		'%copy%'
	);
	$replace = array(
		date('Y'),
		'&copy;'
	);
		
	$new_copyright = get_theme_mod( 'generate_copyright' );
	$new_copyright = str_replace( $options, $replace, get_theme_mod( 'generate_copyright' ) );
		
	if ( get_theme_mod( 'generate_copyright' ) && '' !== get_theme_mod( 'generate_copyright' ) ) :
		return do_shortcode( $new_copyright );
	endif;
	
	return $copyright;

}
endif;

if ( ! function_exists( 'generate_copyright_customizer_live_preview' ) ) :
/**
 * Add our live preview
 */
add_action( 'customize_preview_init', 'generate_copyright_customizer_live_preview' );
function generate_copyright_customizer_live_preview()
{
	wp_enqueue_script( 
		  'generate-copyright-customizer',
		  plugin_dir_url( __FILE__ ) . 'js/customizer.js',
		  array( 'jquery','customize-preview' ),
		  GENERATE_COPYRIGHT_VERSION,
		  true
	);
}
endif;

if ( ! function_exists( 'generate_update_copyright' ) ) :
/**
 * Our copyright use to have it's own setting
 * If we have the old setting, move it into our theme_mod
 */
add_action( 'admin_init', 'generate_update_copyright' );
function generate_update_copyright() 
{
	// If we already have a custom logo, bail.
	if ( get_theme_mod( 'generate_copyright' ) )
		return;
	
	// Get the old logo value.
	$old_value = get_option( 'gen_custom_copyright' );
	
	// If there's no old value, bail.
	if ( empty( $old_value ) )
		return;
	
	// Now let's update the new logo setting with our ID.
	set_theme_mod( 'generate_copyright', $old_value );
	
	// Got our custom logo? Time to delete the old value
	if ( get_theme_mod( 'generate_copyright' ) ) :
		delete_option( 'gen_custom_copyright' );
	endif;
}
endif;