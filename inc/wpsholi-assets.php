<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


function wpsholi_load_admin_script() {
	
	wp_enqueue_script( 'wpsholi-js', WPSHOLI_PLUGIN_URL . 'assets/js/wpsholi.js', array( 'jquery' ), WPSHOLI_PLUGIN_VERSION , true );
	wp_enqueue_style( 'wpsholi-css', WPSHOLI_PLUGIN_URL . 'assets/css/wpsholi.css',[], WPSHOLI_PLUGIN_VERSION , 'all' );
	wp_localize_script( 'wpsholi-js', 'wpsholiJS' , ['ajaxurl' => admin_url( 'admin-ajax.php' )]);

}
add_action('admin_enqueue_scripts', 'wpsholi_load_admin_script');


add_action('wp_footer', 'wpsholi_add_click_to_copy_script', PHP_INT_MAX);
function wpsholi_add_click_to_copy_script() {

	$default_roles = ['administrator'];
	$allowed_roles = apply_filters( 'wpsholi_script_for_allowed_roles', $default_roles );

	foreach ($allowed_roles as $role) {
		if( current_user_can( $role ) ){

			?>

			<script>
				(function($) {
					$(".wpsholi-copy-class").on("click",function(t){if(t.preventDefault(),$wpsholi_link_a=$(this).find("a"),$wpsholi_link=$wpsholi_link_a.attr("href"),$wpsholi_link_title=$wpsholi_link_a.attr("title"),$wpsholi_link){var i=$("<textarea />");i.val($wpsholi_link).css({width:"1px",height:"1px"}).appendTo("body"),i.select(),document.execCommand("copy")&&(i.remove(),$wpsholi_link_a.html("Copied: "+$wpsholi_link),setTimeout(function(){$wpsholi_link_a.html($wpsholi_link_title)},2100))}});
				})(jQuery);
			</script>

			<?php
		} 
	}
}