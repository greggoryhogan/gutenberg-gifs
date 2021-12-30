<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'plugins_api', 'gg_plugin_info' , 21, 3 ); //my-reddit uses 20 and causes bug when using same priority
add_filter( 'site_transient_update_plugins','gg_plugin_update'  );
add_action( 'upgrader_process_complete',  'gg_plugin_purge' , 11, 2 ); //my-reddit uses 10 and causes bug when using same priority
            

function gg_plugin_request(){
	$remote = get_transient( FRGMNT_GG_CACHE_KEY );
	//$remote = false; //for testing
	if( false === $remote) {
		$remote = wp_remote_get(FRGMNT_JSON_URL . '/updates/gutenberg-gifs');
		if(
			is_wp_error( $remote )
			|| 200 !== wp_remote_retrieve_response_code( $remote )
			|| empty( wp_remote_retrieve_body( $remote ) )
		) {
			return false;
		}
		$remote = json_decode( wp_remote_retrieve_body( $remote ) );
		set_transient( FRGMNT_GG_CACHE_KEY, $remote, HOUR_IN_SECONDS );
	}
	
	return $remote;
}

function gg_plugin_info( $res, $action, $args ) {
	//print_r( $action );
	//print_r( $args );
	// do nothing if you're not getting plugin information right now
	if( 'plugin_information' !== $action ) {
		return false;
	}
	// do nothing if it is not our plugin
	if( FRGMNT_GG_PLUGIN_FILENAME !== $args->slug ) {
		return false;
	}
	// get updates
	$remote = gg_plugin_request();
	if( ! $remote ) {
		return false;
	}
	$res = new stdClass();
	$res->name = $remote->name;
	$res->slug = $remote->slug;
	$res->version = $remote->version;
	$res->tested = $remote->tested;
	$res->requires = $remote->requires;
	$res->author = $remote->author;
	$res->author_profile = $remote->author_profile;
	$res->download_link = $remote->download_url;
	$res->trunk = $remote->download_url;
	$res->requires_php = $remote->requires_php;
	$res->last_updated = $remote->last_updated;
	$res->sections = array(
		'description' => $remote->sections->description,
		'installation' => $remote->sections->installation,
		'changelog' => $remote->sections->changelog
	);
	if( ! empty( $remote->banners ) ) {
		$res->banners = array(
			'low' => $remote->banners->low,
			'high' => $remote->banners->high
		);
	}
	return $res;
}

function gg_plugin_update( $transient ) {
	if ( empty($transient->checked ) ) {
		//return $transient;
	}
	$remote = gg_plugin_request();
	if(
		$remote
		&& version_compare( FRGMNT_GG_PLUGIN_VERSION, $remote->version, '<' )
		&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<' )
		&& version_compare( $remote->requires_php, PHP_VERSION, '<' )
	) {
		$res = new stdClass();
		$res->slug = FRGMNT_GG_PLUGIN_FILENAME;
		$res->plugin = FRGMNT_GG_PLUGIN_FILE; // reddit-profiler/reddit-profiler.php
		$res->new_version = $remote->version;
		$res->tested = $remote->tested;
		$res->package = $remote->download_url;
		$transient->response[ $res->plugin ] = $res;
	}
	return $transient;
}

function gg_plugin_purge(){
	if ('update' === $options['action'] && 'plugin' === $options[ 'type' ]) {
		// just clean the cache when new plugin version is installed
		delete_transient( FRGMNT_GG_CACHE_KEY );
	}
}

?>