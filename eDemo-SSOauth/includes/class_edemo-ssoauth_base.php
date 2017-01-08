<?php
/**
 * This file contains the plugin's base class
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.1
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 */
/**
 * Base plugin class 
 *
 * This class defines all functionality which is commonly used by the plugin
 *
 * @since      0.0.1
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 * @author     Claymanus
 */
 class eDemo_SSOauth_Base {
	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $callbackURL	Contains the callback URL to which the SSO server sends the responses
	 */
	private $callbackURL;
	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $appkey		Application key stored into options db
	 */
	private $appkey;
	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $secret		Application secret stored into options db
	 */
	private $secret;
	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $sslverify    Ssl verify option stored into options db
	 */
	private $sslverify;
	private $serviceURI;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	function __construct( ) {
		$this->serviceURI = get_option( 'eDemoSSO_serviceURI' );
		$this->callbackURL = get_site_url( "", "", "https" ).eDemo_SSOauth::CALLBACK_URI;
		$this->appkey = get_option( 'eDemoSSO_appkey' );
		$this->secret = get_option( 'eDemoSSO_secret' );
		$this->sslverify = get_option( 'eDemoSSO_sslverify' );
		$this->allowBind = get_option('eDemoSSO_allowBind');
		$this->allowRegister = get_option('eDemoSSO_allowRegister');
		$this->allowLogin = get_option('eDemoSSO_allowLogin');
		$this->needed_assurances = get_option('eDemoSSO_needed_assurances');
        $this->default_role = get_option('eDemoSSO_default_role');
		$this->hide_adminbar = get_option('eDemoSSO_hide_adminbar');
		$this->array_of_needed_assurances = ($this->needed_assurances)?explode(',',$this->needed_assurances):array();
		$this->terms_of_usege_page_url = get_option('terms_of_usege_page_url');
	}
	/**
	 * registering login widget
	 *
	 * @since    0.0.1
	 */	
	public function register_widgets() {
		register_widget( 'eDemo_SSOauth_login_widget' );
	}
		/**
	 * Display messages in the notice area
	 *
	 * @since    0.0.1
	 */
	public function notice(){
		if ( isset( $_SESSION['eDemoSSO_error_message'] ) and !empty( $_SESSION['eDemoSSO_error_message'] ) ) {
			$class='error';
			$message=$_SESSION['eDemoSSO_error_message']; 
			$_SESSION['eDemoSSO_error_message']='';
		}
		elseif ( isset($_SESSION['eDemoSSO_auth_message'] ) and !empty( $_SESSION['eDemoSSO_auth_message'] ) ) {
			$message=$_SESSION['eDemoSSO_auth_message'];
			$class='notice notice-success';
			$_SESSION['eDemoSSO_auth_message']='';
		}
		else return;
		?>
		<div class="<?= $class ?>">
			<p><?= $message ?></p>
		</div>
		<?php
	}
	
	public function has_user_SSO($user_id) {
		return get_user_meta( $user_id, eDemo_SSOauth::USERMETA_ID, true ) != '';
	}
	public function make_urivars($params_array){
		$retval='';
		foreach ($params_array as $key=>$value) {
			$retval.='&'.$key.'='.$params_array[$key];
		}
		if ($retval!='') $retval=substr($retval,1);
		return $retval;
	}
	public function get_refresh_token($user_id) {
		return get_user_meta( $user_id, eDemo_SSOauth::USERMETA_TOKEN, true );
	}
	public function update_refresh_token($user_id, $refresh_token) {
		return update_user_meta($user_id, eDemo_SSOauth::USERMETA_TOKEN, $refresh_token); 
	}	
	public function SSO_redirect_uri($params_array){
		return '&redirect_uri='.urlencode($this->callbackURL.'?'.$this->make_urivars($params_array));
	}
	public function is_account_disabled($user_id) {
		return get_user_option( 'eDemoSSO_account_disabled', $user_id, false );
	}
	Public function get_button_action($action) {
		return "javascript: eDemo_SSO.button_click('".$this->get_SSO_action_link($action)."')";
	}
	public function get_SSO_action_link($action){
		return 'https://'.$this->serviceURI.eDemo_SSOauth::SSO_AUTH_URI.'?response_type=code&client_id='.$this->appkey.$this->SSO_redirect_uri(array('action'=>'eDemoSSO_'.$action,'_wpnonce'=>wp_create_nonce($action)));
	}
	public function get_user_SSO_id($user_id){
		return get_user_meta($user_id,eDemo_SSOauth::USERMETA_ID, true);
	}
	public function get_action_link($action,$uid=null){
		$params_array=array('SSO_action'=>$action, '_wpnonce'=>wp_create_nonce($action));
		if ($uid) $params_array[eDemo_SSOauth::SSO_UIDVAR]=$uid;
		return $this->callbackURL.'?'.$this->make_urivars($params_array).'&'.eDemo_SSOauth::WP_REDIR_VAR.'='.$_SERVER['REQUEST_URI'];
	}
	public function get_user_by_SSO_id($ssouid) {
		$users=get_users( array('meta_key' => eDemo_SSOauth::USERMETA_ID, 'meta_value' => $ssouid) );
		return ($users) ? $users[0] : false;
	}
	function check_needed_assurances($array_of_assurances) {
		if (count($this->array_of_needed_assurances)==0) return true;
		foreach ($this->array_of_needed_assurances as $assurance) {
			if ( !in_array($assurance,$array_of_assurances) ) return false;
		}
		return true;
	}
		/*
	* Returns the user role with which the user will be registered
	*
	* The user role will be set according the admin options and SSO assurances
	* Can be filtered with the 'eDemo-SSOauth_get_user_role' filter
	*
	* @since 0.0.1
	* @access   protected
	* @param	array  	$assurances		array of assurances coming from the SSO service
	*
	* @return	string	$user_role		the user role
	*/
	protected function get_user_role( $assurances ){
		$user_role = get_option( 'eDemoSSO_default_role' );
		return apply_filters( 'eDemo-SSOauth_get_user_role', $user_role, $assurances );
	}
 
 }