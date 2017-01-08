<?php
/**
 * Classes for plugin activation / deactivation
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.1
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 */
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 * @author     Claymanus
 */
class eDemo_SSOauth_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function deactivate() {
		// Removing SSO rewrite rules  
		remove_action( 'generate_rewrite_rules', array( $this, 'add_rewrite_rules' ) );
		global $wp_rewrite;
		$wp_rewrite->flush_rules(); // force call to generate_rewrite_rules()
	}
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function activate() {
	// Adding new user role "eDemo_SSO_role" only with "read" capability
		add_role( eDemo_SSOauth::USER_ROLE, 'eDemo_SSO user', array( 'read' => true, 'level_0' => true ) );
	// Adding new rewrite rules     
		global $wp_rewrite;
		$wp_rewrite->flush_rules(); // force call to generate_rewrite_rules()
	}
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	function add_rewrite_rules() {
		global $wp_rewrite;
		$callback_uri=get_option('eDemoSSO_callback_uri','sso_callback');
		$rules = array( eDemo_SSOauth::CALLBACK_URI.'(.+?)$' => 'index.php$matches[1]&'.eDemo_SSOauth::CALLBACK_URI.'=true',
						eDemo_SSOauth::CALLBACK_URI.'$'      => 'index.php?'.eDemo_SSOauth::CALLBACK_URI.'=true&'  );
		$wp_rewrite->rules = $rules + (array)$wp_rewrite->rules;
	}
}
?>
