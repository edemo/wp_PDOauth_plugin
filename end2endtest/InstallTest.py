from selenium import webdriver
from unittest.case import TestCase
from UIProcedures import UIProcedures
import os

class InstallTest(TestCase, UIProcedures):
    def setUp(self):
        profile_directory = os.path.join(os.path.dirname(__file__),"..", "etc", "firefox-profile")
        profile = webdriver.FirefoxProfile(profile_directory)
        profile.accept_untrusted_certs = True
        self.driver = webdriver.Firefox(firefox_profile=profile)

    def tearDown(self):
        self.driver.close()

    def test_install_page(self):
        self.login_as_admin()
        self.addSsoWidget()
        self.workaroundPermalinkProblem()
        self.configureSSO()
        self.assertFieldValue("EdemoSSO_serviceURI", "sso.edemokraciagep.org")
        self.assertFieldValue("EdemoSSO_appname", "testapp")
        self.assertFieldValue("EdemoSSO_appkey", "f7354fa2-811f-48b6-9be9-359c5d99d6a4")
        self.assertFieldValue("EdemoSSO_secret", "e2etest")
        self.assertSelected("EdemoSSO_allowBind")
        self.assertSelected("EdemoSSO_allowLogin")
        self.assertSelected("EdemoSSO_allowRegister")
        self.assertIn("Options updated",self.driver.find_element_by_id("wpbody-content").text)
        self.wp_logout()
        self.loginWithSSO()

