<?php
/*
Plugin Name: Ajax Content Filter
Plugin URI: http://www.perceptionsystem.com/plugins.html
Description: Simple Content Filter using Ajax 
Version: 1.0
Author: Perception System
Author URI: http://www.perceptionsystem.com
Contributors: Perception System
*/
?>

<?php

function ACF_activation() {
}
register_activation_hook(__FILE__, 'ACF_activation');

function ACF_deactivation() {
}
register_deactivation_hook(__FILE__, 'ACF_deactivation');

add_action('wp_enqueue_scripts', 'ACF_scripts');
function ACF_scripts() {
	wp_enqueue_script('jquery');
	wp_register_script('ACFjs', plugins_url('js/acf_script.js', __FILE__));
	wp_enqueue_script('ACFjs');
	
	wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));	
}

add_filter('admin_head', 'add_admin_style');
function add_admin_style() {
	if ( is_admin() ) {
		wp_register_style('ACFcss', plugins_url('css/acf_style.css', __FILE__));
		wp_enqueue_style('ACFcss');
	}
}

add_shortcode("ACF", "ajax_filter_content");
function ACF_show() {
	ob_start();
    ajax_filter_content();
    $html = ob_get_clean();
    return $html;
}
function ajax_filter_content() {
	require ("template.php");
}

add_action('init', 'ajax_filter_content_data');
function ajax_filter_content_data() {
	$labels = array(
		'name' => 'ACF Posts',
		'singular_name' => 'ACF Posts',
		'menu_name' => 'ACF Posts',
		'edit_item' => 'Edit ACF Post ',
		'add_new'=> 'Add New ACF Post',
		'add_new_item'=> 'Add New ACF Post',
				
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'description' => 'Simple Content Filter using Ajax',
		'supports' => array('title', 'editor'),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'menu_position' => 25,
		'menu_icon' => plugins_url( 'images/perceptionlogo.png' , __FILE__ )
	);
	register_post_type('acf_post', $args);
}

add_action('wp_head','custom_header',0);
add_action('wp_ajax_user_log_callback', 'custom_header',0);
function custom_header() {
		echo '<script type="text/javascript">';
		echo 'var ajaxurl = \''.admin_url('admin-ajax.php').'\';';
		echo 'var siteurl = \''.get_site_url().'\';';
		echo '</script>';
}

add_action('wp_ajax_my_action', 'my_action_callback');
add_action('wp_ajax_nopriv_my_action', 'my_action_callback');
function my_action_callback() {
	global $wpdb;
	$id = intval($_POST['id']);
	$post = get_post($id);
    echo $post->post_content;

	die();
}

?>
