<?php

require_once 'tests/fake_lib.php';

require_once 'eDemo-SSOauth/includes/class_edemo-ssoauth_communication.php';

class eDemo_SSOauth_communication__Test extends PHPUnit_Framework_TestCase
{

    public function test_the_constructor_initialise_the_class_variables()
    {
        $e = new eDemo_SSOauth();
        $this->assertEquals(
        	$e->get_plugin_name(),
        	"eDemo-SSOauth");

			
    }

}
