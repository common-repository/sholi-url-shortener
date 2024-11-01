<?php

/*
Plugin Name: Sholi URL Shortener
Plugin URI: https://sholi.co/integrations/wordpress
Description: Sholi URL Shortener uses the functionality of Sholi API to generate Sholi short link without leaving your WordPress site.
Version: 1.0
Author: Sholi
Author URI: https://sholi.co/
License: GPLv2 or later
Text Domain: wpsholi
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


define( 'WPSHOLI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHOLI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSHOLI_PLUGIN_VERSION', '1.0' );
define( 'WPSHOLI_API_URL', 'https://sholi.co/api/v1' );
define( 'WPSHOLI_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSHOLI_SETTINGS_URL', admin_url( 'tools.php?page=wpsholi' ) );


/**
 * Load Admin Assets
 */

require_once 'inc/wpsholi-assets.php';


/**
 * Load Util Functions
 */

require_once 'inc/wpsholi-util.php';


/**
 * Load Settings file
 */


require_once 'inc/wpsholi-settings.php';



/**
 * Load Sholi Integration
 */


require_once 'inc/wpsholi-integration.php';



/**
 * Load WordPress related hooks
 */


require_once 'inc/wpsholi-wp-functions.php';

/**
 * Meta Box 
 */
require_once 'inc/wpsholi-metabox.php';