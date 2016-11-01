<?php

$mock_data = array();
$mock_data["options"] = array();
$mock_data["script_queue"] = array();
$mock_data["users"] = array();
$mock_data["admin"] = false;

//to be able read private properties
function invokeProperty(&$object, $propName)
	{	
		$reflection = new \ReflectionClass(get_class($object));
		$prop = $reflection->getProperty($propName);
		$prop->setAccessible(true);
	
		return $prop->getValue($object);
	}

//to be able calling private methodes
function invokeMethod(&$object, $methodName, array $parameters = array())
	{	
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);
	
		return $method->invokeArgs($object, $parameters);
	}

class WP_Widget //mocks the Wordpress Widget Class
{
	function __construct($some,$widget_name) {}	
}
	
function __($text,$textdomain) {
	return $text;
}
function init_mocked_option_container(){
	global $mock_data;
	$mock_data["options"]["eDemoSSO_serviceURI"]["value"]='';
	$mock_data["options"]["eDemoSSO_sslverify"]["value"]=false;
	$mock_data["options"]["eDemoSSO_appkey"]["value"]='';
	$mock_data["options"]["eDemoSSO_secret"]["value"]='';
	$mock_data["options"]["eDemoSSO_appname"]["value"]='';
	$mock_data["options"]["eDemoSSO_allowBind"]["value"]=false;
	$mock_data["options"]["eDemoSSO_allowRegister"]["value"]=false;
	$mock_data["options"]["eDemoSSO_allowLogin"]["value"]=false;
	$mock_data["options"]["eDemoSSO_default_role"]["value"]='';
	$mock_data["options"]["eDemoSSO_hide_adminbar"]["value"]=false;
	$mock_data["options"]["eDemoSSO_needed_assurances"]["value"]='';
	$mock_data["options"]["eDemoSSO_callback_uri"]["value"]='';
}

function add_option($name, $value, $arg3, $arg4) {
	global $mock_data;
	$mock_data["options"][$name] = array("value" => $value);
}

function update_option($name, $value) {
	global $mock_data;
	$mock_data["options"][$name]["value"]=$value;
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

function plugin_dir_path() {
	return getcwd()."/eDemo-SSOauth/";
}

function get_user_meta($userid, $metaid, $default_return) {
	global $mock_data;
	return $mock_data["users"][$userid][$metaid];
}

function set_admin($value) {
	global $mock_data;
	$mock_data["admin"] = $value;
}

function is_admin() {
	global $mock_data;
	return $mock_data["admin"];
}
function register_widget($name) {
	global $mock_data;
	$mock_data["widgets"] = $name;
}

function get_site_url($arg1,$arg2, $arg3) {
	return 'site_url';
}

function add_action($name, $arg1, $arg3) {
	print("add_action ".$name.",".$arg1.",".$arg3);
}

function add_shortcode($name, $args) {
}

function add_filter($name, $args) {
}

function register_activation_hook($name, $args) {
}

function register_deactivation_hook($name, $args) {
}


