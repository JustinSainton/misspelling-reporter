<?php

/**
 * Plugin Name: Misspelling Reporter
 * Plugin URI: https://github.com/blobaugh/misspelling-reporter
 * Description: Allows users to highlight misspelled text and report to the site/article admins. Inspired by #BeachPress2013
 * Version: 0.6
 * Author: Ben Lobaugh
 * Author URI: http://ben.lobaugh.net
 */
define('MISSR_PLUGIN_DIR', trailingslashit(dirname(__FILE__)));
define('MISSR_PLUGIN_URL', trailingslashit(WP_PLUGIN_URL . "/" . basename(__DIR__)));
define('MISSR_PLUGIN_FILE', MISSR_PLUGIN_DIR . basename(__DIR__) . ".php");


add_action('wp_enqueue_scripts', 'missr_enqueue_scripts');
add_action('wp_ajax_missr_report', 'missr_ajax_report');
add_action('wp_ajax_nopriv_missr_report', 'missr_ajax_report');

function missr_enqueue_scripts() {
    global $post;

    // Front end text selection code
    wp_enqueue_script(
	    'missr_highlighter', MISSR_PLUGIN_URL . '/js/highlighter.js', array('jquery')
    );

    $info = array('post_id' => $post->ID, 'ajaxurl' => admin_url('admin-ajax.php'));
    wp_localize_script('missr_highlighter', 'post', $info);
}

function missr_ajax_report() {

    $args = array(
	'post_type' => 'missr_report',
	'post_title' => esc_attr($_POST['selected'])
    );
    
    $post_id = wp_insert_post( $args );
    
    update_post_meta( $post_id, 'missr_post_id', esc_attr($_POST['post_id']));
    
    
    $post = get_post( $_POST['post_id']);
    
    $subject = "Misspelling Report";
    
    $body = "Post: " . get_permalink($post->ID );
    $body .= "\n\nMisspelling: " . $_POST['selected'];

    // Email site admin
    wp_mail( get_option('admin_email'), $subject, $body );
    
    
    // mail post author
    $user = get_userdata( $post->post_author );
    wp_mail( $user->user_email, $subject, $body );
    
    echo 'Misspelling Reported';
}