from UIActions import UIActions
from selenium.webdriver.common.by import By
import pdb

class UIProcedures(UIActions):
    def login_as_admin(self):
        self.driver.get("https://blog.example.com:8080/wp-login.php")
        self.waitUntilElementEnabled("user_login").send_keys("admin")
        self.waitUntilElementEnabled("user_pass").send_keys("admin")
        self.waitUntilElementEnabled("wp-submit").click()
        self.waitUntilElementEnabled("wpadminbar")

    def configureSSO(self):
        self.driver.get("https://blog.example.com:8080/wp-admin/options-general.php?page=edemosso")
        self.fillInField("EdemoSSO_serviceURI", "sso.edemokraciagep.org")
        self.fillInField("EdemoSSO_appname", "testapp")
        self.fillInField("EdemoSSO_appkey", "f7354fa2-811f-48b6-9be9-359c5d99d6a4")
        self.fillInField("EdemoSSO_secret", "e2etest")
        self.waitUntilElementEnabled("EdemoSSO_allowBind").click()
        self.waitUntilElementEnabled("EdemoSSO_allowLogin").click()
        self.waitUntilElementEnabled("EdemoSSO_allowRegister").click()
        self.waitUntilElementEnabled("EdemoSSO_update").click()
        pdb.set_trace()
        self.wait_on_element_text(By.ID, "wpbody-content", "Options updated", 30)

    def workaroundPermalinkProblem(self):
        self.driver.get("https://blog.example.com:8080/wp-admin/options-permalink.php")
        element=self.driver.find_element_by_xpath("//input[@value='/%year%/%monthnum%/%day%/%postname%/']")
        element.click()
        button = self.driver.find_element_by_id("submit")
        button.click()
        self.wait_on_element_text(By.ID, "setting-error-settings_updated", "Permalink structure updated.", 30)
