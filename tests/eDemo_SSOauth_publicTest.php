<?php

require_once 'tests/fake_lib.php';

require_once 'eDemo-SSOauth/includes/class_edemo-ssoauth_base.php';
require_once 'eDemo-SSOauth/public/class_edemo-ssoauth_public.php';

class eDemo_SSOauth_public_Test extends PHPUnit_Framework_TestCase
{

	public function test_the_constructor_sets_the_plugin_name_and_the_version_got_as_parameter()
	{
		init_mocked_option_container(); 
		$e = new eDemo_SSOauth_Public('plugin_name','version');
		$this->assertEquals(
			$e->plugin_name,
			'plugin_name');
		$this->assertEquals(
			$e->version,
			'version');
	}
	
	public function test_login_button_shortcode()
    {
		init_mocked_option_container();
		update_option('eDemoSSO_serviceURI','serviceURI');
		update_option('eDemoSSO_callback_uri','callback_uri');
		update_option('eDemoSSO_appkey','appkey');
		update_option('eDemoSSO_secret','secret');
		update_option('eDemoSSO_sslverify','sslverify');
		$e = new eDemo_SSOauth_Public('plugin_name','version');
		$atts=array(
				"logged_in_class" => "logged_in",
				"logged_out_class" => "logged_out");
		$content='content';
		global $mock_data;
		$_SERVER['REQUEST_URI']='request_uri';
		$mock_data["is_user_logged_in"]=true;
        $html = $e->shortcode_login_button($atts,$content);
        $this->assertEquals(
        	$html,
        	'<a class="logged_in" href="https://serviceURI/ada/v1/oauth2/auth?response_type=code&client_id=appkey&redirect_uri=site_url%2Fcallback_uri%3FSSO_action%3Dlogin%26_wpnonce%3Dlogin%26wp_redirect%3Drequest_uri">content</a>'
			);
    }

}

