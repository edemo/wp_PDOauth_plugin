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

function op_register_menu_meta_box() {
    add_meta_box(
        'eDemo-SSOauth_menu_meta_box',
        esc_html__( 'Login / Logout', 'text-domain' ),
        'eDemo_SSOauth_render_menu_meta_box',
        'nav-menus',
        'side',
        'core'
        );
}
add_action( 'load-nav-menus.php', 'op_register_menu_meta_box' );
 
function eDemo_SSOauth_render_menu_meta_box() {
    // Metabox content
    echo '<strong>Hi, I am MetaBox.</strong>';
	?>
	
	<label class="menu-item-title"><input class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="24" type="checkbox"> Blog</label>
	<input class="menu-item-db-id" name="menu-item[-1][menu-item-db-id]" value="0" type="hidden">
	<input class="menu-item-object" name="menu-item[-1][menu-item-object]" value="page" type="hidden">
	<input class="menu-item-parent-id" name="menu-item[-1][menu-item-parent-id]" value="0" type="hidden">
	<input class="menu-item-type" name="menu-item[-1][menu-item-type]" value="post_type" type="hidden">
	<input class="menu-item-title" name="menu-item[-1][menu-item-title]" value="Blog" type="hidden">
	<input class="menu-item-url" name="menu-item[-1][menu-item-url]" value="http://e.demokracia.rulez.org/blog/" type="hidden">
	<input class="menu-item-target" name="menu-item[-1][menu-item-target]" value="" type="hidden">
	<input class="menu-item-attr_title" name="menu-item[-1][menu-item-attr_title]" value="" type="hidden">
	<input class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="" type="hidden">
	<input class="menu-item-xfn" name="menu-item[-1][menu-item-xfn]" value="" type="hidden">
<input class="button-secondary submit-add-to-menu right" value="Hozzáadás a menühöz" name="add-post-type-menu-item" id="submit-eDemo-SSOauth-menu-item" type="submit">
	<?php
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
