<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Activation
 * Add a check for our plugin before redirecting
 */
register_activation_hook( str_replace( 'includes/init.php', 'plugin.php', __FILE__ ) , function () {
    add_option( 'woolook_do_activation_redirect', true );
});

/**
 * Redirect to the Faceproof admin page on single plugin activation
 */
add_action( 'admin_init', function () {
    
    if ( get_option( 'woolook_do_activation_redirect', false ) ) {

        delete_option( 'woolook_do_activation_redirect' );
    
        if( ! isset( $_GET['activate-multi'] ) ) {

            wp_redirect( "admin.php?page=woolook_admin_page" );
        
        }
    }

});

/**
 * Admin alert when woocommerce is not installed
 */
add_action( 'admin_notices', function () {

    if( class_exists('Woocommerce') ) return;

    ?>
        <div class="notice notice-error">
            <p><?php _e( 'Woolook needs Woocommerce to be installed.', 'woolook' ); ?></p>
        </div>
    <?php
});

/**
 * Add Image Sizes
 */
add_action( 'after_setup_theme', function(){

	add_image_size( 'woolook_size_layout_1', 800, 800, true );

});

/**
 * Enqueue frontend + backend.
 */
add_action( 'enqueue_block_assets', function () {
	global $woolook_font_list;

	$font_selected = get_option('woolook_font', WOOLOOK_OPTION_FONT );

	if( $font_selected !== 'default' ){
		$font_data = $woolook_font_list[ $font_selected ];

		// Fonts.
		wp_enqueue_style(
			'woolook-google-fonts', 
			$font_data['url'], 
			array(),
			WOOLOOK_VERSION
		);
	
	}
	
	// only front
	if( is_admin() ) return;

	// Scripts.
	wp_enqueue_script(
		'woolook-frontend',
		WOOLOOK_URL . '/dist/front.js', 
		array('jquery'),
		WOOLOOK_VERSION,
		true
	);

	wp_localize_script( 'woolook-frontend', 'woolook', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('woolook-addtocart'),
	));

	// Styles.
	wp_enqueue_style(
		'woolook-style', 
		WOOLOOK_URL . '/dist/blocks.style.build.css',
		array(),
		WOOLOOK_VERSION
	);

});

/**
 * Enqueue Gutenberg block assets for backend editor.
 */
add_action( 'enqueue_block_editor_assets', function () { 
	global $woolook_font_list;

	$block_dependencies = array(
		'wp-compose',
		'wp-blocks',
		'wp-i18n',
		'wp-element',
		'wp-editor',
		'wp-api-fetch',
		'wp-components',
		'wp-data',
		'wp-url',
		'lodash',
	);

	// Scripts.
	wp_enqueue_script(
		'woolook-blocks',
		WOOLOOK_URL .'/dist/blocks.build.js', 
		$block_dependencies, 
		WOOLOOK_VERSION,
		true 
	);

	$max_width = get_option('woolook_max_width', WOOLOOK_OPTION_MAX_WIDTH );
	$font_selected = get_option('woolook_font', WOOLOOK_OPTION_FONT );
	$font_data = $woolook_font_list[ $font_selected ];

	wp_localize_script( 'woolook-blocks', 'woolook_admin', array(
		'max_width' => get_option('woolook_max_width', WOOLOOK_OPTION_MAX_WIDTH ),
		'font_css' => $font_data['css'],
	));

	$font_selected = get_option('woolook_font', WOOLOOK_OPTION_FONT );

	// Styles.
	wp_enqueue_style(
		'woolook-cgb-block-editor-css',
		WOOLOOK_URL . '/dist/blocks.editor.build.css',
		array( 'wp-edit-blocks' ),
		WOOLOOK_VERSION
	);

});

/**
 * Register A Block Categories
 */
add_filter( 'block_categories', function ( $categories, $post ) {
	return array_merge(
        $categories,
        array(
            array(
                'slug' => 'woolook',
                'title' => 'Woolook',
            ),
        )
    );
}, 10, 2);

// include
include __DIR__ . '/array-font-list.php';
include __DIR__ . '/class-api.php';
include __DIR__ . '/class-ajax.php';
include __DIR__ . '/class-admin.php';
include __DIR__ . '/class-products.php';
include __DIR__ . '/class-category-list.php';
include __DIR__ . '/blocks/layout-1/index.php';

new Woolook\Api();
new Woolook\Ajax();
new Woolook\Admin();
