<?php

require_once 'tests/fake_lib.php';

require_once 'eDemo-SSOauth/includes/class_edemo-ssoauth_communication.php';

class eDemo_SSOauth_communication__Test extends PHPUnit_Framework_TestCase
{

    public function test_the_constructor_initialise_the_class_variables()
    {
        init_mocked_option_container();
		update_option('eDemoSSO_serviceURI','serviceURI');
		update_option('eDemoSSO_callback_uri','callback_uri');
		update_option('eDemoSSO_appkey','appkey');
		update_option('eDemoSSO_secret','secret');
		update_option('eDemoSSO_sslverify','sslverify');
		
		$e = new eDemo_SSOauth_com('plugin_name','version');
        $this->assertEquals(
        	invokeProperty($e, 'serviceURI'),
        	"serviceURI");
        $this->assertEquals(
        	invokeProperty($e, 'plugin_name'),
        	"plugin_name");
        $this->assertEquals(
        	invokeProperty($e, 'version'),
        	"version");
        $this->assertEquals(
        	invokeProperty($e, 'callbackURL'),
        	"site_url/callback_uri");
        $this->assertEquals(
        	invokeProperty($e, 'appkey'),
        	"appkey");
        $this->assertEquals(
        	invokeProperty($e, 'secret'),
        	"secret");
        $this->assertEquals(
        	invokeProperty($e, 'sslverify'),
        	"sslverify");
			
    }

}
