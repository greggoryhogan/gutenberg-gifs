<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add rest endpoint for tenor
 * 
 * @return void
 */
add_action('rest_api_init', 'gg_create_endpoints');
function gg_create_endpoints() {
    register_rest_route( 'gg/v1', 'search/(?P<search_term>([a-zA-Z0-9-]|%20)+)/pos/(?P<pos>\d+)',array(
        'methods'  => 'GET',
        'callback' => 'gg_tenor_api_request',
        'permission_callback' => '__return_true'
    ));
};

/**
 * Tenor rest endpoint callback
 * 
 * @return json
 */
function gg_tenor_api_request($request) {
    $apikey = get_option('gg_tenor_api_key');
    $contentfilter = get_option('gg_content_filter'); //values: off | low | medium | high
    $locale = get_locale();
    $media_filter = 'minimal'; //minimal | basic
    $limit = get_option('gg_gifs_per_page'); //default 20
    $search = urldecode($request['search_term']);
    $pos = $request['pos'] * $limit;
    //can also use g.tenor.com but it limits to 200 results
    $url = 'https://api.tenor.com/v1/search?q='.$search.'&key='.$apikey.'&limit='.$limit.'&pos='.$pos.'&locale='.$locale.'&contentfilter='.$contentfilter.'&media_filter='.$media_filter;
    $args = array(
        'headers' => array( "Content-type" => "application/json" )
    );
    $response = wp_remote_get( $url, $args );
    
    if (empty($response)) {
        return new WP_Error( 'empty_response', 'Nothing returned for your search.', array('status' => 404) );
    }

    $body = wp_remote_retrieve_body( $response );
    $result = json_decode( $body );
    $lastpage = 1;
    if ( is_object( $result ) && ! is_wp_error( $result ) ) {
        $gifs = $result->results;
        $next = $result->next;
        
        if($gifs) {
            $options = array();
            $gifcounter = 0;
            foreach($gifs as $gif) {
                ++$gifcounter;
                $option = array();
                $media = $gif->media;
                $url = $media[0]->gif->url;
                $width = $media[0]->gif->dims[0];
                $height = $media[0]->gif->dims[1];
                $preview = $media[0]->tinygif->url;
                
                //set option
                $option['url'] = $url;
                $option['width'] = $width;
                $option['height'] = $height;
                $option['preview'] = $preview;
                $options[] = $option;
            }

            if($gifcounter == $limit) {
                $lastpage = 0;
            }
            
        } else {

        }
    } else {
        $options = $result;
    }
    $return = array(
        'options' => $options,
        'next' => $next,
        'last_page' => $lastpage
    );
    $response = new WP_REST_Response($return);
    $response->set_status(200);

    return $response;
}
?>