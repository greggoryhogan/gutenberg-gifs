<?php
/*
Plugin Name:  Gutenberg Gifs
Plugin URI:	  https://fragmentwebworks.com/product/gutenberg-gifs/
Description:  Search and embed gifs from Tenor to Gutenberg enabled posts, pages and widgets
Version:	  1.0.0
Author:		  Fragment Web Works
Author URI:   https://fragmentwebworks.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  gsae
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//definitions for plugin
define( 'GSAE_BLOCK_DIR', plugin_dir_url(__FILE__) );
define( 'GSAE_PLUGIN_DIR', dirname(__FILE__).'/' );

/*
 *
 * Require files for plugin functionality
 * 
 */ 
//settings files
require_once( GSAE_PLUGIN_DIR . '/includes/settings.php' );
//rest endpoint
require_once( GSAE_PLUGIN_DIR . '/includes/rest.php' );

/*
 *
 * Create default options on activation
 * 
 */ 
register_activation_hook( __FILE__, 'gsae_activate' );
function gsae_activate() {
    //Tenor API Key
    add_option( 'gsae_tenor_api_key', '');
    //Content Filter
    add_option( 'gsae_content_filter', 'low');
    //Gifs Per Page
    add_option( 'gsae_gifs_per_page', '20');
}

/*
 *
 * Delete plugin options on uninstall
 * 
 */
register_uninstall_hook( __FILE__, 'gsae_uninstall' );
function gsae_uninstall() {
    delete_option( 'gsae_tenor_api_key');
    delete_option( 'gsae_content_filter');
    delete_option( 'gsae_gifs_per_page');
}

/*
 *
 * Register styles for block
 * 
 */
function gsae_block_assets() {
    // include css style which will be used on both block preview
    // inside Gutemberg block Editor and on the frontend
    wp_enqueue_style( 'gsae-style-css', GSAE_BLOCK_DIR . 'src/style.css', array(), '1.0.0' );
} 
add_action( 'enqueue_block_assets', 'gsae_block_assets' );

/*
 *
 * Register js for block
 *
 */
function gsae_editor_assets() {
    // Scripts
    wp_enqueue_script(
        'gsae-block-js',
        GSAE_BLOCK_DIR . 'build/index.js',
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
        '1.0.0',
        true
    );
    //add localized settings
    wp_localize_script( 'gsae-block-js', 'gsae_settings',
        array( 
            'tenor_api_key' => get_option('gsae_tenor_api_key'),
            'gsae_settings_page' => menu_page_url( 'gif-search-and-embed', false ),
        )
    );
}
add_action( 'enqueue_block_editor_assets', 'gsae_editor_assets' );