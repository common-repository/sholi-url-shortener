<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function wpsholi_add_meta_box_to_post_types()
{

    $wpsholi_settings = new WPSholiURLSettings();
    $active_post_types = $wpsholi_settings->get_wpsholi_active_post_status();

    foreach ($active_post_types as $post_type) {
        add_meta_box(
            'wpsholi-sholi-url-metabox',
            __('Sholi Short URL', 'wpsholi'),
            'wpsholi_add_meta_box_content',
            $post_type,
            'side',
            'default'
        );
    }
}

add_action('add_meta_boxes', 'wpsholi_add_meta_box_to_post_types');


function wpsholi_add_meta_box_content($post)
{

    $post_id = $post->ID;

    if ('publish' != get_post_status($post_id)) {

        echo '<h4>Publish to Generate Sholi URL<h4>';

        return;
    }

    $wpsholi_settings = new WPSholiURLSettings();
    $access_token = $wpsholi_settings->get_wpsholi_access_token();

    if (!$access_token) {

        $plugin_url = admin_url('tools.php?page=wpsholi');
        echo '<a  class="wpsholi_settings" href="' . esc_url($plugin_url) . '">Get Started</a>';

    } else {

        echo '<div class="wpsholi_metabox_container wpsholi-mt-5">';

        $sholi_url = get_wpsholi_short_url($post_id);
        if ($sholi_url) {
            ?>
            <div class="wpsholi_tooltip wpsholi copy_sholi">
                <p><span class="copy_sholi_link wpsholi-meta-bg-link"><?php echo esc_url($sholi_url); ?></span> <span
                            class="wpsholi_tooltiptext">Click to Copy</span></p>
            </div>
            <?php

            $wpsholi_socal_share_status = $wpsholi_settings->get_wpsholi_socal_share_status();

            if ($wpsholi_socal_share_status) {
                wpsholi_get_template('share.php');
            }

            ?>
            <?php

        } else {
            ?>
            <div class="wpsholi_tooltip">
                <p><?php echo esc_url($sholi_url); ?></p>
                <button class="wpsholi generate_sholi" data-post_id="<?php echo esc_attr($post_id); ?>">
                    <span class="wpsholi_tooltiptext">Click to Generate</span>Generate URL
                </button>
            </div>


            <?php
        }

        echo "</div>";

    }


}


function add_wpsholi_shortlink_frontend($wp_admin_bar)
{

    $wpsholi_settings = new WPSholiURLSettings();
    $active_post_types = $wpsholi_settings->get_wpsholi_active_post_status();
    $default_roles = ['administrator'];
    $allowed_roles = apply_filters('wpsholi_script_for_allowed_roles', $default_roles);


    foreach ($allowed_roles as $role) {
        if (current_user_can($role)) {
            foreach ($active_post_types as $post_type) {

                if (is_singular($post_type)) {

                    global $post;

                    $post_id = $post->ID;
                    $sholi_url = get_wpsholi_short_url($post_id);


                    if ($sholi_url) {

                        $args = array(
                            'id' => 'wpsholi_link' . $post_id,
                            'title' => 'Click to Copy Sholi Link',
                            'href' => $sholi_url,
                            'meta' => array(
                                'class' => 'wpsholi-copy-class',
                                'title' => 'Click to Copy Sholi Link',

                            )
                        );

                        $wp_admin_bar->add_node($args);
                    }
                }
            }
        }
    }
}

add_action('admin_bar_menu', 'add_wpsholi_shortlink_frontend', 999);