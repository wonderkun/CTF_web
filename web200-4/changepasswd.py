
__author__ = 'niexinming'
import redis
import random
import time
while(1):
    pool = redis.ConnectionPool(host='127.0.0.1', port=6379)
    r = redis.Redis(connection_pool=pool)
    password="".join(random.sample('abcdefghijklmnopqrstuvwxyz!@#$%^&*()',10))
    r.set("admin",password)
    time.sleep(3)