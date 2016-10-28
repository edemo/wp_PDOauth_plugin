from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from unittest.case import TestCase
import pdb
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
import sys
import time


class element_to_be_useable(object):
    def __init__(self, locator):
        self.locator = locator

    def __call__(self, driver):
        try:
            element = driver.find_element(*self.locator)
        except:
            #print(sys.exc_info())
            element = None
        if element:
            try:
                displayValue=element.value_of_css_property('display')
                displayok = displayValue in ('block', 'inline','inline-block')
                displayed = element.is_displayed()
                enabled = element.is_enabled()
                if displayed and enabled and displayok:
                    return element
            except StaleElementReferenceException:
                pass
        return False

class UIActions(object):
    def waitUntilElementEnabled(self, fieldId):
        element = WebDriverWait(self.driver, 100).until(element_to_be_useable((By.ID,fieldId)))
        return element

    def wait_on_element_text(self, by_type, element, text, timeout=20):
        WebDriverWait(self.driver, timeout).until(
            EC.text_to_be_present_in_element(
                (by_type, element), text)
        )

    def fillInField(self, fieldId, value):
        element = self.waitUntilElementEnabled(fieldId)
        element.clear()
        element.send_keys(value)


class UIProcedures(UIActions):
    def login_as_admin(self):
        self.driver.get("http://blog.example.com:8080/wp-login.php")
        self.waitUntilElementEnabled("user_login").send_keys("admin")
        self.waitUntilElementEnabled("user_pass").send_keys("admin")
        self.waitUntilElementEnabled("wp-submit").click()
        self.waitUntilElementEnabled("wpadminbar")

    def configureSSO(self):
        self.driver.get("http://blog.example.com:8080/wp-admin/options-general.php?page=edemosso")
        self.fillInField("EdemoSSO_appname", "testapp")
        self.fillInField("EdemoSSO_appkey", "testkey")
        self.fillInField("EdemoSSO_secret", "testsecret")
        self.fillInField("EdemoSSO_callback_uri", "/callback/uri")
        self.waitUntilElementEnabled("EdemoSSO_allowBind").click()
        self.waitUntilElementEnabled("EdemoSSO_allowLogin").click()
        self.waitUntilElementEnabled("EdemoSSO_update").click()

class InstalltestTest(TestCase, UIProcedures):
    def setUp(self):
        self.driver = webdriver.Firefox()

    def tearDown(self):
        self.driver.close()
        
    def test_install_page(self):
        self.login_as_admin()
        self.configureSSO()
        self.wait_on_element_text(By.ID, "wpbody-content", "Options updated", 30)
        self.assertIn("Options updated",self.driver.find_element_by_id("wpbody-content").text)

