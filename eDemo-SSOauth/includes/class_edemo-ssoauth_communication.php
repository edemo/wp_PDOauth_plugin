<?php
/**
 * This file contains the class used for communication with the SSO server
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.1
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 */
/**
 * Communication class 
 *
 * This class defines all functionality which is used for the communication with the SSO server.
 *
 * @since      0.0.1
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 * @author     Claymanus
 */
class eDemo_SSOauth_com {
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
	
	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.2
	 * @access   private
	 * @var      string    $serviceURI    the domain name of the sso service option stored into options db
	 */	
	private $serviceURI;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	function __construct( $plugin_name, $version ) {
		$this->serviceURI = get_option( 'eDemoSSO_serviceURI' );
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->callbackURL = get_site_url( "", "", "https" ).eDemo_SSOauth::CALLBACK_URI;
		$this->appkey = get_option( 'eDemoSSO_appkey' );
		$this->secret = get_option( 'eDemoSSO_secret' );
		$this->sslverify = get_option( 'eDemoSSO_sslverify' );
	}
	/**
	* Requesting user data from the SSO server
	*
	* Performs an AJAX request to the SSO server with the access token for the users data
	* Bevore call this, you have to have an access token
	*
	* @since 0.0.1
	*
	* @param	string  $access_token	access token from the SSO service
	*
	* @return	mixed	array	with the response's body contains user data if the request succeeded
	*					boolean false if an error occurs
	*/  
	function request_for_user_data( $access_token ) {
		$response = wp_remote_get( 'https://'.$this->serviceURI.eDemo_SSOauth::SSO_USER_URI, array(
                    'timeout' => 30,
                'redirection' => 10,
                'httpversion' => '1.0',
                   'blocking' => true,
                    'headers' => array( 'Authorization' => 'Bearer '.$access_token ),
                    'cookies' => array(),
                  'sslverify' => $this->sslverify ) );
		return $this->analyse_response( $response );
	}	
	/**
	* Requesting access token from the SSO server
	*
	* Performs an AJAX request to the SSO server with the code for access token
	* Bevore call this, you have to have the code
	*
	* @since 0.0.1
	*
	* @param	string  $code	the access code from the SSO server
	*
	* @return	mixed	array	with the response's body contains tokens if the request succeeded
	*					boolean false if an error occurs
	*/	
	function request_token_by_code( $code ) {
		$arr= array(
                 'method' => 'POST',
                'timeout' => 30,
            'redirection' => 10,
	          'httpversion' => '1.0',
	             'blocking' => true,
	              'headers' => array(),
	                 'body' => array( 'code' => $code,
				                      'grant_type' => 'authorization_code',
				                       'client_id' => $this->appkey,
			                     'client_secret' => $this->secret,
			                      'redirect_uri' => $this->callbackURL ),
	              'cookies' => array(),
	            'sslverify' => $this->sslverify );
		$response = wp_remote_post( 'https://'.$this->serviceURI.eDemo_SSOauth::SSO_TOKEN_URI, $arr );
error_log('token request: '.json_encode($arr));
		return $this->analyse_response( $response );
	}
	/**
	* Gives the user role with which the user will be registered
	*
	* Performs an AJAX request to the SSO server with the refresh token for the access token
	*
	* @since 0.0.1
	*
	* @param	string  $refresh_token	the user's refresh token
	*
	* @return	mixed	array	with the response's body contains tokens if the request succeeded
	*					boolean false if an error occurs
	*/	
	function request_token_by_refresh_token( $refresh_token ) {
		$arr=array(	'method' => 'POST',
					'timeout' => 30,
					'redirection' => 1,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
	                'body' => array('grant_type' => 'refresh_token',
				                    'refresh_token' => $refresh_token,
									'client_id' => $this->appkey,
									'client_secret' => $this->secret
									),
					'cookies' => array(),
					'sslverify' => $this->sslverify );
error_log('refresh: '.json_encode($arr));					
		return $this->analyse_response( wp_remote_post( 'https://'.$this->serviceURI.eDemo_SSOauth::SSO_TOKEN_URI, $arr ) );
	}
	/**
	* Analysing the response comes from the wp_remote 
	*
	* Return the data from the response and set error messages if an error occurs
	*
	* @since 0.0.1
	*
	* @param	mixed  	$response	response of the wp_remote_ function call
	*
	* @return	mixed	array	with the response's body contains data from the SSO server if the request succeeded
	*					boolean false if an error occurs
	*/		
	function analyse_response( $response ){
		if ( is_wp_error( $response )  ) {
			$_SESSION['eDemoSSO_error_message'] = $response->get_error_message();
			return false;
		}
		else {
			$body = json_decode( $response['body'], true );
			if (!empty($body)){
				if ( isset( $body['errors'] ) ) {
					$_SESSION['eDemoSSO_error_message'] = __("The SSO-server's response: ", eDemo_SSOauth::TEXTDOMAIN). $body['errors'];
				return false;
				}
				else {
// here should be implement the data validation as well
					return $body;
				}				
			}
			else {
				$_SESSION['eDemoSSO_error_message'] = __("Unexpected response cames from SSO Server", eDemo_SSOauth::TEXTDOMAIN ).json_encode($response);
				return false;
			}
		}
	}
}
?>