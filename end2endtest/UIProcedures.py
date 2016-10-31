from UIActions import UIActions

class UIProcedures(UIActions):
    def login_as_admin(self):
        self.driver.get("http://blog.example.com:8080/wp-login.php")
        self.waitUntilElementEnabled("user_login").send_keys("admin")
        self.waitUntilElementEnabled("user_pass").send_keys("admin")
        self.waitUntilElementEnabled("wp-submit").click()
        self.waitUntilElementEnabled("wpadminbar")

#5 | f7354fa2-811f-48b6-9be9-359c5d99d6a4 | e2etest    | e2etest | https://blog.example.com:8080 | 


    def configureSSO(self):
        self.driver.get("http://blog.example.com:8080/wp-admin/options-general.php?page=edemosso")
        self.fillInField("EdemoSSO_serviceURI", "/sso_callback")
        self.fillInField("EdemoSSO_appname", "testapp")
        self.fillInField("EdemoSSO_appkey", "f7354fa2-811f-48b6-9be9-359c5d99d6a4")
        self.fillInField("EdemoSSO_secret", "testsecret")
        self.fillInField("EdemoSSO_callback_uri", "/callback/uri")
        self.waitUntilElementEnabled("EdemoSSO_allowBind").click()
        self.waitUntilElementEnabled("EdemoSSO_allowLogin").click()
        self.waitUntilElementEnabled("EdemoSSO_update").click()


