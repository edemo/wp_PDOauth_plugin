<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.2
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/admin
 * @author     Claymanus
 */

 
class eDemo_SSOauth_Ajax extends eDemo_SSOauth_Base {
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
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		parent::__construct( );
		$this->com = new eDemo_SSOauth_com( $plugin_name, $version );
	}
	function http_origin($origin){
		return "https://szabadszavazas.hu";
	}

	function render_the_reloader_message(){
		echo '<html>
				<head>
				<script type="text/javascript">
				parent.postMessage("hide","*");
				parent.postMessage("reload","*");
				</script>
				</head>
				<body>
				</body>
				</html>';
	}
	
	function wp_ajax_eDemoSSO_login (){
		header_remove( 'X-Frame-Options' );
		$this->do_action("login");
		$this->render_the_reloader_message();
		die(1);
	}
	
	function wp_ajax_eDemoSSO_refresh (){
		header_remove( 'X-Frame-Options' );
		$this->do_action("refresh");
		$this->render_the_reloader_message();
		die(1);
	}
	
	function wp_ajax_eDemoSSO_register (){
		error_log('register');
		header_remove( 'X-Frame-Options' );
		$this->do_action("register");
		$this->render_the_reloader_message();
		die(1);
	}
	
	function wp_ajax_eDemoSSO_binding (){
		header_remove( 'X-Frame-Options' );
		$this->do_action("binding");
		$this->render_the_reloader_message();
		die(1);
	}
	
	function wp_ajax_eDemoSSO_unbind (){
		header_remove( 'X-Frame-Options' );
		$this->do_action("unbind");
		$this->render_the_reloader_message();
		die(1);
	}

	function wp_ajax_eDemoSSO_get_message (){
		echo "wp_ajax_get_message </br>";
		$this->do_action("get_message");
		die($_SESSION['eDemoSSO_error_message'].'</br>');
	}
	
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
							$_SESSION['eDemoSSO_error_message'] = sprintf( wp_kses( $mstr, array( 'a' => array( 'href' => array('%s') ) ) ) , $this->get_button_action('register') );
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
		} else error_log("szar a nonce");
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
			echo "ide".$this->get_user_role($user_data['assurances']);
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
	
	/**
	 * Register the stylesheets for the ajax area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
//		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );
	}
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
//		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );
	}
	/**
	 * to hiding admin notices if the current user isn't an admin
	 *
	 * @since    0.0.1
	 */

}


