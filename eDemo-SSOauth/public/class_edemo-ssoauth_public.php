<<<<<<< HEAD
<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines hooks for enqueue the public-specific stylesheet and JavaScript.
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.1
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/public
 * @author     Claymanus
 */
/**
 * This file contains the public-facing functionality class of the plugin.
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.1
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 */
/**
 * Public-facing class 
 *
 * Defines hooks for:
 * 	enqueue the public-specific stylesheet and JavaScript,
 *	login page extension,
 *	parse request filter to catch callback url calls,
 *	authenticate filter to controll account accessability,
 *	processes for requesting data from the SSO server,
 *	the action parser,
 *	action endpoint functions the manage user data
 *
 * @since      0.0.1
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 * @author     Claymanus
 */
class eDemo_SSOauth_Public extends eDemo_SSOauth_Base {
	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	public $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	public $version;
	/**
	 * The communications object
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      object    $com    An instance of the communicaation object.
	 */
	private $com;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		parent::__construct( );
		$this->com = new eDemo_SSOauth_com( $plugin_name, $version );
	}
	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/edemo-ssoauth_public.js', array( ), $this->version, false );
	}
	
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/public.css', array(), $this->version, 'all' );
	}
	
	/**
	 * Used for setting the account accessability
	 *
	 * @since    0.0.1
	 */		
	public function authenticate_user( $user ) {
		if ( is_wp_error( $user ) ) return $user;
		// Return error if user account is banned
		if ( get_user_option( 'eDemoSSO_account_disabled', $user->ID, false ) ) {
			return new WP_Error( 'eDemoSSO_account_disabled', __('<strong>ERROR</strong>: This user account is disabled.', eDemo_SSOauth::TEXTDOMAIN), $user );
		}
		return $user;
	}
	
	/**
	 * requesting for assurances if the user logs in with any other credential then SSO	
	 *
	 * @since    0.0.1
	 */	
	function get_SSO_assurances( $user_login ) {
/*	
	*** under construction ***

		$user=get_user_by('login',$user_login);
		if ( $this->has_user_SSO($user->ID) ) {

		}*/
	}
	
	#filtered by get_footer
	function the_message_frame(){
?>
		<div id="<?= eDemo_SSOauth::MESSAGE_FRAME_ID ?>" class="message-frame"></div>
<?php
	}
	
	/**
	 * parsing callback calls
	 *
	 * @since    0.0.1
	 */	
	function do_parse_request( $result, $wp, $extra_query_vars){
		if ( strpos( $_SERVER['REQUEST_URI'], '/'.get_option( 'eDemoSSO_callback_uri' ) ) !== false ) {
			if ( isset( $_REQUEST['SSO_action'] ) ) {
				$this->do_action( $_REQUEST['SSO_action'] );
			}
			if ( isset( $_REQUEST[eDemo_SSOauth::WP_REDIR_VAR] ) ) {
				$location=urldecode( $_REQUEST[eDemo_SSOauth::WP_REDIR_VAR] );
				wp_redirect( $location );
				exit;
			}
		}
		return $result;
	}
	
	/**
	 * do SSO request for user data authenticated with code 
	 *
	 * @since    0.0.1
	 */	
	private function get_user_data_by_code($code) {
		error_log('get user data by code');
		if ($code!=''){
			if ( $token = $this->com->request_token_by_code( $code ) ) {
				if ( $user_data = $this->com->request_for_user_data( $token['access_token'] ) ) {
					$user_data['refresh_token'] = $token['refresh_token'];
					return $user_data;
				}
			}
		}
		return false;
	}
	
	/**
	 * do SSO request for user data authenticated with refresh token 
	 *
	 * @since    0.0.1
	 */	
	private function get_user_data_by_refresh_token($refresh_token) {
		// request for acces token by refresh token
		if ( $token=$this->com->request_token_by_refresh_token( $refresh_token ) ) {
			// request for user data by access token
			if ( $user_data = $this->com->request_for_user_data( $token['access_token'] ) ) {
				// saving new refresh token
				$this->update_refresh_token($user_data['userid'],$token['refresh_token']);
				return $user_data;
			}
		}
		return false;
	}

	/*
	* Refresh the user's SSO data
	*
	* Refreshing the user's SSO data if the input data is valid
	*
	* @since	0.0.1
	* @access   private
	* @param	string	$user_id	wordpress user id
	* @param	array  	$data		array of user data coming from the SSO service
	*
	* @return	boolean				true if success, false if not
	*/
	private function refreshUserMeta( $user_id, $data ){
		if ( !isset( $data['userid']) or !isset($data['assurances']) or !get_user_by( 'id', $user_id ) ) {
			$_SESSION['eDemoSSO_error_message']=__('Something went wrong at refreshing user meta.', eDemo_SSOauth::TEXTDOMAIN);
			return false;
		}
		update_user_meta( $user_id, eDemo_SSOauth::USERMETA_ID, $data['userid'] );
		if ( isset( $data['refresh_token'] ) ) {
			update_user_meta( $user_id, eDemo_SSOauth::USERMETA_TOKEN, $data['refresh_token'] );
		}
		update_user_meta( $user_id, eDemo_SSOauth::USERMETA_ASSURANCES, json_encode($data['assurances']) );
		return true;
	}
	/*
	* Delete the user's SSO data
	*
	* Deleting all of the user's metadata created by the plugin
	*
	* @since 0.0.1
	* @access   private
	* @param	string	$user_id	wordpress user id
	*
	* @return	boolean 			true if success, false if not
	*/
	private function delete_SSO_data_of_the_user( $user_id ) {
		if ( !get_user_by( 'id', $user_id ) ) {
			return false;
		}
		delete_user_meta( $user_id, eDemo_SSOauth::USERMETA_ID );
		delete_user_meta( $user_id, eDemo_SSOauth::USERMETA_TOKEN );
		delete_user_meta( $user_id, eDemo_SSOauth::USERMETA_ASSURANCES );
		return true;
	}
	/*
	* Logging in the user
	*
	* @since 0.0.1
	* @access   private
	* @param	wp user object	$user	wordpress user object
	*
	* @return	boolean			true on success, false if not
	*/
	private function log_in_the_user($user) {
		wp_set_current_user( $user->ID, $user->data->user_login );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->data->user_login );
		return get_current_user_id()==$user->ID;
	}
}
?>