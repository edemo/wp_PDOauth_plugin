<?php

require_once 'tests/fake_lib.php';

require_once 'eDemo-SSOauth/includes/class_edemo-ssoauth_base.php';
require_once 'eDemo-SSOauth/includes/class_edemo-ssoauth.php';
require_once 'eDemo-SSOauth/includes/class_edemo-ssoauth_widgets.php';

class eDemo_SSOauth_login_widget__Test extends PHPUnit_Framework_TestCase
{

    public function test_constructor_sets_the_properties_from_plugin_options()
    {
		init_mocked_option_container(); 

        $e = new eDemo_SSOauth_login_widget();

        $this->assertEquals(
        	$e->widget_vars['allowBind'],
        	false);
		$this->assertEquals(
        	$e->widget_vars['allowRegister'],
        	false);
		$this->assertEquals(
        	$e->widget_vars['allowLogin'],
        	false);
    }

   public function test_serviceuri_exists()
   {
	init_mocked_option_container(); 
	update_option('eDemoSSO_serviceURI','sso.edemokraciagep.org.lehetne');
	$opt = get_option('eDemoSSO_serviceURI');
	$this->assertEquals('sso.edemokraciagep.org.lehetne',$opt);
   }


   public function test_site_url_exists()
   {
	$this->assertEquals(
		'/login.html',
	eDemo_SSOauth::SSO_SITE_URL);
   }

   public function test_services_url_can_be_assembled()
   {
	init_mocked_option_container(); 
	update_option('eDemoSSO_serviceURI','sso.edemokraciagep.org.lehetne');
	$this->assertEquals(
		'sso.edemokraciagep.org.lehetne/login.html',
	(get_option('eDemoSSO_serviceURI').eDemo_SSOauth::SSO_SITE_URL));
   }
}
