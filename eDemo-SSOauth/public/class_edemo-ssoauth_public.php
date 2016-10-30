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
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
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
	/**
	 * Add SSO login area to the bottom of login screen
	 *
	 * Add login button and register button.
	 * The ability is controlled trough the 'allowLogin' and 'allowRegister' options.
	 *
	 * @since    0.0.1
	 */	
	function login_page_extension() { ?>
	<div style="width: 320px; margin: 20px auto; display: table; background: #FFF none repeat scroll 0% 0%; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.13);">
		<div style="margin: 26px 24px 26px;">
		<h3 align="center" style="margin-bottom: 15px;"><?= __('SSO login',eDemo_SSOauth::TEXTDOMAIN) ?></h3>
		<div class="button <?= ($this->allowLogin)?'':'disabled'?>">
	<?php if ($this->allowLogin) {?>
			<a href="<?= $this->get_SSO_action_link('login')    ?>"><?=__( 'SSO login', eDemo_SSOauth::TEXTDOMAIN );?></a>
	<?php }
	else { ?>
			<?=__( 'SSO login', eDemo_SSOauth::TEXTDOMAIN );?>
	<?php }?>
		</div>
		<div class="button <?= ($this->allowLogin and $this->allowRegister)?'':'disabled'?>" width="50%">
	<?php if ($this->allowRegister and $this->allowLogin) {?>
			<a href="<?= $this->get_SSO_action_link('register') ?>"><?=__( 'SSO register', eDemo_SSOauth::TEXTDOMAIN );?></a>
	<?php }
	else { ?>
			<?=__( 'SSO register', eDemo_SSOauth::TEXTDOMAIN );?>
	<?php }?>
		</div>
		<p style="margin-top: 15px;"><?= ($this->allowLogin)?'':__('Sorry! Login with SSO service isn\'t allowed temporarily.', eDemo_SSOauth::TEXTDOMAIN)?></p>
	</div></div>
	<?php 
	}
	/**
	 * Adding rewrite rules to be able catching the callback calls
	 *
	 * @since    0.0.1
	 */		
	function add_rewrite_rules() {
		global $wp_rewrite;
		$default_callback_uri='sso_callback';
		$callback_uri=get_option('eDemoSSO_callback_uri',$default_callback_uri);
		if (!$callback_uri or $callback_uri=="") $callback_uri=$default_callback_uri;
		$rules = array( $callback_uri.'(.+?)$' => 'index.php$matches[1]&'.$callback_uri.'=true',
						$callback_uri.'$'      => 'index.php?'.$callback_uri.'=true&'  );
		$wp_rewrite->rules = $rules + (array)$wp_rewrite->rules;
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
	* Do action 
	*
	* Doing the wanted action according to the command comes in the param
	*  if the nonce check is succeeded.
	*
	* Actions are:
	* - login		* Login the user with the SSO data
	* - register	* Register the user with the SSO data
	* - refresh		* Refresh the user SSO data
	* - binding		* Bind an SSO account with the current wp account. The old 'SSO only' account will be deleted if given.
	* - unbind		* Delete the user's SSO data.
	* - get_message	* JSON response on an AJAX request. Contains the plugin messages.
	*
	* @since	0.0.1
	* @access   private
	* @param	string	$action	contains the command
	*
	* @return	nothing
	*/
	private function do_action( $action ){
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) ) {
			$uid = isset($_REQUEST[eDemo_SSOauth::SSO_UIDVAR]) ?
					$_REQUEST[eDemo_SSOauth::SSO_UIDVAR] :
					get_current_user_id();
			if ( isset($_REQUEST['code']) ) {
				if ( $user_data = $this->get_user_data_by_code( $_REQUEST['code'] ) ) {
					$ssoUser = $this->get_user_by_SSO_id( $user_data['userid'] );					
				}
			}
			switch ($action){
				case 'refresh':
					if ( $this->has_user_SSO( $uid ) ) {
						if ( $user_data = $this->get_user_data_by_refresh_token( $this->get_refresh_token( $uid  ) ) ){
							if ( $user_data['userid']==get_user_meta( $uid, eDemo_SSOauth::USERMETA_ID, true ) ) {
								if ( $this->refreshUserMeta( $uid,$user_data ) ) {
									$_SESSION['eDemoSSO_auth_message'] = ($uid==get_current_user_id())?
										__('Your SSO metadata has been refreshed successfully', eDemo_SSOauth::TEXTDOMAIN):
										__('The user\'s SSO metadata has been refreshed successfully', eDemo_SSOauth::TEXTDOMAIN);
								}
							}
							else {
								$_SESSION['eDemoSSO_error_message']=__('Something went wrong, the userid is different as expected.', eDemo_SSOauth::TEXTDOMAIN);
							}
						}
					}
					else {
						$_SESSION['eDemoSSO_error_message']=__('Something went wrong, the user doesn\'t have SSO metada. Refresh aborted', eDemo_SSOauth::TEXTDOMAIN);
					}
					break;
				case 'register':
					if ( !$ssoUser ) {
						if ( $this->allowRegister ) {
							if ( $user_id = $this->register_the_user( $user_data )) {
								$ssoUser = get_user_by( 'id', $user_id );
							}
						}
						else {
							$_SESSION['eDemoSSO_error_message'] = __('Registering with SSO service isn\'t allowed momentarily.<br/>Try to contact with the site administrator.', eDemo_SSOauth::TEXTDOMAIN);
						}
					}
				case 'login':
					if ( isset( $ssoUser ) and !empty( $ssoUser ) ) {
						$this->refreshUserMeta( $ssoUser->ID, $user_data );
						$response=$this->authenticate_user( $ssoUser );
						if ( !is_wp_error( $response ) ) {
							$_SESSION['eDemoSSO_error_message'] = ($this->log_in_the_user($ssoUser))?__('You are signed in', eDemo_SSOauth::TEXTDOMAIN):__("Can't log in", eDemo_SSOauth::TEXTDOMAIN);
						}
						else {
							$_SESSION['eDemoSSO_error_message'] = $response->get_error_message();
						}
					}
					else {
						if ( $this->allowRegister ) {
							$mstr=__( 'This user hasn\'t registered yet. Would you like to <a href="%s">register</a>?', eDemo_SSOauth::TEXTDOMAIN);
							$_SESSION['eDemoSSO_error_message'] = sprintf( wp_kses( $mstr, array( 'a' => array( 'href' => array('%s') ) ) ) , $this->get_SSO_action_link('register') );
						}
						else {
							$_SESSION['eDemoSSO_error_message'] = __('You haven\'t account here, registering with SSO service isn\'t allowed momentarily.<br/>Try to contact with the site administrator.', eDemo_SSOauth::TEXTDOMAIN);
						}
					}
					break;
				case 'binding':
					if ( is_user_logged_in() ) {
						$message='';
						if ( $ssoUser = get_users( array('meta_key' => eDemo_SSOauth::USERMETA_ID, 'meta_value' => $user_data['userid']) ) ) {
							require_once( ABSPATH.'wp-admin/includes/user.php' );
							wp_delete_user( $ssoUser[0]->ID, get_current_user_id() );
							$message=__( 'Old SSO user has been erased, its data has been reassigned to the current user. ', eDemo_SSOauth::TEXTDOMAIN );
						}
						$this->refreshUserMeta( get_current_user_id(), $user_data );
						$_SESSION['eDemoSSO_auth_message'] = $message.__( "SSO account has been binded successfully", eDemo_SSOauth::TEXTDOMAIN);
					}							
					break;
				case 'unbind':
					if ( $this->has_user_SSO( $uid ) ) {
						if ( $this->delete_SSO_data_of_the_user( $uid ) ) 
							$_SESSION['eDemoSSO_auth_message'] = ($uid==get_current_user_id())?
								__( 'Your SSO metadata has been deleted. You can\'t login with SSO service anymore.', eDemo_SSOauth::TEXTDOMAIN):
								__( 'SSO metadata has been deleted. The user can\'t login with SSO service anymore.', eDemo_SSOauth::TEXTDOMAIN);
						else $_SESSION['eDemoSSO_error_message']=__('Someting went wrong, can\'t delete usermeta', eDemo_SSOauth::TEXTDOMAIN);
					}
					break;
				case 'get_message':
					header( 'Content-Type:application/json; charset=utf-8' );
					$message='';
					if ( isset( $_SESSION['eDemoSSO_auth_message'] ) ) {
						$message=$_SESSION['eDemoSSO_auth_message'];
						$_SESSION['eDemoSSO_auth_message']='';
					}
					echo json_encode( array( 'text'=>$message ) );
					exit;
					break;
			}
		}
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
	/*
	* Registering the new user
	*
	* After checking the needed assurance, insert a new user in the Wordpress user database with
	* the data comes from the SSO service.
	* settings controlled by plugin options are:
	* - user role
	* - hide admin bar if user logs in
	* - account accessability
	*
	* @since 0.0.1
	* @access   private
	* @param	array  	$user_data	user data object coming from SSO service and refresh_token
	*
	* @return	mixed		the new wp user's id on success, false on failure.
	*/
	private function register_the_user( $user_data ){
		if ( $this->check_needed_assurances( $user_data['assurances'] ) ) {
			$display_name = (isset($user_data['display_name'])) ? $user_data['display_name'] : __('SSO user', eDemo_SSOauth::TEXTDOMAIN);
			$user_id = wp_insert_user( array(	'user_login' => $user_data['userid'],
												'user_email' => $user_data['email'],
												'display_name' => $display_name,
												'user_pass' => null,
												'role' => $this->get_user_role($user_data['assurances']) 
											) );
			if( !is_wp_error($user_id) ) {
				// creating SSO specific meta data
				$this->refreshUserMeta( $user_id, $user_data );
				wp_update_user( array('ID'=>$user_id, 'nickname'=> $display_name ));
				update_user_option( $user_id, 'eDemoSSO_account_disabled', false, false );
				if ( $this->hide_adminbar ) update_user_option( $user_id, 'show_admin_bar_front', false );
				return $user_id;
			}
			else {
				$_SESSION['eDemoSSO_error_message'] = $user_id->get_error_message(); 
			}
		}
		else $_SESSION['eDemoSSO_error_message'] = __( "The following assurances needed for registration: ", eDemo_SSOauth::TEXTDOMAIN ).str_replace( ',', ', ', $this->needed_assurances);
		return false;
	}
	/*
	* Returns the user role with which the user will be registered
	*
	* The user role will be set according the admin options and SSO assurances
	* Can be filtered with the 'eDemo-SSOauth_get_user_role' filter
	*
	* @since 0.0.1
	* @access   private
	* @param	array  	$assurances		array of assurances coming from the SSO service
	*
	* @return	string	$user_role		the user role
	*/
	private function get_user_role( $assurances ){
		$user_role = get_option( 'eDemoSSO_default_role' );
		return apply_filters( 'eDemo-SSOauth_get_user_role', $user_role, $assurances );
	}
}
?>