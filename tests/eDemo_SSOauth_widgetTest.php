<?php

require_once 'tests/fake_lib.php';

require_once 'eDemo-SSOauth/includes/class_edemo-ssoauth_base.php';
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

}
