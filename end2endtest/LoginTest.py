from selenium import webdriver
from unittest.case import TestCase
from UIProcedures import UIProcedures


class LoginTest(TestCase, UIProcedures):
    def setUp(self):
        self.driver = webdriver.Firefox()

    def tearDown(self):
        self.driver.close()
