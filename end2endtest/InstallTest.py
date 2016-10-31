from selenium import webdriver
from unittest.case import TestCase
from selenium.webdriver.common.by import By
from UIProcedures import UIProcedures

class InstalltestTest(TestCase, UIProcedures):
    def setUp(self):
        self.driver = webdriver.Firefox()

    def tearDown(self):
        self.driver.close()
        
    def assertFieldValue(self, fieldId, value):
        element = self.driver.find_element_by_id(fieldId)
        self.assertEqual(value,element.get_property('value'))

    def test_install_page(self):
        self.login_as_admin()
        self.configureSSO()
        self.wait_on_element_text(By.ID, "wpbody-content", "Options updated", 30)
        self.assertFieldValue("EdemoSSO_serviceURI", "/sso_callback")
        self.assertFieldValue("EdemoSSO_appname", "testapp")
        self.assertFieldValue("EdemoSSO_appkey", "f7354fa2-811f-48b6-9be9-359c5d99d6a4")
        self.assertFieldValue("EdemoSSO_secret", "testsecret")
        self.assertFieldValue("EdemoSSO_callback_uri", "/callback/uri")

        self.assertIn("Options updated",self.driver.find_element_by_id("wpbody-content").text)

