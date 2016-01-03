<?php
class edemo_auth {

	const QUERY_VAR				= 'sso_callback';
	const USER_ROLE				= 'eDemo_SSO_role';
	const CALLBACK_URI			= 'sso_callback';
	const USERMETA_ID			= 'eDemoSSO_ID'; 
	const USERMETA_TOKEN		= 'eDemoSSO_refresh_token';
	const USERMETA_ASSURANCES	= 'eDemoSSO_assurances';
	const WP_REDIR_VAR			= 'wp_redirect';
	const SSO_LOGIN_URL			= 'sso.edemokraciagep.org/static/login.html';
	const SSO_UIDVAR			= 'eDemoSSO_uid';
	
	protected $callbackURL;
	protected $error_message;
	protected $auth_message;
	protected $appkey;
	protected $allowBind;
	protected $allowRegister;
	protected $allowLogin;
	protected $secret;
	protected $sslverify;
	protected $access_token;
	protected $refresh_token;
	protected $default_role;
	protected $SSO_code;
	protected $SSO_action;
	protected $needed_assurances;
	private   $com;

	function __construct() {
		
		if (!session_id()) session_start();
					
		add_option('eDemoSSO_appkey', '', '', 'yes');
		add_option('eDemoSSO_secret', '', '', 'yes');
		add_option('eDemoSSO_appname', '', '', 'yes');
		add_option('eDemoSSO_sslverify', '', '', 'yes');
		add_option('eDemoSSO_allowBind', '', '', 'yes');
		add_option('eDemoSSO_allowRegister', '', '', 'yes');
		add_option('eDemoSSO_default_role', '', '', 'yes');
		add_option('eDemoSSO_hide_adminbar', '', '', 'yes');
		add_option('eDemoSSO_needed_assurances', '', '', 'yes');
    
		$this->callbackURL = get_site_url( "", "", "https" )."/".self::CALLBACK_URI;
		$this->appkey = get_option('eDemoSSO_appkey');
		$this->allowBind = get_option('eDemoSSO_allowBind');
		$this->allowRegister = get_option('eDemoSSO_allowRegister');
		$this->allowLogin = get_option('eDemoSSO_allowLogin');
		$this->secret = get_option('eDemoSSO_secret');
		$this->sslverify = get_option('eDemoSSO_sslverify');
        $this->default_role = get_option('eDemoSSO_default_role');
		$this->hide_adminbar = get_option('eDemoSSO_hide_adminbar');
		$this->needed_assurances = get_option('eDemoSSO_needed_assurances');
		$this->array_of_needed_assurances = ($this->needed_assurances)?explode(',',$this->needed_assurances):array();

		$this->com = new edemo_SSO_com( $this->callbackURL, $this->appkey, $this->secret, $this->sslverify);
		
		if (isset($_SESSION['eDemoSSO_auth_message'])) {
			$this->auth_message=$_SESSION['eDemoSSO_auth_message'];
			$_SESSION['eDemoSSO_auth_message']='';
		}
		if (isset($_SESSION['eDemoSSO_error_message'])) {
			$this->error_message=$_SESSION['eDemoSSO_error_message'];
			$_SESSION['eDemoSSO_error_message']='';
		}
		
		### Adding sso callback function to rewrite rules
		add_action( 'generate_rewrite_rules', array( $this, 'add_rewrite_rules' ) );
		add_action( 'login_footer', array( $this, 'add_login_button' ) );
		add_filter( 'the_content', array( $this, 'the_content_filter' ) );

		### Plugin activation hooks
		register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivation' ) );
		
		add_shortcode('SSOsignit', array( $this, 'sign_it' ) );	
		
		### Adding admin page
		add_action('admin_menu', array( $this, 'addAdminPage' ) );

		### Create Text Domain For Translations
		add_action( 'plugins_loaded', array( $this, 'textdomain' ) );
	
		add_action( 'wp_login', array ( $this, 'get_SSO_assurances'), 10, 1);
		add_filter( 'wp_authenticate_user', array( $this, 'authenticate_user'), 1 );
		
		// adding page script
		add_action( 'wp_enqueue_scripts', array ( $this, 'add_js') );

		add_filter( 'do_parse_request',  array( $this, 'do_parse_request'), 10, 3 );
		add_action( 'admin_notices', array( $this, 'notice') );
		add_action( 'admin_head', array( $this,'hide_update_notice_to_all_but_admin_users'), 1 );

		// registering widgets
		if (!is_admin()) add_action( 'widgets_init', array( $this, 'register_widgets' ) );		
	}

	function get_SSO_site_url() {
		return $this->com->get_SSO_site_url();
	}

	function register_widgets() {
		register_widget( 'eDemoSSO_login' );
	}
	
	// adding page script
	function add_js(){
		wp_enqueue_script( 'pagescript', plugins_url( '/../js/edemo_sso_auth.js' , __FILE__ ));	
	}

	#
	#property interfaces
	#
	
	public function is_bind_allowed() {
		return $this->allowBind;
	}
	
	public function is_register_allowed() {
		return $this->allowRegister;
	}
	
	public function is_login_allowed() {
		return $this->allowLogin;
	}
	
	#
	# Helper functions
	#
	
	private function make_urivars($params_array){
		$retval='';
		foreach ($params_array as $key=>$value) {
			$retval.='&'.$key.'='.$params_array[$key];
		}
		if ($retval!='') $retval=substr($retval,1);
		return $retval;
	}
	
	public function SSO_redirect_uri($params_array){
		return '&redirect_uri='.urlencode($this->callbackURL.'?'.$this->make_urivars($params_array).'&'.self::WP_REDIR_VAR.'='.$_SERVER['REQUEST_URI']);
	}

	public function get_SSO_action_link($action){
		return 'https://'.$this->com->get_SSO_auth_uri().'?response_type=code&client_id='.$this->appkey.$this->SSO_redirect_uri(array('SSO_action'=>$action));
	}

	public function get_action_link($action,$uid=null){
		$params_array=array('SSO_action'=>$action, '_wpnonce'=>wp_create_nonce($action));
		if ($uid) $params_array[self::SSO_UIDVAR]=$uid;
		return $this->callbackURL.'?'.$this->make_urivars($params_array).'&'.self::WP_REDIR_VAR.'='.$_SERVER['REQUEST_URI'];
	}

	function get_refresh_token($user_id) {
		return get_user_meta($user_id,self::USERMETA_TOKEN, true);
	}

	public function has_user_SSO($user_id) {
		return get_user_meta($user_id,self::USERMETA_ID, true)!='';
	}
	
//	function get_appkey() {return this->$appkey;}
	
//	function get_callbackURL() {return self->$callbackURL;}
	
	

	
	function add_login_button() { ?>
	<div id="eDemoSSO-message-container"></div>
	<div style="width: 320px; margin: 20px auto; display: table; background: #FFF none repeat scroll 0% 0%; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.13);">
		<div style="margin: 26px 24px 26px;">
		<h3 align="center" style="margin-bottom: 15px;"><?= __('SSO login','eDemo-SSO') ?></h3>
		<div class="button <?= ($this->allowLogin)?'':'disabled'?>">
	<?php if ($this->allowLogin) {?>
			<a href="https://<?=$this->com->get_SSO_auth_uri()?>?response_type=code&client_id=<?=$this->appkey?>&redirect_uri=<?=urlencode($this->callbackURL.'?'.self::WP_REDIR_VAR.'=/&SSO_action=login')?>">
				<?=__( 'SSO login', 'eDemo-SSO' );?>
			</a>
	<?php }
	else { ?>
			<?=__( 'SSO login', 'eDemo-SSO' );?>
	<?php }?>
		</div>
		<div class="button <?= ($this->allowLogin and $this->allowRegister)?'':'disabled'?>" width="50%">
	<?php if ($this->allowRegister and $this->allowLogin) {?>
			<a href="https://<?=$this->com->get_SSO_auth_uri()?>?response_type=code&client_id=<?=$this->appkey?>&redirect_uri=<?=urlencode($this->callbackURL.'?'.self::WP_REDIR_VAR.'=/&SSO_action=register')?>">
				<?=__( 'SSO register', 'eDemo-SSO' );?>
			</a>
	<?php }
	else { ?>
			<?=__( 'SSO register', 'eDemo-SSO' );?>
	<?php }?>
		</div>
		<p style="margin-top: 15px;"><?= ($this->allowLogin)?'':__('Sorry! Login with SSO service isn\'t allowed temporarily.', 'eDemo-SSO')?></p>
	</div></div>
	<?php 
	
	
	
	}
 


	function is_account_disabled($user_id) {
		return get_user_option( 'eDemoSSO_account_disabled', $user_id, false );
	}

	function update_user_profile() {
		if ( !current_user_can( 'edit_users' ) ) return;
        global $user_id;

    // User cannot disable itself
		if ( get_current_user_id() == $user_id ) return;

	// Lock
		if( isset( $_POST['EdemoSSO_disable_account'] ) && $_POST['EdemoSSO_disable_account'] ) {
			$this->disable_user_account( $user_id );
		} else { // Unlock
			$this->enable_user_account( $user_id );
		}
    }

	function disable_user_account( $user_id ){
		update_user_option( $user_id, 'eDemoSSO_account_disabled', true, false );
	}
	function enable_user_account( $user_id ) {
		update_user_option( $user_id, 'eDemoSSO_account_disabled', false, false );
	}
	
	function authenticate_user( $user ) {

		if ( is_wp_error( $user ) ) return $user;

    // Return error if user account is banned
		if ( get_user_option( 'eDemoSSO_account_disabled', $user->ID, false ) ) {
			return new WP_Error( 'eDemoSSO_account_disabled', __('<strong>ERROR</strong>: This user account is disabled.', 'eDemo-SSO'), $user );
		}
		return $user;
	}

	//adding plugin texdomain
	function textdomain() {
		load_plugin_textdomain( 'eDemo-SSO', false, plugin_basename( dirname( __FILE__ ) ) . '/../languages' );
	}


	
	//
	// Actual functionality
	//
	
  // shortcode for 'sign it' function
 	// [SSOsignit text="Sign it if you agree with" thanks="Thank you" signed="Has been signed"]

  function sign_it( $atts )	{
    $a = shortcode_atts( array(
        'text'   => __('Sign it if you agree with'),
        'thanks' => __('Thanks for your sign'),
        'signed' => __('You signed yet, thanks'),
          ), $atts );

	if ( !is_user_logged_in() ) {
		return '
		<a href="https://'.$this->com->get_SSO_auth_uri().'?response_type=code&client_id='.$this->appkey.'&redirect_uri='.urlencode($this->callbackURL.'?wp_redirect='.$_SERVER['REQUEST_URI'].'&signed=true').'">
			<div class="btn">
				'.$a['text'].'
			</div>
		</a>';
    }
	
    elseif ( isset( $_GET['signed'] ) ) {
      if ($this->is_signed()) return '<div class="button SSO_signed">'.$a['signed'].'</div>';
      else {
        $this->do_sign_it();
        return '<div class="button SSO_signed">'.$a['thanks'].'</div>';
      }
    } 
    return '<a href="'.get_permalink().'?signed=true"><div class="btn">'.$a['text'].'</div></a>';
	}

  // saving the signing event in database
  function do_sign_it(){}
  
  // checking if is it signed yet
  function is_signed(){ 
    return true ;
  }
  
	//
	// Hooks
	//


	function add_rewrite_rules() {
		global $wp_rewrite;
		$rules = array( self::CALLBACK_URI.'(.+?)$' => 'index.php$matches[1]&'.self::QUERY_VAR.'=true',
                    self::CALLBACK_URI.'$'      => 'index.php?'.self::QUERY_VAR.'=true&'  );
		$wp_rewrite->rules = $rules + (array)$wp_rewrite->rules;
	}

	function plugin_activation() {

		// Adding new user role "eDemo_SSO_role" only with "read" capability
	  
		add_role( self::USER_ROLE, 'eDemo_SSO user', array( 'read' => true, 'level_0' => true ) );

		// Adding new rewrite rules     
    
		global $wp_rewrite;
		$wp_rewrite->flush_rules(); // force call to generate_rewrite_rules()
	}
	
	function plugin_deactivation() {
	
		// Removing SSO rewrite rules  
		remove_action( 'generate_rewrite_rules', array( $this, 'rewrite_rules' ) );
		global $wp_rewrite;
		$wp_rewrite->flush_rules(); // force call to generate_rewrite_rules()
	}

	function do_parse_request( $result, $wp, $extra_query_vars){
		if (strpos($_SERVER['REQUEST_URI'],'/'.self::CALLBACK_URI)!==false) {
			if (isset($_GET['SSO_action'])) {
				$this->SSO_action=$_GET['SSO_action'];
				if (isset($_GET['code'])) {
					$this->SSO_code=$_GET['code'];
					$_SESSION['eDemoSSO_auth_message']=$this->callback_process();
				}
				else {
					$this->do_action($this->SSO_action);
				}
			}
			if (isset($_GET[self::WP_REDIR_VAR])) {
				$location=urldecode($_GET[self::WP_REDIR_VAR]);
				error_log('redirect');
				wp_redirect($location);
				exit;
			}
		}
		return $result;
	}
	

  //
  // displaying auth error message in the top of content
  //
  
	// we will found out what is the best way to display this (pop-up or anithing else) 
  
  function the_content_filter( $content ) {
    return $content;
  }
	
	public function get_user_by_SSO_id($ssouid) {
		$users=get_users( array('meta_key' => self::USERMETA_ID, 'meta_value' => $ssouid) );
		if ($users) return $users[0];
	}
	
	function hide_update_notice_to_all_but_admin_users() {
		if (!current_user_can('update_core')) {
			remove_action( 'admin_notices', 'update_nag', 3 );
		}	
	}

	function notice(){
		if ($this->error_message) {
			$class='error';
			$message=$this->error_message; 
		}
		elseif ($this->auth_message) {
			$message=$this->auth_message;
			$class='notice notice-success';
		}
		else return;
		?>
		<div class="<?= $class ?>">
			<p><?= $message ?></p>
		</div>
		<?php
	}
	
	function do_action($action){
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) ) {
			error_log(json_encode($_REQUEST));
			$uid=(isset($_REQUEST[self::SSO_UIDVAR])?$_REQUEST[self::SSO_UIDVAR]:get_current_user_id());
			error_log('action: '.$action);
			error_log('iud: '.$uid);
			switch ($action){
				case 'refresh':
					if ($this->has_user_SSO($uid)) {
						if ($token=$this->com->request_token_by_refresh_token(get_user_meta( $uid, self::USERMETA_TOKEN, true ))) {
							if ($user_data = $this->com->request_for_user_data($token['access_token'])) {
								error_log('do_refresh userdata[userid]='.$user_data['userid']);
								error_log('do_refresh getusermeta='.get_user_meta( $uid, self::USERMETA_ID, true ));
								if ($user_data['userid']==get_user_meta( $uid, self::USERMETA_ID, true )) {
									$user_data['refresh_token']=$token['refresh_token'];
									if ($this->refreshUserMeta($uid,$user_data)) {
										$_SESSION['eDemoSSO_auth_message']=($uid==get_current_user_id())?
											__('Your SSO metadata has been refreshed successfully', 'eDemo-SSO'):
											__('The user\'s SSO metadata has been refreshed successfully', 'eDemo-SSO');
										error_log('do_refresh eDemoSSO_auth_message: '.$_SESSION['eDemoSSO_auth_message']);
									}
								}
								else {
								if ( $gotuser=$this->get_user_by_SSO_id($user_data['userid']))  { 
									update_user_meta($gotuser->ID, self::USERMETA_TOKEN, $token['refresh_token']); 
								}
								$this->error_message=__('Someting went wrong, the userid is diffrent as expected', 'eDemo-SSO');
								}
							}
						}
						$_SESSION['eDemoSSO_error_message']=$this->error_message;
					}
					break;
				case 'unbind':
					if ($this->has_user_SSO($uid)) {
						if ($this->deleteUserMeta($uid)) 
							$_SESSION['eDemoSSO_auth_message'] = ($uid==get_current_user_id())?
								__('Your SSO metadata has been deleted. You can\'t login with SSO service anymore.', 'eDemo-SSO'):
								__('SSO metadata has been deleted. The user can\'t login with SSO service anymore.', 'eDemo-SSO');
						else $_SESSION['eDemoSSO_error_message']=__('Someting went wrong', 'eDemo-SSO');
					}
					break;
				case 'get_message':
					error_log('itt vok');
					header('Content-Type:application/json; charset=utf-8');
					$message='';
					if (isset($_SESSION['eDemoSSO_auth_message'])) {
						$message=$_SESSION['eDemoSSO_auth_message'];
						$_SESSION['eDemoSSO_auth_message']='';
					}
					echo json_encode(array('text'=>$message));
					error_log('get_messages lefutott: '.$message);
					error_log('');
					exit;
					break;
			}
		}
	}
	
  //
  // Commumication with oauth server
  //

  // The main callback function controlls the whole authentication process
   
	function callback_process() {

		if (isset($this->SSO_code) and $this->SSO_code!='') {
			if ( $token = $this->com->request_token_by_code( $this->SSO_code ) ) {
				$this->access_token=$token['access_token'];
				$this->refresh_token=$token['refresh_token'];
				if ( $user_data = $this->com->request_for_user_data( $this->access_token ) and isset($this->SSO_action) ) {
					$ssoUser = $this->get_user_by_SSO_id($user_data['userid']);
//					$ssoUser = get_users( array('meta_key' => self::USERMETA_ID, 'meta_value' => $user_data['userid']) );
					switch ($this->SSO_action){ 
						case 'register':
							if (!$ssoUser and $this->allowRegister) {
								if ( $user_id = $this->registerUser($user_data, $token)) {
									$ssoUser = get_user_by( 'id', $user_id );
								}
								else $this->error_message=$user_id;
							}
						case 'login':
							if ( $ssoUser ) {
								$this->refreshUserMeta($ssoUser->ID, Array(	'userid' => $user_data['userid'],
																			'refresh_token' => $token['refresh_token'],
																			'assurances' => $user_data['assurances'] ));
								$response=$this->authenticate_user($ssoUser);
								if (!is_wp_error($response)) {
									$this->error_message=($this->signinUser($ssoUser))?__('You are signed in', 'eDemo-SSO'):__("Can't log in", 'eDemo-SSO');
								}
								else {
									error_log(json_encode($response));
									$this->error_message=$response->get_error_message();
								}
							}
							else {
								if ($this->allowRegister) {
									$mstr=__('This user hasn\'t registered yet. Would you like to <a href="%s">register</a>?', 'eDemo-SSO');
									$this->error_message = sprintf( wp_kses( $mstr, array( 'a' => array( 'href' => array('%s') ) ) ) , $this->get_SSO_action_link('register') );
								}
								else {
									$this->error_message = __('You haven\'t account here, registering with SSO service isn\'t allowed momentarily.<br/>Try to contact with the site administrator.', 'eDemo-SSO');
								}
							}
							break;
						case 'binding':
							if (is_user_logged_in()) {
								$delete_action='';
								if ( $ssoUser = get_users( array('meta_key' => self::USERMETA_ID, 'meta_value' => $user_data['userid']) ) ) {
									require_once(ABSPATH.'wp-admin/includes/user.php');
									wp_delete_user($ssoUser[0]->ID,get_current_user_id());
									$delete_action=__('Old SSO user has been erased, its data has been reassigned to the current user. ', 'eDemo-SSO');
								}
								$this->refreshUserMeta(get_current_user_id(), Array(	'userid' => $user_data['userid'],
																						'refresh_token' => $token['refresh_token'],
																						'assurances' => $user_data['assurances'] ));
								$this->error_message=$delete_action.__("SSO account has been binded successfully", 'eDemo-SSO');
							}							
							break;
					}
				}
			}
		}
		else $this->error_message = __('Invalid page request - missing code', 'eDemo-SSO');
		return $this->error_message;
	}
  
	private function check_needed_assurances($array_of_assurances) {
		if (count($this->array_of_needed_assurances)==0) return true;
		foreach ($this->array_of_needed_assurances as $assurance) {
			if ( !in_array($assurance,$array_of_assurances) ) return false;
		}
		return true;
	}
  
#  
# Functions for user handling
#
  
/**
* Registering the new user
*
* Used for inserting a new user in the Wordpress user database with
* the data comes from the SSO service
*
* @since 0.1
*
* @param array  	$user_data	user data object coming from SSO service
* @param array  	$token 		tokens sent by SSO server 
*
* @return mixed		the new wp user's id on success, false on failure.
*/
  
	function registerUser($user_data, $token){

		if ($this->check_needed_assurances($user_data['assurances'])) {
			$display_name = __('SSO user','eDemo-SSO');
			$user_id = wp_insert_user( array( 'user_login' => $user_data['userid'],
                                          'user_email' => $user_data['email'],
                                          'display_name' => $display_name,
										  'user_pass' => null,
                                          'role' => $this->default_role ));
		//On success
			if( !is_wp_error($user_id) ) {
				
				// creating SSO specific meta data
				$this->refreshUserMeta($user_id, Array(	'userid' => $user_data['userid'],
													'refresh_token' => $token['refresh_token'],
													'assurances' => $user_data['assurances'] ));
				wp_update_user( array('ID'=>$user_id, 'nickname'=> $display_name ));
				update_user_option( $user_id, 'eDemoSSO_account_disabled', false, false );
				if ($this->hide_adminbar) update_user_option( $user_id, 'show_admin_bar_front', false );
				return $user_id;
			}
		//On failure in communication with the SSO server
			else {
				$this->error_message=$user_id->get_error_message(); 
			}
		}
		else $this->error_message=__("The following assurances needed for registration: ",'eDemo-SSO').str_replace(',', ', ', $this->needed_assurances);
		return false;
	}
  
	function refreshUserMeta($user_id, $data){
		update_user_meta( $user_id, self::USERMETA_ID, $data['userid'] );
		update_user_meta( $user_id, self::USERMETA_TOKEN, $data['refresh_token'] );
		update_user_meta( $user_id, self::USERMETA_ASSURANCES, json_encode($data['assurances']) );
		return true;
	}
	
	function deleteUserMeta($user_id) {
		delete_user_meta( $user_id, self::USERMETA_ID );
		delete_user_meta( $user_id, self::USERMETA_TOKEN );
		delete_user_meta( $user_id, self::USERMETA_ASSURANCES );
		return true;
	}
  
  //  Logging in the user
	function signinUser($user) {
		wp_set_current_user( $user->ID, $user->data->user_login );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->data->user_login );
		return get_current_user_id()==$user->ID;
	}

	
	function ____________t_o_d_o____________(){}
	
# requesting for assurances if the user logs in with any other credential then SSO	
	function get_SSO_assurances($user_login) {
		$user=get_user_by('login',$user_login);
		if ( $this->has_user_SSO($user->ID) ) {

		}
	}
	
} // end of class declaration
?>