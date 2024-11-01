<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function get_wpsholi_short_url($post_id = null){

	if(!$post_id){
		global $post;
		$post_id = isset($post->ID) ? $post->ID : 0;
	}

    if(!$post_id){
        return false;
    }

	$wpsholi_url = get_post_meta($post_id, '_wpsholi_shorturl', true);

	return $wpsholi_url ? $wpsholi_url : false;

}


function save_wpsholi_short_url($shorten_url , $post_id = null){
	if(!$post_id){
		global $post;
		$post_id = isset($post->ID) ? $post->ID : 0;
	}

    if(!$post_id){
        return false;
    }

	update_post_meta($post_id, '_wpsholi_shorturl', $shorten_url);
    do_action('wpsholi_shorturl_updated' , $shorten_url);
}


function wpsholi_remove_http($url) {
   $disallowed = array('http://', 'https://');
   foreach($disallowed as $d) {
      if(strpos($url, $d) === 0) {
         return str_replace($d, '', $url);
      }
   }
   return $url;
}


function get_wpsholi_headers(){

	$wpsholi_settings = new WPSholiURLSettings();
    $access_token     =  $wpsholi_settings->get_wpsholi_access_token();

	$headers = array (
        "Authorization" => "Bearer ".$access_token ,
        "Content-Type"  => "application/json"
    );

    return $headers;
}

if (!function_exists('wpsholi_write_log')) {

    function wpsholi_write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

}


function wpsholi_get_template( $template_name, $template_path = '', $default_path = '' ) {

    $located = wpsholi_locate_template( $template_name, $template_path, $default_path );

    if ( ! file_exists( $located ) ) {
        _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $located ) ), WPSHOLI_PLUGIN_VERSION );
        return;
    }
    include( $located );
}


function wpsholi_locate_template( $template_name,  $default_path = '') {

    if ( ! $default_path ) {
        $default_path = untrailingslashit(WPSHOLI_PLUGIN_PATH). '/templates/';
    }

    $template = $default_path . $template_name;

    return $template;
}

