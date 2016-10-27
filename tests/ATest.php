<?php

include 'tests/fake_lib.php';

include 'edemo_sso_auth.php';

class ATest extends PHPUnit_Framework_TestCase
{

    public function testFail()
    {
        $this->assertEquals('a','a');
    }

    public function test_add_js_adds_pagescript()
    {
    	global $mock_data;
        $e = new eDemoSSO();
        $e->add_js();
        $this->assertEquals(
        	$mock_data["script_queue"]["pagescript"],
        	"http://plugins.url/".getcwd()."/edemo_sso_auth.php/edemo_sso_auth.js");
    }

    public function test_user_has_SSO_returns_false_if_user_meta_have_empty_string()
    {
    	global $mock_data;
    	$mock_data["users"]["test1"]=array(eDemoSSO::USERMETA_ID => '');
        $e = new eDemoSSO();
        $this->assertFalse($e->user_has_sso('test1'));
    }

    public function test_user_has_SSO_returns_true_if_user_meta_have_nonempty_value()
    {
    	global $mock_data;
    	$mock_data["users"]["test1"]=array(eDemoSSO::USERMETA_ID => 'something');
        $this->assertTrue(eDemoSSO::user_has_sso('test1'));
    }
    
    public function test_register_widgets_registers_eDemoSSO_login() {
    	global $mock_data;
        $e = new eDemoSSO();
		$e->register_widgets();
		$this->assertEquals($mock_data["widgets"],'eDemoSSO_login');    	
    }

    public function test_get_refresh_token_gets_the_refresh_token_of_the_user_from_the_token_usermeta() {
    	global $mock_data;
    	$mock_data["users"]["test2"]=array(eDemoSSO::USERMETA_TOKEN => 'refreshtoken');
        $e = new eDemoSSO();
		$this->assertEquals($e->get_refresh_token('test2'),'refreshtoken');    	
    }

}
