#!/usr/bin/env python
# -*- coding:utf-8 -*-

import selenium
from selenium import webdriver  
from selenium.webdriver.common.keys import Keys  
from selenium.common.exceptions import WebDriverException
import os 
import time 
import requests

# only for vps
# from pyvirtualdisplay import Display

# display = Display(visible=0, size=(800,800))
# display.start()


url = "http://localhost:9999/"
username = "hctf_admin_LoRexxar2e23"
password = "123456"


while 1:
	try:
		
		# only for windows
		# chromedriver = "C:\Users\lorex\AppData\Local\Google\Chrome\Application\chromedriver.exe"  
		# os.environ["webdriver.chrome.driver"] = chromedriver  
		# browser = webdriver.Chrome(chromedriver) 
		
		browser = webdriver.Chrome()
		print "[INFO] start new round..."

		browser.set_page_load_timeout(10)
		browser.set_script_timeout(10)

		out_url = url + '/logout.php'
		browser.get(out_url)

		a_url = url+'/login.php'	
		browser.get(a_url)
		time.sleep(1)

		elem = browser.find_element_by_name("user")
		elem.clear()
		elem.send_keys(username)
		elem = browser.find_element_by_name("pass")
		elem.clear()
		elem.send_keys(password)
		elem = browser.find_element_by_name("submit")
		elem.click()

		l_url = url+'/api/getmess.php'
		browser.get(l_url)
		while 1:
			try: 
				browser.switch_to_alert().accept()  # 接收一切弹框，防止浏览器被弹框
			except selenium.common.exceptions.NoAlertPresentException:
				break
		source = browser.page_source

		if "Nothing" not in source:
			print time.strftime("%Y-%m-%d %X", time.localtime())
			print "[info] Reading now..."
			time.sleep(4)

			d_url = url+'/api/clearmess.php'
			browser.get(l_url)

			browser.quit()
			time.sleep(1)

		if "This is not where you should come" in source:
			print time.strftime("%Y-%m-%d %X", time.localtime())
			print "[info] bot admin check error..."
			browser.quit()
			time.sleep(2)

		else:
			print time.strftime("%Y-%m-%d %X", time.localtime())
			print "[info] no unread messages..."
			browser.quit()
			time.sleep(2)
			# exit(0)

	except Exception as e: 
		print "[error] "+str(e)
		time.sleep(2)
		browser.quit()
