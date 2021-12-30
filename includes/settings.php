<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
Functions related to overall plugin functionality
*/

/*
 *
 * Plugin Options for Admin Pages
 * 
 */
function gg_settings() {
    //Tenor API Key
    register_setting( 'gg_settings', 'gg_tenor_api_key' );
    //Content Filter
    register_setting( 'gg_settings', 'gg_content_filter' );
    //Gifs Per Page
    register_setting( 'gg_settings', 'gg_gifs_per_page' );
    
}
add_action( 'admin_init', 'gg_settings' );

/*
 *
 * Create admin page
 * 
 */
function gg_admin_settings_page() {
    add_submenu_page( 'options-general.php', 'Gutenberg Gifs', 'Gutenberg Gifs', 'administrator', 'gutenberg-gifs', 'gg_settings_content' );
}
add_action( 'admin_menu', 'gg_admin_settings_page' );    
 
/*
 *
 * Add link to settings page from plugins page
 * 
 */
add_filter('plugin_action_links', 'gg_add_plugin_settings_link', 10, 2);
function gg_add_plugin_settings_link( $plugin_actions, $plugin_file ) {
	$added_actions = array();
    if ( 'gutenberg-gifs.php' == basename($plugin_file) ) {
        $added_actions['cl_settings'] = sprintf( __( '<a href="%s" title="Settings">Settings</a>', 'gg' ), esc_url( menu_page_url( 'gutenberg-gifs', false )  ) );
    }
    return array_merge( $added_actions, $plugin_actions );
}

/*
 *
 * Admin page formatting
 * 
 */
function gg_settings_content() { ?>
    <style type="text/css">
        .gg-options {
            display: flex;
        }
        .gg-options label,
        .gg-options select {
            display: block;
        }
        .gg-options label {
            margin-right: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
    <div class="wrap">
        <h1 class="wp-heading-inline">Gutenberg Gifs</h1>
        <div class="metabox-holder wrap" id="dashboard-widgets">
            <form method="post" action="options.php">
                <?php settings_fields( 'gg_settings' ); ?>
                
                <section id="defaults" class="babel-tab-panel">
                    <div class="babel-settings-panel">
                        <!--API Key-->
                        <div class="postbox">
                            <div class="postbox-header"><h2 class="hndle">Tenor API Key</h2></div>
                            <div class="inside">
                                <div class="input-text-wrap">
                                    <p>Gutenberg Gifs uses <a href="https://tenor.com/" title="Open Tenor in a new window" target="_blank">Tenor</a> to deliver you these awesome gifs. Tenor requires a (free) API key in order to use their service. If you already have an API key, enter it below.</p>
                                    <input type="text" name="gg_tenor_api_key" value="<?php echo esc_html(get_option('gg_tenor_api_key')); ?>" />
                                    <p><strong>Don&rsquo;t have an API key?</strong>
                                    <ol>
                                        <li>Visit <a href="https://tenor.com/developer/keyregistration" target="_blank" title="Tenor API Key Registration">https://tenor.com/developer/keyregistration</a>
                                        <li>If you don't have an account, it will prompt you to log in using your Google account.</li>
                                        <li>Enter app name and description. These can be whatever you&rsquo;d you like, such as your website name.</li>
                                        <li>Copy the generated key and paste it above.</li>
                                        <li>You&rsquo;re done! Start adding gifs to your website!</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <!--Search Settings-->
                        <div class="postbox">
                            <div class="postbox-header"><h2 class="hndle">Tenor Search Results</h2></div>
                            <div class="inside">
                                <div class="input-text-wrap">
                                    <p>If you want to change the way results are returned when searching, update the options below.</p>
                                        <div class="gg-options">
                                        <?php 
                                        $gg_content_filter = get_option('gg_content_filter');
                                        $options = array ('high','medium','low','off'); ?>
                                        <div>
                                            <label for="gg_content_filter">Content Filter:</label>
                                            <select name="gg_content_filter" id="gg_content_filter" autocomplete="off">
                                            <?php foreach($options as $option) { ?>
                                                <option value="<?php echo esc_html($option); ?>"
                                                    <?php if($option == $gg_content_filter) { ?> selected <?php } ?>
                                                ><?php echo ucwords(esc_html($option)); ?></option>
                                            <?php } ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="gg_gifs_per_page">Gifs Per Page:</label>
                                            <?php 
                                            $gg_gifs_per_page = get_option('gg_gifs_per_page'); 
                                            $options = array (5,10,20,30,40,50); ?>
                                            <select name="gg_gifs_per_page" id="gg_gifs_per_page" autocomplete="off">
                                            <?php foreach($options as $option) { ?>
                                                <option value="<?php echo esc_html($option); ?>"
                                                    <?php if($option == $gg_gifs_per_page) { ?>selected<?php } ?>
                                                ><?php echo esc_html($option); ?></option>
                                            <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <ul>
                                        <li><strong>Content Filtering Options:</strong></li>
                                        <li>High - G</li>
                                        <li>Medium - G and PG</li>
                                        <li>Low - G, PG, and PG-13</li>
                                        <li>Off - G, PG, PG-13, and R (no nudity)</li>
                                    </ul>
                                </div>
                            </div>
                            
                            
                        </div>
                    </div>
                    <div class="clear"></div>
                </section>
                    
                <?php submit_button('Save Options'); ?>
            </form>    
        </div>
    </div>
<?php
}

/*
 *
 * Admin notices
 * 
 */ 
function gg_admin_notice() {
    global $current_user;
	$tenor_api_key = get_option('gg_tenor_api_key');
    if(!$tenor_api_key) {
        $ignore = get_transient('gg_admin_notice_api_key_ignore');
        if ($ignore === false) {
            $screen = get_current_screen();
            if($screen) {
                $show_on = array('dashboard','plugins');
                if(in_array($screen->base,$show_on)) {
                    echo '<div class="updated notice"><p>'. __('Gif Search &amp; Embed requires a (free) Tenor API key to get started.') .' <a href="'.menu_page_url( 'gutenberg-gifs', false ).'">Enter API Key</a> | <a href="?gg-ignore-notice">Dismiss</a></p></div>';
                }
            }
        }
    }
}
add_action('admin_notices', 'gg_admin_notice');

/*
 *
 * Add transient to ignore the notice for 7 days
 * 
 */ 
function gg_admin_notice_ignore() {
	if (isset($_GET['gg-ignore-notice'])) {	
        set_transient('gg_admin_notice_api_key_ignore', 7 * DAY_IN_SECONDS);	
	}
}
add_action('admin_init', 'gg_admin_notice_ignore');
?>