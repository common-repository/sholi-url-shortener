<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * Generate short URL from permalink
 *
 * @param      <type>  $permalink  The permalink
 *
 * @return     <type>  ( description_of_the_return_value )
 */
function wpsholi_generate_shorten_url($permalink){

   if ( ! class_exists( 'WPSholiURLSettings' ) ) {
      return;
    }
     $wpsholi_settings = new WPSholiURLSettings();

     $permalink      =  apply_filters( 'wpsholi_url_before_process', $permalink );
     $access_token   =  $wpsholi_settings->get_wpsholi_access_token();
     $shorten_domain =  $wpsholi_settings->get_wpsholi_domain();


    if(!$shorten_domain){
       $payload = array(
        "external_url"   =>"".$permalink.""
      );
    }else{
      $payload = array(
        "domain"     =>"".$shorten_domain."",
        "external_url"   =>"".$permalink.""
      );
    }


    $json_payload = json_encode($payload);
    
    $headers      = get_wpsholi_headers();

    $api_url = WPSHOLI_API_URL . "/links" ;

    $options = array(
        'method'      => 'POST',
        'headers'     => $headers,
        'body'        => $json_payload
    );

    $response = wp_remote_post( $api_url , $options);

    if ( is_wp_error( $response ) ) {
      wpsholi_write_log($response->get_error_message());
      return false;
    } else {
      $response_array = json_decode($response['body']);
      return $response_array->data ? $response_array->data->generated_url : false;

    }

}






/**
 * Generate and return URL or return false;
 * Will be removed in future update
 *
 * @param      string   $shorten_url  The shorten url
 */

function wpsholi_shorten_url ($permalink) {
  _deprecated_function( 'wpsholi_shorten_url', '1.0', 'wpsholi_generate_shorten_url' );
  return wpsholi_generate_shorten_url($permalink);

}


/**
 * Add Colum for custom post list
 * 
 */

add_action( 'admin_init', function(){


   $wpsholi_settings = new WPSholiURLSettings();
   $active_post_types = $wpsholi_settings->get_wpsholi_active_post_status();


   foreach ($active_post_types as $active_post) {
     
     /**
     * Add Short URL Column in Post List 
     */

      $wpsholi_column_key        = 'manage_'.$active_post.'_posts_columns';
      $wpsholi_column_value_key  = 'manage_'.$active_post.'_posts_custom_column';

      add_filter($wpsholi_column_key, function($columns) {
        return array_merge($columns, ['wpsholi_url' => __('Short URL', 'wpsholi')]);
      });


      /**
       * Display the value of sholi URL
       * If Access token not added or Guid not added column will show settings link
       * If Post Short URL is not generated "Not Generated yet" message will show
       */
       
      add_action($wpsholi_column_value_key, function($column_key, $post_id) {
        if ($column_key == 'wpsholi_url') {

          if( 'publish' != get_post_status($post_id)){
              return;
          }

          $wpsholi_settings = new WPSholiURLSettings();
          $access_token =  $wpsholi_settings->get_wpsholi_access_token();

          if(!$access_token){

            $plugin_url = admin_url( 'tools.php?page=wpsholi');
            echo '<a  class="wpsholi_settings" href="'.esc_url($plugin_url) .'">Get Started</a>';
          }else{

            echo '<div class="wpsholi_column_container">';

            $sholi_url = get_wpsholi_short_url($post_id);
            if ($sholi_url) {
              ?>
                <div class="wpsholi_tooltip wpsholi copy_sholi">
                  <p><span class="copy_sholi_link"><?php echo esc_url($sholi_url); ?></span>  <span class="wpsholi_tooltiptext">Click to Copy</span></p>
                </div>
                <?php 

                $wpsholi_socal_share_status =  $wpsholi_settings->get_wpsholi_socal_share_status();

                if( $wpsholi_socal_share_status){
                  wpsholi_get_template('share.php');
                }

                ?>
              <?php
              
            } else {
              ?>
                <div class="wpsholi_tooltip">
                  <p><?php echo esc_url($sholi_url); ?></p>
                  <button  class="wpsholi generate_sholi" data-post_id="<?php echo esc_attr($post_id);?>">
                    <span class="wpsholi_tooltiptext">Click to Generate</span>
                   Generate URL
                  </button>
                </div>


              <?php
            }

            echo "</div>";

          }


        }
      }, 10, 2);

   }



});




/**
 * Generate and Save Sholi URL in `_wpsholi_shorturl` post meta key
 * `wpsholi_shorturl_updated` hook is available after value is updated with $shorten_url argument
 */

add_action('transition_post_status', 'wpsholi_update_shorturl' , 10 , 3 );
function wpsholi_update_shorturl($new_status, $old_status, $post) {

    if('publish' === $new_status && 'publish' !== $old_status) {
      
      $wpsholi_settings = new WPSholiURLSettings();
      $active_post_types = $wpsholi_settings->get_wpsholi_active_post_status();

      if(in_array($post->post_type, $active_post_types)){

          $post_id     = $post->ID;
          $shorten_url = get_wpsholi_short_url($post_id);

          if( empty( $shorten_url ) && ! wp_is_post_revision( $post_id ) ) {
           
            $permalink   = get_permalink($post_id);
            $shorten_url = wpsholi_generate_shorten_url($permalink);
            
            if($shorten_url){
              save_wpsholi_short_url($shorten_url , $post_id);
            }
            
          }
      }     

    }
}
