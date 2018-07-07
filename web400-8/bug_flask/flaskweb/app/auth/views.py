from flask import render_template, redirect, request, url_for, flash
from flask_login import login_user,  logout_user, current_user
from .forms import LoginForm, RegistForm
from ..models import User
from . import auth
from .. import db

@auth.route('/login', methods=['GET', 'POST'])
def login():
	form = LoginForm()
	if form.validate_on_submit():
		user = User.query.filter_by(username = form.username.data).first()
		if user is not None and user.verify_password(form.password.data):
			login_user(user, form.remember_me.data)
			return redirect(request.args.get('next') or url_for('main.index'))
		flash('Invaild username or passsword')
	return render_template('auth/login.html', form=form)

@auth.route('/logout', methods=['GET', 'POST'])
def logout():
	logout_user()
	flash('You have log out')
	return redirect(url_for('main.index'))


@auth.route('/regist', methods=['GET', 'POST'])
def regist():
	form = RegistForm()
	if form.validate_on_submit():
		user = User(username=form.username.data, password=form.password.data)
		db.session.add(user)
		flash('Now you can log in!')
		return redirect(url_for('auth.login', url='login'))
	return render_template('auth/register.html', form=form)