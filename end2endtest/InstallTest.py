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

class PDUnitTest(TestCase):
    def waitUntilElementEnabled(self, fieldId):
        element = WebDriverWait(self.driver, 100).until(element_to_be_useable((By.ID,fieldId)))
        return element

    def login_as_admin(self):
        self.driver.get("http://blog.example.com:8080/wp-login.php")
        self.waitUntilElementEnabled("user_login").send_keys("admin")
        self.waitUntilElementEnabled("user_pass").send_keys("admin")
        self.waitUntilElementEnabled("wp-submit").click()
        self.waitUntilElementEnabled("wpadminbar")

    def configureSSO(self):
        self.driver.get("http://blog.example.com:8080/wp-admin/options-general.php?page=edemosso")
        self.waitUntilElementEnabled("EdemoSSO_appname").send_keys("testapp")
        self.waitUntilElementEnabled("EdemoSSO_appkey").send_keys("testkey")
        self.waitUntilElementEnabled("EdemoSSO_secret").send_keys("testsecret")
        self.waitUntilElementEnabled("EdemoSSO_callback_uri").send_keys("/callback/uri")
        self.waitUntilElementEnabled("EdemoSSO_allowBind").click()
        self.waitUntilElementEnabled("EdemoSSO_allowLogin").click()
        self.waitUntilElementEnabled("EdemoSSO_update").click()

    def test_install_page(self):
        self.driver = webdriver.Firefox()
        self.login_as_admin()
        self.configureSSO()
        #there should have been something on the UI which shows whether actions are all done 
        time.sleep(2)
        self.assertIn("Options updated",self.driver.find_element_by_id("wpbody-content").text)
        self.driver.close()

