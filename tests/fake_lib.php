<?php

$mock_data = array();
$mock_data["options"] = array();
$mock_data["script_queue"] = array();
$mock_data["users"] = array();

function add_option($name, $value, $arg3, $arg4) {
	global $mock_data;
	$mock_data["options"][$name] = array("value" => $value);
}

function get_option($name) {
	global $mock_data;
	return $mock_data["options"][$name]["value"];
}

function wp_enqueue_script($name,$uri) {
	global $mock_data;
	$mock_data["script_queue"][$name] = $uri;
}

function plugins_url($uri,$base) {
	return "http://plugins.url/".$base."$uri";
}

function get_user_meta($userid, $metaid, $default_return) {
	global $mock_data;
	return $mock_data["users"][$userid][$metaid];
}

function register_widget($name) {
	global $mock_data;
	$mock_data["widgets"] = $name;
}

function get_site_url($arg1,$arg2, $arg3) {
}

function add_action($name, $args) {
}

function add_shortcode($name, $args) {
}

function add_filter($name, $args) {
}

function register_activation_hook($name, $args) {
}

function register_deactivation_hook($name, $args) {
}

class WP_widget
{
}

