<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WPSholiURLSettings
{

    private $sholi_url_options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'wpsholi_url_add_plugin_page'));
        add_action('admin_init', array($this, 'wpsholi_url_page_init'));
    }


    public function wpsholi_get_sholi_guid_request($access_token)
    {
        $response = false;
        try {
            $headers = array(
                "Authorization" => "Bearer " . $access_token,
                "Content-Type" => "application/json"
            );

            $http_response = wp_remote_get(WPSHOLI_API_URL . '/links/create', array(
                    'timeout' => 0,
                    'headers' => $headers
                )
            );

            if (!is_wp_error($http_response)) {

                $response = json_decode($http_response['body']);
            } else {

                $error = $http_response->get_error_message();
                $pluginlog = plugin_dir_path(__FILE__) . 'error.log';
                $message = $error . PHP_EOL;
                error_log($message, 3, $pluginlog);
            }

        } catch (Exception $e) {

            $pluginlog = plugin_dir_path(__FILE__) . 'debug.log';
            $message = 'Unable to get Sholi' . PHP_EOL;
            error_log($message, 3, $pluginlog);
        }

        return $response;

    }


    /**
     * Add Plugin Page
     */

    public function wpsholi_url_add_plugin_page()
    {
        add_management_page(
            'Sholi URL Shortener Settings', // page_title
            'Sholi URL Shortener', // menu_title
            'manage_options', // capability
            'wpsholi', // menu_slug
            array($this, 'wpsholi_url_create_admin_page') // function
        );
    }

    /**
     * Add Plugin Page Form
     */

    public function wpsholi_url_create_admin_page()
    {
        $this->sholi_url_options = get_option('wpsholi_url_option_name'); ?>

        <div class="wrap">
            <h2>Sholi URL Shortener Settings</h2>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('wpsholi_url_option_group');
                do_settings_sections('wpsholi-url-admin');
                submit_button();
                ?>
            </form>
        </div>
    <?php }


    /**
     * Add Input fields to Settings Page
     */

    public function wpsholi_url_page_init()
    {
        register_setting(
            'wpsholi_url_option_group', // option_group
            'wpsholi_url_option_name', // option_name
            array($this, 'wpsholi_url_sanitize') // sanitize_callback
        );

        add_settings_section(
            'wpsholi_url_setting_section', // id
            'Settings', // title
            array($this, 'wpsholi_url_section_info'), // callback
            'wpsholi-url-admin' // page
        );

        add_settings_field(
            'access_token', // id
            'Access Token', // title
            array($this, 'access_token_callback'), // callback
            'wpsholi-url-admin', // page
            'wpsholi_url_setting_section' // section
        );


        add_settings_field(
            'sholi_domain', // id
            'Domain (Optional)', // title
            array($this, 'sholi_domain_callback'), // callback
            'wpsholi-url-admin', // page
            'wpsholi_url_setting_section' // section
        );


        add_settings_field(
            'wpsholi_socal_share', // id
            'Enable Social Share Button', // title
            array($this, 'add_wpsholi_social_share_button'), // callback
            'wpsholi-url-admin', // page
            'wpsholi_url_setting_section' // section
        );

        add_settings_field(
            'wpsholi_custom_post', // id
            'Post Types', // title
            array($this, 'add_wpsholi_custom_posttype_settings'), // callback
            'wpsholi-url-admin', // page
            'wpsholi_url_setting_section' // section
        );

    }

    /**
     * Validate Fields
     *
     * @param <type> $input The input
     *
     * @return     array   ( description_of_the_return_value )
     */

    public function wpsholi_url_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['access_token'])) {
            $sanitary_values['access_token'] = sanitize_text_field($input['access_token']);
        }

        if (isset($input['sholi_domain'])) {
            $sanitary_values['sholi_domain'] = sanitize_text_field($input['sholi_domain']);
        }


        if (isset($input['wpsholi_socal_share'])) {
            $sanitary_values['wpsholi_socal_share'] = sanitize_text_field($input['wpsholi_socal_share']);
        }


        if (isset($input['wpsholi_custom_post'])) {
            $sanitary_values['wpsholi_custom_post'] = $input['wpsholi_custom_post'];
        }


        return $sanitary_values;
    }

    public function wpsholi_url_section_info()
    {

    }

    public function access_token_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="wpsholi_url_option_name[access_token]" id="access_token" value="%s">',
            isset($this->sholi_url_options['access_token']) ? esc_attr($this->sholi_url_options['access_token']) : ''
        );
        echo '<p> <small>Tip: </small>Copy the Access Token for Sholi\'s Dashboard</p>';
    }


    public function sholi_domain_callback()
    {
        printf(
            '<input class="regular-text" type="text" placeholder="Default: sholi.co" name="wpsholi_url_option_name[sholi_domain]" id="sholi_domain" value="%s">',
            isset($this->sholi_url_options['sholi_domain']) ? esc_url($this->sholi_url_options['sholi_domain']) : ''
        );
        echo '<p><small>Leave blank if you are in Free Plan</small></p>';
    }

    public function add_wpsholi_social_share_button()
    {

        $wpsholi_social_share = '';

        if (isset($this->sholi_url_options['wpsholi_socal_share'])) {
            $wpsholi_social_share = $this->sholi_url_options['wpsholi_socal_share'] == "enable" ? "checked" : '';
        }

        printf('<label><input name="wpsholi_url_option_name[wpsholi_socal_share]"  id="wpsholi_socal_share" type="checkbox" value="enable"  %s> Enable </label>', $wpsholi_social_share);
        echo '<p><small>If you enable this you can share the link from your post list/edit screen.</small></p>';
    }


    public function add_wpsholi_custom_posttype_settings()
    {

        $post_types = get_post_types(array('public' => true));
        $current_post_types = [];


        $output = '<fieldset><legend class="screen-reader-text"><span>Post Types</span></legend>';


        if (isset($this->sholi_url_options['wpsholi_custom_post'])) {
            $current_post_types = $this->sholi_url_options['wpsholi_custom_post'];
        }


        foreach ($post_types as $label) {
            $random = rand();
            $input_label = $label . '_' . $random;
            $output .= '<label for="' . esc_attr($input_label) . '">' . '<input id="' .esc_attr($input_label) . '" type="checkbox" name="wpsholi_url_option_name[wpsholi_custom_post][]" value="' .esc_attr($label) . '" ' . checked(in_array($label, $current_post_types), true, false) . '>' . esc_attr($label) . '</label><br />';
        }

        $allowed_html = array(
            'input' => array(
                'type' => array(),
                'id' => array(),
                'name' => array(),
                'value' => array(),
                'checked' => array(),
            ),
            'fieldset' => array(),
            'legend' => array(),
            'label' => array(),
            'span' =>array(),
            'br' => array()
        );
        echo wp_kses($output, $allowed_html);
    }


    /**
     * Return currently saved sholi access token
     *
     * @return     boolean  The wpsholi access token.
     */

    public function get_wpsholi_access_token()
    {

        $sholi_url_options_from_db = get_option('wpsholi_url_option_name');
        $access_token = isset($sholi_url_options_from_db['access_token']) ? $sholi_url_options_from_db['access_token'] : '';
        return $access_token ? trim($access_token) : false;
    }


    /**
     * Gets the wpsholi domain.
     *
     * @return     boolean  The wpsholi domain.
     */

    public function get_wpsholi_domain()
    {

        $sholi_url_options_from_db = get_option('wpsholi_url_option_name');
        $domain = isset($sholi_url_options_from_db['sholi_domain']) ? $sholi_url_options_from_db['sholi_domain'] : '';
        return $domain ? trim($domain) : "https://sholi.co";
    }

    /**
     * Gets the wpsholi socal share status.
     *
     * @return     bool  The wpsholi socal share status.
     */
    public function get_wpsholi_socal_share_status()
    {

        $sholi_url_options_from_db = get_option('wpsholi_url_option_name');
        $wpsholi_socal_share = isset($sholi_url_options_from_db['wpsholi_socal_share']) ? $sholi_url_options_from_db['wpsholi_socal_share'] : '';
        return $wpsholi_socal_share === "enable" ? true : false;
    }


    public function get_wpsholi_active_post_status()
    {


        $sholi_url_options_from_db = get_option('wpsholi_url_option_name');
        $active_post_types = isset($sholi_url_options_from_db['wpsholi_custom_post']) ? $sholi_url_options_from_db['wpsholi_custom_post'] : ['post'];
        return $active_post_types;

    }


}


$wpsholi_settings = new WPSholiURLSettings();

add_action('plugin_action_links_' . WPSHOLI_BASENAME, 'wpsholi_add_settings_url');

function wpsholi_add_settings_url($links)
{

    $links = array_merge(array(
        '<a href="' . esc_url(admin_url('tools.php?page=wpsholi')) . '">' . __('Settings', 'wpsholi') . '</a>'
    ), $links);

    return $links;

}









