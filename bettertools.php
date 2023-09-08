<?php 
/*
 * Plugin Name:       BetterTools
 * Plugin URI:        https://hurayraiit.github.io/bettertools/
 * Description:       A collection of necessary tools for WordPress users.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Abu Hurayra
 * Author URI:        https://github.com/hurayraiit/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bettertools
 */

if (!defined('ABSPATH')) {
    exit;
}

if ( ! function_exists( 'is_plugin_active' ) ) 
{
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

// Add nodes to the admin toolbar
function bettertools_admin_toolbar($wp_admin_bar)
{
    // Add BetterTools dropdown
    $wp_admin_bar->add_menu(
        array(
            'id'     => 'bettertools_dropdown',
            'title'  => '<span style="background-color: #1bd5d5; color: #000000; font-weight: 600; display: inline-block; height: 32px; margin: 0; padding: 0; font-size: 13px; position: static; width: auto;">BetterTools</span>',
        )
    );

    // Add Classic Editor Activate/Deactivate sub-menu item
    $classic_editor_active = is_plugin_active('classic-editor/classic-editor.php');
    $classic_editor_text = $classic_editor_active ? 'Deactivate Classic Editor' : 'Activate Classic Editor';
    $classic_editor_action = $classic_editor_active ? 'deactivate' : 'activate';

    $wp_admin_bar->add_menu(
        array(
            'id'     => 'bettertools_classic_editor_sub_menu',
            'parent' => 'bettertools_dropdown',
            'title'  => $classic_editor_text,
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'bettertools_toggle_classic_editor(event);'
            )
        )
    );

    // Add Current Theme and Version to BetterTools dropdown
    $current_theme = wp_get_theme();
    $current_theme_text = '<span>Theme:</span> <span style="color: #3cdacd; font-weight: bold;">' . sanitize_text_field($current_theme->get('Name')) . '(' . sanitize_text_field($current_theme->get('Version')) . ')</span>';

    $wp_admin_bar->add_menu(
        array(
            'id'     => 'bettertools_current_theme_sub_menu',
            'parent' => 'bettertools_dropdown',
            'title'  => $current_theme_text,
            'href'   => '#',
        )
    );

    // Add PHP Version to BetterTools dropdown
    $php_version = phpversion();
    $php_version_text = '<span>PHP:</span> <span style="color: #ff6f00; font-weight: bold;">' . $php_version . '</span>';

    $wp_admin_bar->add_menu(
        array(
            'id'     => 'bettertools_php_version_sub_menu',
            'parent' => 'bettertools_dropdown',
            'title'  => $php_version_text,
            'href'   => '#',
        )
    );

    // Enqueue JavaScript and localize data
    wp_enqueue_script('bettertools-js', plugin_dir_url(__FILE__) . 'bettertools.js', array('jquery'), '1.0.0', true);
    wp_localize_script('bettertools-js', 'bettertools_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'classic_editor_action' => $classic_editor_action,
        'classic_editor_nonce' => wp_create_nonce('bettertools_classic_editor_nonce')
    ));
}

add_action('admin_bar_menu', 'bettertools_admin_toolbar', 999);

// Register and enqueue the JavaScript file
function bettertools_enqueue_scripts() {
    wp_enqueue_script('bettertools-js', plugin_dir_url(__FILE__) . 'bettertools.js', array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'bettertools_enqueue_scripts');

// AJAX callback to toggle Classic Editor
function bettertools_toggle_classic_editor_callback() {
    if (!current_user_can('activate_plugins') || !check_ajax_referer('bettertools_classic_editor_nonce', 'security', false)) {
        wp_send_json_error('Invalid request');
    }

    $classic_editor_action = isset($_POST['classic_editor_action']) ? sanitize_text_field($_POST['classic_editor_action']) : '';

    if ($classic_editor_action === 'activate') {
        $result = activate_plugin('classic-editor/classic-editor.php');
    } elseif ($classic_editor_action === 'deactivate') {
        $result = deactivate_plugins('classic-editor/classic-editor.php');
    }

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success();
}

add_action('wp_ajax_bettertools_toggle_classic_editor', 'bettertools_toggle_classic_editor_callback');
add_action('wp_ajax_nopriv_bettertools_toggle_classic_editor', 'bettertools_toggle_classic_editor_callback');
