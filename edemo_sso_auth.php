<?php
	/*
		Plugin Name: eDemo SSO authentication
		Plugin URI: 
		Description: Allows you connect to the Edemo SSO server, and autenticate the users, who acting on your site
		Version: 0.02
		Author: Claymanus
		Author URI:
		Text Domain: eDemo-SSO
		Domain Path: /languages
	*/

### Version
define( 'EDEMO_SSO_VERSION', 0.01 );

class eDemoSSO {

	const SSO_DOMAIN			= 'sso.edemokraciagep.org';
	const SSO_TOKEN_URI			= 'sso.edemokraciagep.org/v1/oauth2/token';
	const SSO_AUTH_URI			= 'sso.edemokraciagep.org/v1/oauth2/auth';
	const SSO_USER_URI			= 'sso.edemokraciagep.org/v1/users/me';
	const SSO_USERS_URI			= 'sso.edemokraciagep.org/v1/users';
	const QUERY_VAR				= 'sso_callback';
	const USER_ROLE				= 'eDemo_SSO_role';
	const CALLBACK_URI			= 'sso_callback';
	const USERMETA_ID			= 'eDemoSSO_ID'; 
	const USERMETA_TOKEN		= 'eDemoSSO_refresh_token';
	const USERMETA_ASSURANCES	= 'eDemoSSO_assurances';
	const WP_REDIR_VAR			= 'wp_redirect';
	const SSO_LOGIN_URL			= 'sso.edemokraciagep.org/static/login.html';
	const SSO_UIDVAR			= 'eDemoSSO_uid';
	const SSO_SITE_URL			= 'https://sso.edemokraciagep.org/static/login.html';

	static $callbackURL;
	public $error_message;
	public $auth_message;
	static $appkey;
	static $allowBind;
	static $allowRegister;
	static $allowLogin;
	private $secret;
	private $sslverify;
	private $access_token;
	private $refresh_token;
	private $default_role;
	private $SSO_code;
	private $SSO_action;
	private $needed_assurances;


	function __construct() {
		
		add_option('eDemoSSO_appkey', '', '', 'yes');
		add_option('eDemoSSO_secret', '', '', 'yes');
		add_option('eDemoSSO_appname', '', '', 'yes');
		add_option('eDemoSSO_sslverify', '', '', 'yes');
		add_option('eDemoSSO_allowBind', '', '', 'yes');
		add_option('eDemoSSO_allowRegister', '', '', 'yes');
		add_option('eDemoSSO_default_role', '', '', 'yes');
		add_option('eDemoSSO_hide_adminbar', '', '', 'yes');
		add_option('eDemoSSO_needed_assurances', '', '', 'yes');
    
		self::$callbackURL = get_site_url( "", "", "https" )."/".self::CALLBACK_URI;
		self::$appkey = get_option('eDemoSSO_appkey');
		self::$allowBind = get_option('eDemoSSO_allowBind');
		self::$allowRegister = get_option('eDemoSSO_allowRegister');
		self::$allowLogin = get_option('eDemoSSO_allowLogin');
		$this->secret = get_option('eDemoSSO_secret');
		$this->sslverify = get_option('eDemoSSO_sslverify');
        $this->default_role = get_option('eDemoSSO_default_role');
		$this->hide_adminbar = get_option('eDemoSSO_hide_adminbar');
		$this->needed_assurances = get_option('eDemoSSO_needed_assurances');
		$this->array_of_needed_assurances = ($this->needed_assurances)?explode(',',$this->needed_assurances):array();
		
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
	
		### Show SSO data
		add_action( 'show_user_profile', array ( $this, 'show_SSO_user_profile' ) );
		add_action( 'edit_user_profile', array ( $this, 'show_SSO_user_profile' ) );
		add_action( 'wp_login', array ( $this, 'get_SSO_assurances'), 10, 1);
		
		### registering widgets
		add_action( 'widgets_init', array ( $this, 'register_widgets' ) );
		
		### adding page script
		add_action( 'wp_enqueue_scripts', array ( $this, 'add_js') );
		add_action( 'admin_enqueue_scripts', array ( $this, 'add_js') );
		
		add_filter( 'do_parse_request',  array($this, 'do_parse_request'), 10, 3 );
		add_filter( 'wp_headers', array($this, 'wp_headers_filter'), 10, 2);
		add_action( 'wp_footer', array( $this, 'messageSript') );
	}
	function wp_headers_filter($headers, $wp){
		return $headers;
	}
	
	### adding page script
	function add_js(){
		wp_enqueue_script( 'pagescript', plugins_url( '/edemo_sso_auth.js' , __FILE__ ));	
	}

	#
	# Helper functions
	#
	
	static function make_urivars($params_array){
		$retval='';
		foreach ($params_array as $key=>$value) {
			$retval.='&'.$key.'='.$params_array[$key];
		}
		if ($retval!='') $retval=substr($retval,1);
		return $retval;
	}
	
	public static function SSO_redirect_uri($params_array){
		return '&redirect_uri='.urlencode(self::$callbackURL.'?'.self::make_urivars($params_array).'&'.self::WP_REDIR_VAR.'='.$_SERVER['REQUEST_URI']);
	}

	public static function SSO_auth_action_link($action){
		return 'https://'.self::SSO_AUTH_URI.'?response_type=code&client_id='.self::$appkey.self::SSO_redirect_uri(array('SSO_action'=>$action));
	}
	
	public static function SSO_action_link($action,$uid=null){
		$params_array=array('SSO_action'=>$action, '_wpnonce'=>wp_create_nonce($action));
		if ($uid) $params_array[self::SSO_UIDVAR]=$uid;
		return self::$callbackURL.'?'.self::make_urivars($params_array).'&'.self::WP_REDIR_VAR.'='.$_SERVER['REQUEST_URI'];
	}

	function get_refresh_token($user_id) {
		return get_user_meta($user_id,self::USERMETA_TOKEN, true);
	}

	public static function has_user_SSO($user_id) {
		return get_user_meta($user_id,self::USERMETA_ID, true)!='';
	}
//	static function get_appkey() {return this->$appkey;}
	
//	static function get_callbackURL() {return self->$callbackURL;}
	

	
	function register_widgets() {
		register_widget( 'eDemoSSO_login' );
	}
	


	function get_SSO_assurances($user_login) {
		$user=get_user_by('login',$user_login);
		$refresh_token=$this->get_refresh_token($user->ID);
		if ($this->access_token == '' and $refresh_token!='' ) {
			if ( $token=$this->request_new_token($refresh_token) ) {
				$this->access_token=$token['access_token'];	
				if ( $user_data = $this->requestUserData( ) ) {
					if ( $ssoUsers = get_users( array('meta_key' => self::USERMETA_ID, 'meta_value' => $user_data['userid']) ) ) {
						$ssoUser=$ssoUsers[0]->data;
//						if ($ssoUser[]) {
							
//						}
					}
				}				
			}
		}
	}
	
	function SSO_client_token_requiest() {
// not implemented yet
	}
	
	function add_login_button() { ?>
	<div id="eDemoSSO-message-container"></div>
	<div style="width: 320px; margin: 20px auto; display: table; background: #FFF none repeat scroll 0% 0%; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.13);">
		<div style="margin: 26px 24px 26px;">
		<h3 align="center" style="margin-bottom: 15px;"><?= __('SSO login','eDemo-SSO') ?></h3>
		<div class="button <?= (self::$allowLogin)?'':'disabled'?>">
	<?php if (self::$allowLogin) {?>
			<a href="https://<?=self::SSO_AUTH_URI?>?response_type=code&client_id=<?=self::$appkey?>&redirect_uri=<?=urlencode(self::$callbackURL.'?'.self::WP_REDIR_VAR.'=/&SSO_action=login')?>">
				<?=__( 'SSO login', 'eDemo-SSO' );?>
			</a>
	<?php }
	else { ?>
			<?=__( 'SSO login', 'eDemo-SSO' );?>
	<?php }?>
		</div>
		<div class="button <?= (self::$allowLogin and self::$allowRegister)?'':'disabled'?>" width="50%">
	<?php if (self::$allowRegister and self::$allowLogin) {?>
			<a href="https://<?=self::SSO_AUTH_URI?>?response_type=code&client_id=<?=self::$appkey?>&redirect_uri=<?=urlencode(self::$callbackURL.'?'.self::WP_REDIR_VAR.'=/&SSO_action=register')?>">
				<?=__( 'SSO register', 'eDemo-SSO' );?>
			</a>
	<?php }
	else { ?>
			<?=__( 'SSO register', 'eDemo-SSO' );?>
	<?php }?>
		</div>
		<p style="margin-top: 15px;"><?= (self::$allowLogin)?'':__('Sorry! Login with SSO service isn\'t allowed temporarily.', 'eDemo-SSO')?></p>
	</div></div>
	<?php }
 
function show_SSO_user_profile( $user ) { ?>
 
	<hr>
	<h3><?= __( 'SSO user data', 'eDemo-SSO' )?></h3>
    <table class="form-table">
		<div id="eDemoSSO-message-container"></div>
		<?php if (isset($_SESSION['eDemoSSO_auth_message']) and  $_SESSION['eDemoSSO_auth_message']!='') {?> 
			<div class="notice notice-success inline">
				<p><?=$_SESSION['eDemoSSO_auth_message']?></p>
			</div>
		<?php }			
		if (self::has_user_SSO($user->ID)) {?> 
        <tr>
            <th>SSO id</th>
            <td><?= get_user_meta($user->ID,self::USERMETA_ID, true) ?></td>
		</tr>
		<tr>
            <th>SSO token</th>
            <td><?= get_user_meta($user->ID,self::USERMETA_TOKEN, true) ?></td>
		</tr>
		<tr>			
			<th>SSO assurances</th>
            <td><?= get_user_meta($user->ID,self::USERMETA_ASSURANCES, true) ?></td>
        </tr>
		<tr>
			<th></th>
			<td>
				<p>
					<a class="button" href="<?=self::SSO_action_link('refresh',$user->ID)?>">
						<?= __( 'Refresh', 'eDemo-SSO' )?>
					</a>
				</p>
				<p class="description"><?= __('Downloads the assurences from the SSO service', 'eDemo-SSO' )?></p>
			</td>
		</tr>
		<tr>
			<th></th>
			<td>
				<p>
					<a class="button" href="<?=self::SSO_action_link('unbind',$user->ID)?>">
						<?= __( 'Unbind', 'eDemo-SSO' )?>
					</a>
				</p>
				<p class="description"><?= __('Unbinding SSO account from the user', 'eDemo-SSO' )?></p>
			</td>
		</tr>
		<?php }
		else if ($user->ID==get_current_user_id() and self::$allowBind){?>
		<tr>
			<th></th>
			<td>
				<p><?__( "For this account hasn't SSO account binded", 'eDemo-SSO' )?><p>
				<a class="button" href="<?=self::SSO_auth_action_link('binding',$user->ID)?>">
					<?= __( 'Bind SSO account', 'eDemo-SSO' )?>
				</a>
				<p class="description"><?= __('This will bind your account with an SSO account. If you are logged in to your SSO account, this goes automaticly. Otherwise you will be redirected to the SSO login page served by SSO Service. ', 'eDemo-SSO' )?></p>
				<p class="description"><?= __('If you have here registered with your SSO account, that will be merged in this account with all activity data stored before. User data still remain.', 'eDemo-SSO' )?></p>
			</td>
		</tr>
		<?php }?>
     </table>
	<?php $this->messageSript('eDemoSSO-profilepage-message-container');
	}

	//adding plugin texdomain
	function textdomain() {
		load_plugin_textdomain( 'eDemo-SSO', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	//
	// Options/admin panel
	//

	// Add page to options menu.
	function addAdminPage() 
	{
	  // Add a new submenu under Options:
		add_options_page('eDemo SSO Options', 'eDemo SSO', 'manage_options', 'edemosso', array( $this, 'displayAdminPage'));
	}

	// Display the admin page.
	function displayAdminPage() {
		
		if (isset($_POST['edemosso_update'])) {
//			check_admin_referer();    // EZT MAJD MEG KELLENE NÉZNI !!!!!

			// Update options 
			$this->sslverify		= isset($_POST['EdemoSSO_sslverify']);
			self::$appkey			= $_POST['EdemoSSO_appkey'];
			$this->secret			= $_POST['EdemoSSO_secret'];
			$this->appname			= $_POST['EdemoSSO_appname'];
			self::$allowBind		= isset($_POST['EdemoSSO_allowBind']);
			self::$allowRegister	= isset($_POST['EdemoSSO_allowRegister']);
			self::$allowLogin		= isset($_POST['EdemoSSO_allowLogin']);
			$this->default_role		= $_POST['EdemoSSO_default_role'];
			$this->hide_adminbar	= isset($_POST['EdemoSSO_hide_adminbar']);
			$this->needed_assurances= $_POST['EdemoSSO_needed_assurances'];

			update_option( 'eDemoSSO_appkey'   			, self::$appkey   );
			update_option( 'eDemoSSO_secret'   			, $this->secret   );
			update_option( 'eDemoSSO_appname'  			, $this->appname  );
			update_option( 'eDemoSSO_sslverify'			, $this->sslverify );
			update_option( 'eDemoSSO_allowBind'			, self::$allowBind );
			update_option( 'eDemoSSO_allowRegister'		, self::$allowRegister );
			update_option( 'eDemoSSO_allowLogin'		, self::$allowLogin );
			update_option( 'eDemoSSO_hide_adminbar'		, $this->hide_adminbar );
			update_option( 'eDemoSSO_default_role'  	, $this->default_role );
			update_option( 'eDemoSSO_needed_assurances' , str_replace(' ', '', $this->needed_assurances) );

			// echo message updated
			echo "<div class='updated fade'><p>Options updated.</p></div>";
		}		
		?>
		<div class="wrap">

			<h2><?= __( 'eDemo SSO Authentication Options', 'eDemo-SSO' ) ?></h2>
			<form method="post">
				<fieldset class='options'>
					<table class="form-table">
						<tr>
							<th>
								<label for="EdemoSSO_appname"><?= __( 'Application name:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='text' size='16' maxlength='30' name='EdemoSSO_appname' id='EdemoSSO_appname' value='<?= get_option('eDemoSSO_appname'); ?>' />
								<p class="description"><?= __( 'Used for registering the application', 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_appkey"><?= __( 'Application key:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='text' size='40' maxlength='40' name='EdemoSSO_appkey' id='EdemoSSO_appkey' value='<?= self::$appkey; ?>' />
								<p class="description"><?= __( 'Application key.', 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_secret"><?= __( 'Application secret:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='text' size='40' maxlength='40' name='EdemoSSO_secret' id='EdemoSSO_secret' value='<?= $this->secret; ?>' />
								<p class="description"><?= __( 'Application secret.', 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_sslverify"><?= __( 'Allow verify ssl certificates:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_sslverify' id='EdemoSSO_sslverify' <?= (($this->sslverify)?'checked':''); ?> />
								<p class="description"><?= __( "If this set, the ssl certificates will be verified during the communication with sso server. Uncheck is recommended if your site has no cert, or the issuer isn't validated.", 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="eDemoSSO_callbackURI"><?= __( 'eDemo_SSO callback URL:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<?= self::$callbackURL ?>
								<p class="description"><?=__('Callback url for communication with the SSO_server', 'eDemo-SSO')?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowBind"><?= __( 'SSO account binding:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowBind' id='EdemoSSO_allowBind' <?= ((self::$allowBind)?'checked':''); ?> />
								<p class="description"><?= __( "If this set, a SSO account can be binded with the given Wordpress account. User gets a 'bind' button on his datasheet and in the SSO login widget.", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowRegister"><?= __( 'Allow registering:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowRegister' id='EdemoSSO_allowRegister' <?= ((self::$allowRegister)?'checked':''); ?> />
								<p class="description"><?= __( "This setting allows the user registrating with SSO service.", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowLogin"><?= __( 'Allow sign in:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowLogin' id='EdemoSSO_allowLogin' <?= ((self::$allowLogin)?'checked':''); ?> />
								<p class="description"><?= __( "This setting allows the users logging in. In emergency case unset this option to forbid the users logging in", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_hide_adminbar"><?= __( 'Hide adminbar:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_hide_adminbar' id='EdemoSSO_hide_adminbar' <?= (($this->hide_adminbar)?'checked':''); ?> />
								<p class="description"><?= __( "If this set, the hide admin bar option will be set on the users profile during registration process. That means, the admin bar willn't be shown as default if the user logged in. Anyway hide and show admin bar can be set on the user's profile page", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_default_role"><?= __( 'Default WP role for SSO usrs:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<select name="EdemoSSO_default_role" id="EdemoSSO_default_role">
									<?= wp_dropdown_roles( $this->default_role ); ?>
								</select>
								<p class="description"><?= __( "The default WP role, which will be added during the SSO registration", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_needed_assurances"><?= __( 'Needed assurances:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='text' size='16' maxlength='30' name='EdemoSSO_needed_assurances' id='EdemoSSO_needed_assurances' value='<?= get_option('EdemoSSO_needed_assurances'); ?>' />
								<p class="description"><?= __( 'Comma separated list of assurances needed for allowing registering the user. Keep empty, if no assurance needed for registration.', 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<td colspan="2">
							<p class="submit"><input type='submit' name='edemosso_update' value='<?= __( 'Update Options', 'eDemo-SSO' ) ?>' /></p>
							</td>
						</tr>
					</table>
				</fieldset>
			</form>
		</div>
		<?php
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
		<a href="https://'.self::SSO_AUTH_URI.'?response_type=code&client_id='.self::$appkey.'&redirect_uri='.urlencode(self::$callbackURL.'?wp_redirect='.$_SERVER['REQUEST_URI'].'&signed=true').'">
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
			if (!session_id()) session_start();
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

	function do_action($action){
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) ) {
			$uid=(isset($_REQUEST['self::SSO_UIDVAR'])?$_REQUEST['self::SSO_UIDVAR']:get_current_user_id());
			switch ($action){
				case 'refresh':
					if (self::has_user_SSO($uid)) {
						if ($token=$this->request_new_token(get_user_meta($uid,self::USERMETA_TOKEN, true))) {
							if ($user_data = $this->requestUserData()) {
								if ($this->refreshUserMeta($uid,$user_data)) {
									$this->error_message=__('Your SSO metadata has been refreshed successfully', 'eDemo-SSO');
								}
							}
						}
						$_SESSION['eDemoSSO_auth_message']=$this->error_message;
					}
					break;
				case 'unbind':
					if (self::has_user_SSO($uid)) {
						if ($this->deleteUserMeta($uid)) $message=__('Your SSO metadata has been deleted', 'eDemo-SSO');
						else $message=__('Someting went wrong', 'eDemo-SSO');
						$_SESSION['eDemoSSO_auth_message']=$message;
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
			if ( $token = $this->requestToken( $this->SSO_code ) ) {
				$this->access_token=$token['access_token'];
				$this->refresh_token=$token['refresh_token'];
				if ( $user_data = $this->requestUserData( $this->access_token ) and isset($this->SSO_action) ) {
					$ssoUser = get_users( array('meta_key' => self::USERMETA_ID, 'meta_value' => $user_data['userid']) );
					switch ($this->SSO_action){ 
						case 'register':
							if (!$ssoUser and self::$allowRegister) {
								if ( $user_id=$this->registerUser($user_data, $token)) {
									$ssoUser[0]=get_user_by('id',$user_id);
								}
								else $this->error_message=$user_id;
							}
						case 'login':
							if ( $ssoUser ) {
								$this->refreshUserMeta($ssoUser[0]->ID, Array(	'userid' => $user_data['userid'],
																		'refresh_token' => $token['refresh_token'],
																		'assurances' => $user_data['assurances'] ));
								$this->error_message=($this->signinUser($ssoUser[0]))?__('You are signed in', 'eDemo-SSO'):__("Can't log in", 'eDemo-SSO');
							}
							else {
								if (self::$allowRegister) {
									$mstr=__('This user hasn\'t registered yet. Would you like to <a href="%s">register</a>?', 'eDemo-SSO');
									$this->error_message = sprintf( wp_kses( $mstr, array( 'a' => array( 'href' => array('%s') ) ) ) , self::SSO_auth_action_link('register') );
								}
								else {
									$this->error_message = __('You haven\'t account here, registering with SSO service isn\'t allowed momentarily.<br/>Try to contact with the site administrator.', 'eDemo-SSO');
								}
							}
							break;
						case 'refresh':
							if ( $ssoUser = get_users( array('meta_key' => self::USERMETA_ID, 'meta_value' => $user_data['userid']) ) ) {
								$this->refreshUserMeta($user_id, Array(	'userid' => $user_data['userid'],
																		'refresh_token' => $token['refresh_token'],
																		'assurances' => $user_data['assurances'] ));
								$this->error_message=__("User's SSO data has been updated successfully", 'eDemo-SSO');
							}
							else $this->error_message=__("User not found", 'eDemo-SSO');
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
  
  // token requesting phase
  function request_new_token($refresh_token) {
	      $response = wp_remote_post( 'https://'.self::SSO_TOKEN_URI, array(
                 'method' => 'POST',
                'timeout' => 30,
            'redirection' => 1,
	          'httpversion' => '1.0',
	             'blocking' => true,
	              'headers' => array(),
	                 'body' => array(  'grant_type' => 'refresh_token',
				                       'refresh_token' => $refresh_token,
										'client_id' => self::$appkey,
										'client_secret' => $this->secret
									   ),
	              'cookies' => array(),
	            'sslverify' => $this->sslverify ) );
    if ( is_wp_error( $response )  ) {
      $this->error_message = $response->get_error_message();
      return false;
    }
    else {
      $body = json_decode( $response['body'], true );
      if (!empty($body)){
        if ( isset( $body['error'] ) ) {
          $this->error_message = __("The SSO-server's response: ", 'eDemo-SSO'). $body['error'];
          return false;
        }
        else {
			return $body;
		}
      }
        $this->error_message = __("Unexpected response cames from SSO Server", 'eDemo-SSO');
        return false;
    }
  }
 
  function requestToken( $code ) {
    $response = wp_remote_post( 'https://'.self::SSO_TOKEN_URI, array(
                 'method' => 'POST',
                'timeout' => 30,
            'redirection' => 10,
	          'httpversion' => '1.0',
	             'blocking' => true,
	              'headers' => array(),
	                 'body' => array( 'code' => $code,
				                      'grant_type' => 'authorization_code',
				                       'client_id' => self::$appkey,
			                     'client_secret' => $this->secret,
			                      'redirect_uri' => self::$callbackURL ),
	              'cookies' => array(),
	            'sslverify' => $this->sslverify ) );
    if ( is_wp_error( $response )  ) {
      $this->error_message = $response->get_error_message();
      return false;
    }
    else {
      $body = json_decode( $response['body'], true );
      if (!empty($body)){
        if ( isset( $body['errors'] ) ) {
          $this->error_message = __("The SSO-server's response: ", 'eDemo-SSO'). $body['errors'];
          return false;
        }
        else return $body;
      }
        $this->error_message = __("Unexpected response cames from SSO Server", 'eDemo-SSO');
        return false;
    }
  }
  
  // user data requesting phase, called if we have a valid token
  
  function requestUserData( $access_token ) {
	if ($access_token=='') return false;
    $response = wp_remote_get( 'https://'.self::SSO_USER_URI, array(
                    'timeout' => 30,
                'redirection' => 10,
                'httpversion' => '1.0',
                   'blocking' => true,
                    'headers' => array( 'Authorization' => 'Bearer '.$this->access_token ),
                    'cookies' => array(),
                  'sslverify' => $this->sslverify ) );
    if ( is_wp_error( $response ) ) {
      $this->error_message = $response->get_error_message();
      return false;
    }
    elseif ( isset( $response['body'] ) ) {
        $body = json_decode( $response['body'], true );
        if (!empty($body)) {
			return $body;
		}
    }
	$this->error_message=__("Invalid response has been came from SSO server", 'eDemo-SSO');
    return false;
	}
  
	function check_needed_assurances($array_of_assurances) {
		if (count($this->array_of_needed_assurances)==0) return true;
		foreach ($this->array_of_needed_assurances as $assurance) {
			if ( !in_array($assurance,$array_of_assurances) ) return false;
		}
		return true;
	}
  
  //
  //  Wordpress User function
  //
  
  //  Registering the new user
  
	function registerUser($user_data, $token){

	// registering new user
		if ($this->check_needed_assurances($user_data['assurances'])) {
			$display_name = __('SSO user','eDemo-SSO');
			$user_id = wp_insert_user( array( 'user_login' => $user_data['userid'],
                                          'user_email' => $user_data['email'],
                                          'display_name' => $display_name,
										  'user_pass' => null,
                                          'role' => $this->default_role ));
		//On success
			if( !is_wp_error($user_id) ) {
				$this->refreshUserMeta($user_id, Array(	'userid' => $user_data['userid'],
													'refresh_token' => $token['refresh_token'],
													'assurances' => $user_data['assurances'] ));
				wp_update_user( array('ID'=>$user_id, 'nickname'=> $display_name ));
				if ($this->hide_adminbar) update_user_option( $user_id, 'show_admin_bar_front', false );
				return $user_id;
			}
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
		return;
	}
	
	function deleteUserMeta($user_id) {
		delete_user_meta( $user_id, self::USERMETA_ID );
		delete_user_meta( $user_id, self::USERMETA_TOKEN );
		delete_user_meta( $user_id, self::USERMETA_ASSURANCES );
		return;
	}
  
  //  Logging in the user
  
	function signinUser($user) {
		wp_set_current_user( $user->ID, $user->data->user_login );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->data->user_login );
		return get_current_user_id()==$user->ID;
	}

	function messageSript($container){
		?>
		<script type="text/javascript">
			eDemo_SSO.callForMessage("<?= wp_create_nonce('get_message') ?>","<?= $container ?>")
		</script>
		<?php
	}
} // end of class declaration

if (!isset($eDemoSSO)) { $eDemoSSO = new eDemoSSO(); } 

class eDemoSSO_login extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct( false, 'eDemoSSO_login' );
	}

	function widget( $args, $instance ) { 
	// Widget output
		$title = apply_filters( 'widget_title', $instance['title'] );
		$current_user = wp_get_current_user(); 
		?>
		<?= str_replace('class="widget widget_edemosso_login', 'class="widget widget_links', $args['before_widget']) ?>
		<?php if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title']; ?>
		<ul>
		<?php if (is_user_logged_in()) { ?>
		<p><?= __('Welcome ','eDemo-SSO').$current_user->display_name ?>!</p>
		<?php } ?>
		<div id="eDemoSSO-message-container"></div>
		<?php if (is_user_logged_in()) { ?>
		<?php if (eDemoSSO::$allowBind and !eDemoSSO::has_user_SSO($current_user->ID)) { ?>
		<li class="page-item"><a href="<?= eDemoSSO::SSO_auth_action_link('binding') ?>"><?= __('Bind SSO account','eDemo-SSO')?></a></li>
			<?php } ?>
		<li class="page-item"><a href="/wp-admin/profile.php"><?=__('Show user profile', 'eDemo-SSO')?></a></li>
		<li class="page-item"><a href="<?=wp_logout_url( urldecode($_SERVER['REQUEST_URI']) )?>"><?= __('Logout', 'eDemo-SSO')?></a></li>
		<?php }
		elseif (eDemoSSO::$allowLogin) { ?>
		<li class="page-item"><a href="<?= eDemoSSO::SSO_auth_action_link('login')    ?>"><?= __('Login with SSO', 'eDemo-SSO')    ?></a></li>
		<?php if (eDemoSSO::$allowRegister) { ?>
		<li class="page-item"><a href="<?= eDemoSSO::SSO_auth_action_link('register') ?>"><?= __('Register with SSO', 'eDemo-SSO') ?></a></li>
		<?php }} else {?>
		<p><?= __('Sorry! Login with SSO service isn\'t allowed temporarily.', 'eDemo-SSO') ?></p>
		<?php }?>
		<li class="page-item"><a href="<?= eDemoSSO::SSO_SITE_URL ?>"><?= __('SSO services', 'eDemo-SSO')?></a></li>
		</ul>
		<?= $args['after_widget'] ?>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;

	}

	function form( $instance ) {
		// Output admin widget options form
		
		// widget title
		if ( isset( $instance[ 'title' ] ) ) $title = $instance[ 'title' ];
		else $title = __( 'New title', 'eDemo-SSO' );
		
		// Widget admin form
		?>
		<p>
			<label for="<?= $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?= $this->get_field_id( 'title' ); ?>" name="<?= $this->get_field_name( 'title' ); ?>" type="text" value="<?= esc_attr( $title ); ?>" />
		</p>
		<?php
	}
}
?>
