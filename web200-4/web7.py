#!/usr/bin/python 
# coding:utf8

__author__ = 'niexinming'

import cherrypy
import urllib2
import redis

class web7:
    @cherrypy.expose
    def index(self):
        return "<script> window.location.href='/web7/input';</script>"
    @cherrypy.expose
    def input(self,url="",submit=""):
        file=open("index.html","r").read()
        reheaders=""
        if cherrypy.request.method=="GET":
            reheaders=""
        else:
            url=cherrypy.request.params["url"]
            submit=cherrypy.request.params["submit"]
            try:
                for x in urllib2.urlopen(url).info().headers:
                    reheaders=reheaders+x+"<br>"
            except Exception,e:
                reheaders="错误"+str(e)
        file=file.replace("<?response?>",reheaders)
        return file
    @cherrypy.expose
    def login(self,password="",submit=""):
        pool = redis.ConnectionPool(host='127.0.0.1', port=6379)
        r = redis.Redis(connection_pool=pool)
        re=""
        file=open("login.html","r").read()
        if cherrypy.request.method=="GET":
            re=""
        else:
            password=cherrypy.request.params["password"]
            submit=cherrypy.request.params["submit"]
            if r.get("admin")==password:
                re="flag{this_is_func}"
            else:
                re="Can't find admin:"+password+",fast fast fast....."
        file=file.replace("<?response?>",re)
        return file
cherrypy.quickstart(web7(),'/web7')


