<?php
/*
Plugin Name: eDemo SSO authentication
Plugin URI: https://github.com/edemo/wp_oauth_plugin/wiki
Description: Allows you connect to the Edemo SSO server, and autenticate the users, who acting on your site
Version: 0.1
Author: Claymanus
Author URI: https://github.com/Claymanus
License: GPL2

eDemo-SSOauth is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
eDemo-SSOauth is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with eDemo-SSOauth. If not, see {License URI}.

License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: eDemo-SSOauth
Domain Path: /languages
*/

// abort if this file is called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class_edemo-ssoauth_activator.php';
	eDemo_SSOauth_Activator::activate();
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class_edemo-ssoauth_activator.php';
	eDemo_SSOauth_Activator::deactivate();
}
register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class_edemo-ssoauth.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_eDemo_SSOauth() {
	$plugin = new eDemo_SSOauth();
	$plugin->run();
}
run_eDemo_SSOauth();




?>
