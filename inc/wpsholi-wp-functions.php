<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


add_action('wp_ajax_generate_wpsholi_url_via_ajax', 'generate_wpsholi_url_via_ajax');
function generate_wpsholi_url_via_ajax()
{
    if (!isset($_POST['post_id']) && !is_numeric($_POST['post_id'])) {
        echo json_encode(['status' => false, 'sholi_link_html' => 'null']); exit();
    }

    $error = false;
    $post_id = (int)$_POST['post_id'];

    $permalink = get_permalink($post_id);

    $sholi_link = wpsholi_generate_shorten_url($permalink);


    if (!$sholi_link) {
        $error = true;
    }


    if ($sholi_link) {
        save_wpsholi_short_url($sholi_link, $post_id);
    }


    $sholi_link_html = '<div class="wpsholi_tooltip wpsholi copy_sholi">
            <p><span class="copy_sholi_link">' . esc_url($sholi_link) . '</span>  <span class="wpsholi_tooltiptext">Click to Copy</span></p>
          </div>';


    if (!$error) {

        echo json_encode(['status' => true, 'sholi_link_html' => $sholi_link_html]);
    } else {
        echo json_encode(['status' => false, 'sholi_link_html' => 'null']);
    }

    die();
}


/**
 * Filter the core shortlink with Our generated Sholi Link
 */

add_filter('pre_get_shortlink', 'change_core_short_link_with_wpsholi_link', 10, 5);
function change_core_short_link_with_wpsholi_link($status, $id, $context, $allow_slugs)
{
    $sholi_url = get_wpsholi_short_url($id);
    if ($sholi_url) {
        return $sholi_url;
    }

    return $status;
}