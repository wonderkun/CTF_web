import requests
from time import sleep
from bs4 import BeautifulSoup


headers = {"Accept":"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Upgrade-Insecure-Requests":"1","User-Agent":"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0","Connection":"close","Accept-Language":"ja","Accept-Encoding":"gzip, deflate"}
cookies = {"session":".eJwtj7uuAjEMBf8lNUX8ihN-ZuXYjkBIIO1CdXX_nS0oTjcjzfkr29rzuJXre__kpWz3KNcCTkINRWtrA-agqkN0kSTHhIqjAnp6B8C2Ggyz7GBjRXdhB_CJmFXiJGRxQ0YhFWA-p65qLaMPwWQmX0BIhNEMZkUKQy-X4se-tvfrkc-zJ2yadaUIM1fuztGZyGBJpVUV1GeMiaf3OXL_nSj_X3joPWs.DiDEtA.7NXKmCSsjk0cu8nJ_DgiVHXRy5w"}

print(cookies)
while 1:
	rp = requests.get("http://ip:4455/88e6955d09f5ab8e75a96706507b04a5", headers=headers, cookies=cookies)
	soup = BeautifulSoup(rp.content, 'html.parser')
	lists = soup.find_all(class_='post-url')
	print(lists)
	try:
		for url in lists:
			url = url.text
			print(url)
			if url.startswith('http://ip:4455/'):
				rp = requests.get(url=url, headers=headers, cookies=cookies, timeout=5)
				print(len(rp.text))
			else:
				requests.get(url=url, timeout=3)
	except Exception as e:
		print(e)
	sleep(1)
