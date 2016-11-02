from UIActions import UIActions
from selenium.webdriver.common.by import By

class UIProcedures(UIActions):
    def login_as_admin(self):
        self.driver.get("https://blog.example.com:8080/wp-login.php")
        self.waitUntilElementEnabled("user_login").send_keys("admin")
        self.waitUntilElementEnabled("user_pass").send_keys("admin")
        self.waitUntilElementEnabled("wp-submit").click()
        self.waitUntilElementEnabled("wpadminbar")

    def addSsoWidget(self):
        self.driver.get("https://blog.example.com:8080/wp-admin/widgets.php?widgets-access=on")
        self.waitUntilElementEnabled("widgets-left")
        ssoauth=self.driver.find_element_by_xpath("//a[contains(@href, 'base=edemo_ssoauth_login_widget')]")
        ssoauth.click()
        title = self.waitUntilElementEnabled("widget-edemo_ssoauth_login_widget-__i__-title")
        title.clear()
        title.send_keys("SSO")
        sidebarPosition=self.driver.find_element_by_xpath("//select[@name='sidebar-1_position']")
        for option in sidebarPosition.find_elements_by_tag_name('option'):
            if option.text == '1':
                option.click()
                break
        self.waitUntilElementEnabled("savewidget").click()
        self.wait_on_element_text(By.ID, "message", "Changes saved.", 50)
        
        
    def configureSSO(self):
        self.driver.get("https://blog.example.com:8080/wp-admin/options-general.php?page=edemosso")
        self.fillInField("EdemoSSO_serviceURI", "sso.edemokraciagep.org")
        self.fillInField("EdemoSSO_appname", "testapp")
        self.fillInField("EdemoSSO_appkey", "f7354fa2-811f-48b6-9be9-359c5d99d6a4")
        self.fillInField("EdemoSSO_secret", "e2etest")
        self.setCheckBox("EdemoSSO_allowBind")
        self.setCheckBox("EdemoSSO_allowLogin")
        self.setCheckBox("EdemoSSO_allowRegister")
        self.waitUntilElementEnabled("EdemoSSO_update").click()
        self.wait_on_element_text(By.ID, "wpbody-content", "Options updated", 30)

    def wp_logout(self):
        self.driver.get("https://blog.example.com:8080/wp-login.php?action=logout")
        element=self.driver.find_element_by_xpath("//a")
        element.click()
        
    def workaroundPermalinkProblem(self):
        self.driver.get("https://blog.example.com:8080/wp-admin/options-permalink.php")
        element=self.driver.find_element_by_xpath("//input[@value='/%year%/%monthnum%/%day%/%postname%/']")
        element.click()
        button = self.driver.find_element_by_id("submit")
        button.click()
        self.wait_on_element_text(By.ID, "setting-error-settings_updated", "Permalink structure updated.", 30)

    def loginWithSSO(self):
        self.driver.get("https://blog.example.com:8080/")
        registerLink=self.driver.find_element_by_xpath("//a[text()='Register with SSO']")
        registerLink.click()
        self.fillInField("LoginForm_email_input", "mag+blog@magwas.rulez.org")
        self.fillInField("LoginForm_password_input", "3l3k Th3 T3st3r")
        button=self.driver.find_element_by_xpath("//button[text()='Bejelentkezés']")
        button.click()
        self.waitUntilElementEnabled("wp-admin-bar-root-default")
        avatar = self.driver.find_element_by_xpath("//a[text()='Howdy, SSO user']")
        self.assertEqual(avatar.text,"Howdy, SSO user")
