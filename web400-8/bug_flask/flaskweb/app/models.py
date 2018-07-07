from . import db, login_manager
from werkzeug.security import generate_password_hash, check_password_hash
from flask_login import UserMixin
from datetime import datetime

class User(UserMixin, db.Model):
	__tablename__ = 'User'
	id = db.Column(db.Integer, primary_key=True)
	username = db.Column(db.String(64), unique=True)
	password_hash = db.Column(db.String(128))
	role = db.Column(db.Boolean, default=False)
	location = db.Column(db.String(64))
	about_me = db.Column(db.Text())

	posts = db.relationship('Post', backref='author', lazy='dynamic')
	plans = db.relationship('Plan', backref='author', lazy='dynamic')

	def is_administrator(self):
		return self.role

	@property
	def password(self):
		raise AttributeError('password is not readable attribute')

	@password.setter
	def password(self, password):
		self.password_hash = generate_password_hash(password)

	def verify_password(self, password):
		return check_password_hash(self.password_hash, password)

	def __repr__(self):
		return '<User %r>' % self.username

class Post(db.Model):
	__tablename__ = 'Posts'
	id = db.Column(db.Integer, primary_key=True)
	bug_url = db.Column(db.Text)
	body = db.Column(db.Text)
	timestamp = db.Column(db.DateTime, index=True, default=datetime.utcnow)
	flag = db.Column(db.Boolean, default=False)
	author_id = db.Column(db.Integer, db.ForeignKey('User.id'))

class Plan(db.Model):
	__tablename__ = 'Plans'
	id = db.Column(db.Integer, primary_key=True)
	content = db.Column(db.Text)
	timestamp = db.Column(db.DateTime, index=True, default=datetime.utcnow)
	author_id = db.Column(db.Integer, db.ForeignKey('User.id'))


@login_manager.user_loader
def load_user(user_id):
	return User.query.get(int(user_id))