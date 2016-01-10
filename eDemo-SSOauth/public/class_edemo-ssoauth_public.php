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
class eDemo_SSOauth_Public {
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
		$this->allowLogin = get_option( 'eDemoSSO_allowLogin' );
		$this->allowRegister = get_option( 'eDemoSSO_allowRegister' );
		$this->common = new eDemo_SSOauth_functions();
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
	 * Adding SSO login area to the bottom of login screen
	 *
	 * @since    0.0.1
	 */	
	function add_login_button() { ?>
	<div style="width: 320px; margin: 20px auto; display: table; background: #FFF none repeat scroll 0% 0%; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.13);">
		<div style="margin: 26px 24px 26px;">
		<h3 align="center" style="margin-bottom: 15px;"><?= __('SSO login','eDemo-SSO') ?></h3>
		<div class="button <?= ($this->allowLogin)?'':'disabled'?>">
	<?php if ($this->allowLogin) {?>
			<a href="<?= $this->common->get_SSO_action_link('login')    ?>"><?=__( 'SSO login', 'eDemo-SSO' );?></a>
	<?php }
	else { ?>
			<?=__( 'SSO login', 'eDemo-SSO' );?>
	<?php }?>
		</div>
		<div class="button <?= ($this->allowLogin and $this->allowRegister)?'':'disabled'?>" width="50%">
	<?php if ($this->allowRegister and $this->allowLogin) {?>
			<a href="<?= $this->common->get_SSO_action_link('register') ?>"><?=__( 'SSO register', 'eDemo-SSO' );?></a>
	<?php }
	else { ?>
			<?=__( 'SSO register', 'eDemo-SSO' );?>
	<?php }?>
		</div>
		<p style="margin-top: 15px;"><?= ($this->allowLogin)?'':__('Sorry! Login with SSO service isn\'t allowed temporarily.', 'eDemo-SSO')?></p>
	</div></div>
	<?php 
	}
	/**
	 * Adding rewrite rules for callback
	 *
	 * @since    0.0.1
	 */		
	function add_rewrite_rules() {
		global $wp_rewrite;
		$rules = array( self::CALLBACK_URI.'(.+?)$' => 'index.php$matches[1]&'.self::QUERY_VAR.'=true',
						self::CALLBACK_URI.'$'      => 'index.php?'.self::QUERY_VAR.'=true&'  );
		$wp_rewrite->rules = $rules + (array)$wp_rewrite->rules;
	}
	/**
	 * Used for disable account functionality
	 *
	 * @since    0.0.1
	 */		
	public function authenticate_user( $user ) {
		if ( is_wp_error( $user ) ) return $user;
		// Return error if user account is banned
		if ( get_user_option( 'eDemoSSO_account_disabled', $user->ID, false ) ) {
			return new WP_Error( 'eDemoSSO_account_disabled', __('<strong>ERROR</strong>: This user account is disabled.', 'eDemo-SSO'), $user );
		}
		return $user;
	}
	/**
	 * requesting for assurances if the user logs in with any other credential then SSO	
	 *
	 * @since    0.0.1
	 */	
	function get_SSO_assurances($user_login) {
		$user=get_user_by('login',$user_login);
		if ( $this->common->has_user_SSO($user->ID) ) {

		}
	}

	/**
	 * parsing callback calls
	 *
	 * @since    0.0.1
	 */	
	function do_parse_request( $result, $wp, $extra_query_vars){
		if ( strpos( $_SERVER['REQUEST_URI'], '/'.get_option( 'eDemoSSO_callback_uri' ) ) !== false ) {
			if (isset($_GET['SSO_action'])) {
				$this->do_action($_GET['SSO_action']);
			}
			if (isset($_GET[eDemo_SSOauth::WP_REDIR_VAR])) {
				$location=urldecode($_GET[eDemo_SSOauth::WP_REDIR_VAR]);
				error_log('redirect');
				wp_redirect($location);
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
				$this->common->update_refresh_token($user_data['userid'],$token['refresh_token']);
				return $user_data;
			}
		}
		return false;
	}
	private function do_action($action){
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) ) {
error_log(json_encode($_REQUEST));
			$uid=(isset($_REQUEST[eDemo_SSOauth::SSO_UIDVAR])?$_REQUEST[eDemo_SSOauth::SSO_UIDVAR]:get_current_user_id());
error_log('action: '.$action);
error_log('iud: '.$uid);
error_log('uri: '.$_SERVER['REQUEST_URI']);
			if ( isset($_REQUEST['code']) ) {
				if ( $user_data = $this->get_user_data_by_code($_REQUEST['code']) ) {
					$ssoUser = $this->common->get_user_by_SSO_id( $user_data['userid'] );					
				}
			}
			switch ($action){
				case 'refresh':
					if ( $this->common->has_user_SSO( $uid ) ) {
						if ( $user_data = $this->get_user_data_by_refresh_token( $this->common->get_refresh_token( $uid  ) ) ){
							if ($user_data['userid']==get_user_meta( $uid, eDemo_SSOauth::USERMETA_ID, true )) {
								if ($this->refreshUserMeta($uid,$user_data)) {
									$_SESSION['eDemoSSO_auth_message'] = ($uid==get_current_user_id())?
										__('Your SSO metadata has been refreshed successfully', 'eDemo-SSO'):
										__('The user\'s SSO metadata has been refreshed successfully', 'eDemo-SSO');
								}
							}
							else {
								$_SESSION['eDemoSSO_error_message']=__('Something went wrong, the userid is different as expected.', 'eDemo-SSO');
							}
						}
					}
					else {
						$_SESSION['eDemoSSO_error_message']=__('Something went wrong, the user doesn\'t have SSO metada. Refresh aborted', 'eDemo-SSO');
					}
					break;
				case 'register':
					if ( !$ssoUser and $this->allowRegister ) {
						if ( $user_id = $this->registerUser( $user_data )) {
							$ssoUser = get_user_by( 'id', $user_id );
						}
					}
				case 'login':
					if ( isset($ssoUser) and !empty($ssoUser) ) {
						$this->refreshUserMeta($ssoUser->ID, $user_data);
						$response=$this->authenticate_user($ssoUser);
						if (!is_wp_error($response)) {
							$_SESSION['eDemoSSO_error_message']=($this->signinUser($ssoUser))?__('You are signed in', 'eDemo-SSO'):__("Can't log in", 'eDemo-SSO');
						}
						else {
							$_SESSION['eDemoSSO_error_message']=$response->get_error_message();
						}
					}
					else {
						if ($this->allowRegister) {
							$mstr=__('This user hasn\'t registered yet. Would you like to <a href="%s">register</a>?', 'eDemo-SSO');
							$_SESSION['eDemoSSO_error_message'] = sprintf( wp_kses( $mstr, array( 'a' => array( 'href' => array('%s') ) ) ) , $this->common->get_SSO_action_link('register') );
						}
						else {
							$_SESSION['eDemoSSO_error_message'] = __('You haven\'t account here, registering with SSO service isn\'t allowed momentarily.<br/>Try to contact with the site administrator.', 'eDemo-SSO');
						}
					}
					break;
				case 'binding':
					if ( is_user_logged_in() ) {
						$message='';
						if ( $ssoUser = get_users( array('meta_key' => eDemo_SSOauth::USERMETA_ID, 'meta_value' => $user_data['userid']) ) ) {
							require_once( ABSPATH.'wp-admin/includes/user.php' );
							wp_delete_user( $ssoUser[0]->ID, get_current_user_id() );
							$message=__( 'Old SSO user has been erased, its data has been reassigned to the current user. ', 'eDemo-SSO' );
						}
						$this->refreshUserMeta( get_current_user_id(), $user_data );
						$_SESSION['eDemoSSO_auth_message'] = $message.__( "SSO account has been binded successfully", 'eDemo-SSO');
					}							
					break;
				case 'unbind':
					if ( $this->has_user_SSO( $uid ) ) {
						if ( $this->deleteUserMeta( $uid ) ) 
							$_SESSION['eDemoSSO_auth_message'] = ($uid==get_current_user_id())?
								__('Your SSO metadata has been deleted. You can\'t login with SSO service anymore.', 'eDemo-SSO'):
								__('SSO metadata has been deleted. The user can\'t login with SSO service anymore.', 'eDemo-SSO');
						else $_SESSION['eDemoSSO_error_message']=__('Someting went wrong, can\'t delete usermeta', 'eDemo-SSO');
					}
					break;
				case 'get_message':
					header('Content-Type:application/json; charset=utf-8');
					$message='';
					if (isset($_SESSION['eDemoSSO_auth_message'])) {
						$message=$_SESSION['eDemoSSO_auth_message'];
						$_SESSION['eDemoSSO_auth_message']='';
					}
					echo json_encode(array('text'=>$message));
					exit;
					break;
			}
		}
	}
	protected function refreshUserMeta($user_id, $data){
		if ( !isset($data['userid']) or !isset($data['assurances']) or !get_user_by( 'id', $user_id ) ) {
			$_SESSION['eDemoSSO_error_message']=__('Something went wrong at refreshing user meta.', 'eDemo-SSO');
			return false;
		}
		update_user_meta( $user_id, eDemo_SSOauth::USERMETA_ID, $data['userid'] );
		if (isset($data['refresh_token'])) {
			update_user_meta( $user_id, eDemo_SSOauth::USERMETA_TOKEN, $data['refresh_token'] );
		}
		update_user_meta( $user_id, eDemo_SSOauth::USERMETA_ASSURANCES, json_encode($data['assurances']) );
		return true;
	}
	protected function deleteUserMeta($user_id) {
		if ( !get_user_by( 'id', $user_id ) ) {
			return false;
		}
		delete_user_meta( $user_id, eDemo_SSOauth::USERMETA_ID );
		delete_user_meta( $user_id, eDemo_SSOauth::USERMETA_TOKEN );
		delete_user_meta( $user_id, eDemo_SSOauth::USERMETA_ASSURANCES );
		return true;
	}
  //  Logging in the user
	function signinUser($user) {
		wp_set_current_user( $user->ID, $user->data->user_login );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->data->user_login );
		return get_current_user_id()==$user->ID;
	}
	/**
	* Registering the new user
	*
	* Used for inserting a new user in the Wordpress user database with
	* the data comes from the SSO service
	*
	* @since 0.0.1
	*
	* @param	array  	$user_data	user data object coming from SSO service and refresh_token
	*
	* @return	mixed		the new wp user's id on success, false on failure.
	*/
	function registerUser( $user_data ){
		if ( $this->common->check_needed_assurances( $user_data['assurances'] ) ) {
			$display_name = (isset($user_data['display_name'])) ? $user_data['display_name'] : __('SSO user', 'eDemo-SSO');
			$user_id = wp_insert_user( array(	'user_login' => $user_data['userid'],
												'user_email' => $user_data['email'],
												'display_name' => $display_name,
												'user_pass' => null,
												'role' => $this->get_user_role($user_data['assurances']) 
											) );
		//On success
			if( !is_wp_error($user_id) ) {
				// creating SSO specific meta data
				$this->refreshUserMeta( $user_id, $user_data );
				wp_update_user( array('ID'=>$user_id, 'nickname'=> $display_name ));
				update_user_option( $user_id, 'eDemoSSO_account_disabled', false, false );
				if ( get_option('eDemoSSO_hide_adminbar') ) update_user_option( $user_id, 'show_admin_bar_front', false );
				return $user_id;
			}
		//On failure in communication with the SSO server
			else {
				$_SESSION['eDemoSSO_error_message'] = $user_id->get_error_message(); 
			}
		}
		else $_SESSION['eDemoSSO_error_message'] = __( "The following assurances needed for registration: ", 'eDemo-SSO' ).str_replace( ',', ', ', $this->needed_assurances);
		return false;
	}
	/**
	* Gives the user role with which the user will be registered
	*
	* The user role will be set according the admin options and SSO assurances
	* Can be filtered with the 'eDemo-SSOauth_get_user_role' filter
	*
	* @since 0.0.1
	*
	* @param	array  	$assurances		array of assurances coming from the SSO service
	*
	* @return	string	$user_role		the user role
	*/
	function get_user_role( $assurances ){
		$user_role = get_option( 'eDemoSSO_default_role' );
		return apply_filter( 'eDemo-SSOauth_get_user_role', $user_role, $assurances );
	}
}
?>