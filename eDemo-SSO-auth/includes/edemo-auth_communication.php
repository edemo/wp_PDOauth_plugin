<?php
	#
	# SSO communication
	#
	
class edemo_SSO_com {

	const SSO_DOMAIN	= 'sso.edemokraciagep.org';
	const SSO_TOKEN_URI	= 'sso.edemokraciagep.org/v1/oauth2/token';
	const SSO_AUTH_URI	= 'sso.edemokraciagep.org/v1/oauth2/auth';
	const SSO_USER_URI	= 'sso.edemokraciagep.org/v1/users/me';
	const SSO_USERS_URI	= 'sso.edemokraciagep.org/v1/users';
	const SSO_SITE_URL	= 'https://sso.edemokraciagep.org/static/login.html';
	
	function __construct( $callbackURL, $appkey, $secret, $sslverify ) {
		$this->callbackURL = $callbackURL;
		$this->appkey = $appkey;
		$this->secret = $secret;
		$this->sslverify = $sslverify;
	}
	
	function get_SSO_auth_uri() {
		return self::SSO_AUTH_URI;
	}
	
	function get_SSO_site_url() {
		return self::SSO_SITE_URL;
	}
	

  // user data requesting phase, called if we have a valid token
  
  function request_for_user_data( $access_token ) {
	error_log('requestUserData acces_token: '.$access_token);
	if ($access_token=='') return false;
    $response = wp_remote_get( 'https://'.self::SSO_USER_URI, array(
                    'timeout' => 30,
                'redirection' => 10,
                'httpversion' => '1.0',
                   'blocking' => true,
                    'headers' => array( 'Authorization' => 'Bearer '.$access_token ),
                    'cookies' => array(),
                  'sslverify' => $this->sslverify ) );
	error_log('request user data: '.json_encode($response));
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
	
  function request_token_by_code( $code ) {
    $response = wp_remote_post( 'https://'.self::SSO_TOKEN_URI, array(
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
        else
			error_log(json_encode($body));
			return $body;
      }
        $this->error_message = __("Unexpected response cames from SSO Server", 'eDemo-SSO');
        return false;
    }
  }
	
 // token requesting phase
	function request_token_by_refresh_token($refresh_token) {
		error_log($refresh_token);
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
				error_log(json_encode($arr));			
		$response = wp_remote_post( 'https://'.self::SSO_TOKEN_URI, $arr );

		if ( is_wp_error( $response )  ) {
			$this->error_message = $response->get_error_message();
			return false;
		}
		else {
			error_log(json_encode($response));
			$body = json_decode( $response['body'], true );
			if (!empty($body)){
				if ( isset( $body['errors'] ) ) {
					$this->error_message = __("The SSO-server's response: ", 'eDemo-SSO'). $body['errors'];
					return false;
				}
				else return $body;	
			}
			else {
				$this->error_message = __("Unexpected response cames from SSO Server", 'eDemo-SSO');
				return false;
			}
		}	
	}		
	
}

?>