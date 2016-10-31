<?php

require_once 'tests/fake_lib.php';

require_once 'eDemo-SSOauth/includes/class_edemo-ssoauth.php';

class eDemo_SSOauth_baseTest extends PHPUnit_Framework_TestCase
{

    public function test_get_plugin_name_gets_plugin_name()
    {
        $e = new eDemo_SSOauth();
        $this->assertEquals(
        	$e->get_plugin_name(),
        	"eDemo-SSOauth");
    }

    public function test_get_loader_gets_the_loader()
    {
        $e = new eDemo_SSOauth();
        $loader = $e->get_loader();
        $this->assertEquals(
        	get_class($loader),
        	"eDemo_SSOauth_Loader");
    }
    
    public function test_version()
    {
        $e = new eDemo_SSOauth();
        $version = $e->get_version();
        $this->assertEquals(
        	$version,
        	"0.0.1");
    }

}
