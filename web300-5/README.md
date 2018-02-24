### WriteUp 

此题目本来是是个crypto，但是由于在web中也常考次知识点，所以收集在这里。

跟之前的CBC反转的题目有所不同，次题目多了一个检验，在伪造数据的时候，还需要能够通过校验，所以难度就加大了。

##### 加密过程
```python
    def encrypt(self,raw):
        raw = pad(raw)
        raw = md5(raw).digest() + raw
     
        iv = Random.new().read(BS)
        cipher = AES.new(self.key,AES.MODE_CBC,iv)
     
        return ( iv + cipher.encrypt(raw) ).encode("hex")
```
通过返回，我们可以获取此次加密所用的 iv 以及  encrypt(md5sum + data+padding)

解密的时候，验证了校验是否正确：

```python
def decrypt(self,enc):
        enc = enc.decode("hex")
     
        iv = enc[:BS]
        enc = enc[BS:]
     
        cipher = AES.new(self.key,AES.MODE_CBC,iv)
        blob = cipher.decrypt(enc)
     
        checksum = blob[:BS]
        data = blob[BS:]
     
        if md5(data).digest() == checksum:
            return unpad(data)
        else:
            return
```
常规的方式是伪造iv进行CBC反转，得到任意的校验，但是却没办法伪造校验部分的密文，来得到任意的明文。

要同时控制住，校验部分的密文和明文部分的密文显然是不太可行的。 

**正确的做法是：先构造出想要的明文，再去伪造IV得到想要的检验值**

先注册用户为：
```
admin\x0b\x0b\x0b\x0b\x0b\x0b\x0b\x0b\x0b\x0b\x0bwonderkun
```
然后去掉加密结果的后16字节，也就是后32位，此时解密的后得到的数据就是admin，只是因为校验不对没办法通过验证，通过修改IV进行cbc反转伪造校验位就可以了。

poc就不再写了。