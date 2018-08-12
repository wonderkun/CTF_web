import os,time
from flask import Flask, render_template, request,jsonify
from flask_sqlalchemy import SQLAlchemy
import jwt
import string
from Crypto import Random
from Crypto.Hash import SHA
from Crypto.Cipher import PKCS1_v1_5 as Cipher_pkcs1_v1_5
from Crypto.Signature import PKCS1_v1_5 as Signature_pkcs1_v1_5
from Crypto.PublicKey import RSA
import base64
import cgi
from urllib import quote
from urllib import unquote
import hashlib
import json


app = Flask(__name__)
app.secret_key = os.urandom(24)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:////tmp/pastebin.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = True
db = SQLAlchemy(app)
random_generator = Random.new().read
rsa = RSA.generate(1024, random_generator)

class User(db.Model):
    __tablename__ = 'user'
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.Text)
    password = db.Column(db.Text)
    priv = db.Column(db.Text)
    key = db.Column(db.Text)
    token = db.Column(db.Text)

    def __init__(self, username, password, priv, key, token):
        self.username = username
        self.password = password
        self.priv = priv
        self.key = key
        self.token = token

    def __repr__(self):
        return '<User id:{}, username:{}, password:{}, priv:{}, key:{}, token:{}>'.format(self.id, self.username, self.password, self.priv, self.key, self.token)

class Link(db.Model):
    __tablename__ = 'link'
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.Text)
    link = db.Column(db.Text)
    content = db.Column(db.Text)

    def __init__(self, username, link, content):
        self.username = username
        self.link = link
        self.content = content

    def __repr__(self):
        return '<Link id:{}, username:{}, link:{}, content:{}'.format(self.id, self.username, self.link, self.content)

def defense(input_str):
    for c in input_str:
        if c not in string.letters and c not in string.digits:
            return False
    return True

def getmd5(str):
    m = hashlib.md5()
    m.update(str)   
    return m.hexdigest()

def getname(str, value):
    try:
        tmp = str.split('.')[1]
        while True:
            if len(tmp)%4 == 0:
                break
            tmp = tmp + "="
        username = json.loads(base64.b64decode(tmp))['name']
    except:
        return False
    user = User.query.filter_by(username=username,).first()
    if not user:
        return False
    key_name = user.key
    with open('./pubkey/' + key_name + '.pem', 'r') as f:
        secret = f.read()
        # print(secret)
    try:
        de_user = jwt.decode(str, secret)
    except Exception as e:
        # print(e)
        return False
    # print(de_user)
    name = de_user[value]
    return name


@app.route("/")
def index():
    return render_template("index.html")

@app.route("/user")
def user():
    return render_template("user.html")

@app.route("/reg",methods=['POST'])
def reg():
    regname = request.form['regname']
    if regname == "admin":
        return jsonify(result=False,)
    regpass = request.form['regpass']
    if len(regname) < 5 or len(regname) > 20 or len(regpass) < 5 or len(regpass) > 20 or not defense(regname) or not defense(regpass) or User.query.filter_by(username=regname,).first():
        return jsonify(result=False,)
    private_pem = rsa.exportKey()
    public_pem = rsa.publickey().exportKey()  
    key_name = getmd5(regname + regpass)
    with open('./key/' + key_name + '.pem', 'w') as f:
        f.write(private_pem)
    with open('./pubkey/' + key_name + '.pem', 'w') as f:
        f.write(public_pem)
    if regname == "admin":
        priv = "admin"
    else:
        priv = "other"
    token = jwt.encode({'name': regname,'priv': priv}, private_pem, algorithm='RS256')
    user = User(regname, regpass, priv, key_name, token)
    db.session.add(user)
    db.session.commit()
    return jsonify(result=True,)
@app.route("/login",methods=['POST'])
def login():
    username = request.form['name']
    password = request.form['pass']
    if len(username) < 5 or len(username) > 20 or len(password) < 5 or len(password) > 20 or not defense(username) or not defense(password):
        return jsonify(result=False,)
    user = User.query.filter_by(username=username,password=password,).first()
    if not user:
        return jsonify(result=False,)
    return jsonify(result=True,token=user.token,)

@app.route("/paste",methods=['POST'])
def paste():
    content = unquote(request.form['content'])
    if len(content)>300:
        return jsonify(result=False,)
    try:
        post_token = request.headers['Authorization'][7:]
    except:
        return jsonify(result=False,)
    name = getname(post_token, "name")
    if name == False:
        return jsonify(result=False,)
    if name == "admin":
        return jsonify(result=False,)
    link = getmd5(os.urandom(24))
    content = cgi.escape(content)
    li = Link(name, link, content)
    db.session.add(li)
    db.session.commit()
    return jsonify(result=True,link=name+":"+link)

@app.route("/list",methods=["GET"])
def list():
    try:
        post_token = request.headers['Authorization'][7:]
    except:
        return jsonify(result=False,)
    name = getname(post_token, "name")
    if name == False:
        return jsonify(result=False,)
    priv = getname(post_token, "priv")
    if priv == False:
        return jsonify(result=False,)
    if priv == "other":
        li = Link.query.filter_by(username=name,)
        links = []
        for lin in li:
            links.append(name + ":" + lin.link)
        return jsonify(result=True,username=name,links=links)
    if priv == "admin":
        li = Link.query.filter_by()
        links = []
        for lin in li:
            links.append(lin.username + ":" + lin.link)
        return jsonify(result=True,username="admin",links=links)

@app.route("/pubkey/<key>",methods=["GET"])
def getkey(key):
    try:
        with open('./pubkey/' + key + '.pem', 'r') as f:
            secret = f.read()
        return jsonify(result=True,pubkey=secret,)
    except:
        return jsonify(result=False,)

@app.route("/text/<link>",methods=["GET"])
def getcontent(link):
    name = link.split(":")[0]
    links = link.split(":")[1]
    if defense(name) == False or defense(links) == False:
        return jsonify(result=False,)
    li = Link.query.filter_by(username=name,link=links,).first()
    if not li:
        return jsonify(result=False,)
    return jsonify(result=True,content=li.content,)


app.run(debug=False,host='0.0.0.0')