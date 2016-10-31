<?php

require_once 'tests/fake_lib.php';

include 'eDemo-SSOauth/includes/class_edemo-ssoauth_base.php';
include 'eDemo-SSOauth/admin/class_edemo-ssoauth_admin.php';

class eDemo_SSOauth_AdminTest extends PHPUnit_Framework_TestCase
{
	public function test__constructor()
	{
		$e = new eDemo_SSOauth_Admin('plugin_name','version');
		$this->assertEquals(
			$e->plugin_name,
			'plugin_name');
		$this->assertEquals(
			$e->version,
			'version');

	}
	
	public function test_update_SSO_options()
    {
		global $mock_data;
		$mock_data["options"]["eDemoSSO_serviceURI"]["value"]='';
		$mock_data["options"]["eDemoSSO_sslverify"]["value"]=false;
		$mock_data["options"]["eDemoSSO_appkey"]["value"]='';
		$mock_data["options"]["eDemoSSO_secret"]["value"]='';
		$mock_data["options"]["eDemoSSO_appname"]["value"]='';
		$mock_data["options"]["eDemoSSO_allowBind"]["value"]=false;
		$mock_data["options"]["eDemoSSO_allowRegister"]["value"]=false;
		$mock_data["options"]["eDemoSSO_allowLogin"]["value"]=false;
		$mock_data["options"]["eDemoSSO_default_role"]["value"]='';
		$mock_data["options"]["eDemoSSO_hide_adminbar"]["value"]=false;
		$mock_data["options"]["eDemoSSO_needed_assurances"]["value"]='';
		$mock_data["options"]["eDemoSSO_callback_uri"]["value"]='';
		$_POST=['EdemoSSO_serviceURI'        => 'serviceURI',
				'EdemoSSO_sslverify'         => true,
				'EdemoSSO_appkey'            => 'appkey',
				'EdemoSSO_secret'            => 'secret',
				'EdemoSSO_appname'           => 'appname',
				'EdemoSSO_allowBind'         => true,
				'EdemoSSO_allowRegister'     => true,
				'EdemoSSO_allowLogin'        => true,
				'EdemoSSO_default_role'      => 'default_role',
				'EdemoSSO_hide_adminbar'     => true,
				'EdemoSSO_needed_assurances' => 'needed_assurances',
				'EdemoSSO_callback_uri'      => 'callback_uri'];
		$e = new eDemo_SSOauth_Admin('plugin_name','version');
        invokeMethod($e, 'update_SSO_options');
        $this->assertEquals(
			$e->serviceURI,
			'serviceURI');
		$this->assertEquals(
			$e->sslverify,
			true);
		$this->assertEquals(
			$e->appkey,
			'appkey');
		$this->assertEquals(
			$e->secret,
			'secret');
		$this->assertEquals(
			$e->appname,
			'appname');
		$this->assertEquals(
			$e->allowBind,
			true);
		$this->assertEquals(
			$e->allowRegister,
			true);
		$this->assertEquals(
			$e->allowLogin,
			true);
		$this->assertEquals(
			$e->default_role,
			'default_role');
		$this->assertEquals(
			$e->hide_adminbar,
			true);
		$this->assertEquals(
			$e->needed_assurances,
			'needed_assurances');
		$this->assertEquals(
			$e->callback_uri,
			'callback_uri');
		
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_serviceURI"]["value"],
			'serviceURI');
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_sslverify"]["value"],
			true);
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_appkey"]["value"],
			'appkey');
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_secret"]["value"],
			'secret');
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_appname"]["value"],
			'appname');
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_allowBind"]["value"],
			true);
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_allowRegister"]["value"],
			true);
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_allowLogin"]["value"],
			true);
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_default_role"]["value"],
			'default_role');
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_hide_adminbar"]["value"],
			true);
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_needed_assurances"]["value"],
			'needed_assurances');
		$this->assertEquals(
			$mock_data["options"]["eDemoSSO_callback_uri"]["value"],
			'callback_uri');
    }
}

