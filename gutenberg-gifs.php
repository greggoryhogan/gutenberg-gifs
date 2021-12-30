<?php
/*
Plugin Name:  Gutenberg Gifs
Plugin URI:	  https://fragmentwebworks.com/product/gutenberg-gifs/
Description:  Search and embed gifs from Tenor to Gutenberg enabled posts, pages and widgets
Version:	  1.0.4
Author:		  Fragment Web Works
Author URI:   https://fragmentwebworks.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  gg
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//definitions for plugin
if( !function_exists('get_plugin_data') ){
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugin_data = get_plugin_data( __FILE__ );
define( 'GG_BLOCK_DIR', plugin_dir_url(__FILE__) );
define( 'GG_PLUGIN_DIR', dirname(__FILE__).'/' );
define( 'FRGMNT_GG_CACHE_KEY', 'frgmnt_gg_plgn_chk');
define( 'FRGMNT_GG_PLUGIN_VERSION', $plugin_data['Version'] ); //url of this dir
define( 'FRGMNT_GG_PLUGIN_DIR', dirname(__FILE__).'/' ); //url of this dir
define( 'FRGMNT_GG_PLUGIN_FILE', plugin_basename( __FILE__ ) ); //url of this dir
define( 'FRGMNT_GG_PLUGIN_FILENAME', plugin_basename( __DIR__ ) ); //url of this dir
define( 'FRGMNT_GG_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
if(!defined('FRGMNT_JSON_URL')) {
	define( 'FRGMNT_JSON_URL', 'https://fragmentwebworks.com/wp-json/frgmnt/v1'); //wp-json endpoint we use for all plugins
}
/*
 *
 * Require files for plugin functionality
 * 
 */ 
//settings files
require_once( GG_PLUGIN_DIR . '/includes/settings.php' );
//rest endpoint
require_once( GG_PLUGIN_DIR . '/includes/rest.php' );
//updater
require_once( GG_PLUGIN_DIR . '/includes/update.php' );
/*
 *
 * Create default options on activation
 * 
 */ 
register_activation_hook( __FILE__, 'gg_activate' );
function gg_activate() {
    //Tenor API Key
    add_option( 'gg_tenor_api_key', '');
    //Content Filter
    add_option( 'gg_content_filter', 'low');
    //Gifs Per Page
    add_option( 'gg_gifs_per_page', '20');
}

/*
 *
 * Delete plugin options on uninstall
 * 
 */
register_uninstall_hook( __FILE__, 'gg_uninstall' );
function gg_uninstall() {
    delete_option( 'gg_tenor_api_key');
    delete_option( 'gg_content_filter');
    delete_option( 'gg_gifs_per_page');
}

/*
 *
 * Register styles for block
 * 
 */
function gg_block_assets() {
    // include css style which will be used on both block preview
    // inside Gutemberg block Editor and on the frontend
    wp_enqueue_style( 'gg-style-css', GG_BLOCK_DIR . 'src/style.css', array(), '1.0.0' );
} 
add_action( 'enqueue_block_assets', 'gg_block_assets' );

/*
 *
 * Register js for block
 *
 */
function gg_editor_assets() {
    // Scripts
    wp_enqueue_script(
        'gg-block-js',
        GG_BLOCK_DIR . 'build/index.js',
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
        '1.0.0',
        true
    );
    //add localized settings
    wp_localize_script( 'gg-block-js', 'gg_settings',
        array( 
            'tenor_api_key' => get_option('gg_tenor_api_key'),
            'gg_settings_page' => menu_page_url( 'gutenberg-gifs', false ),
        )
    );
}
add_action( 'enqueue_block_editor_assets', 'gg_editor_assets' );

/*
 *
 * Add required theme support for plugin to run
 * 
 */ 
add_action( 'after_setup_theme', 'gg_add_theme_support' );
function gg_add_theme_support() {
    //add aligment support to theme
    add_theme_support('align-wide');
    //allow images to be resizes when embedded
    add_theme_support( 'responsive-embeds' );
}