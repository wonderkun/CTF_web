from datetime import datetime
from flask import render_template, redirect, url_for, flash, abort, make_response, request, session
from flask_login import login_required, current_user
from . import main
from .. import db
from ..models import User, Post, Plan
from  .forms import EditProfileForm, BugForm, PlanForm, ImportForm
from .xmlparse import MyContentHandler
import xml.sax
import hashlib
import random

@main.route('/', methods=['GET', 'POST'], defaults={'path': ''})
@main.route('/<path:path>')
def index(path):
	return render_template('index.html')

@main.route('/user/<username>')
@login_required
def user(username):
	user = User.query.filter_by(username=username).first()
	if user is None:
		abort(404)
	return render_template('user.html', user=user)

@main.route('/<regex("edit(.*)"):url>/', methods=['GET', 'POST'])
@login_required
def edit(url):
	form = EditProfileForm()
	if form.validate_on_submit():
		current_user.location = form.location.data
		current_user.about_me = form.about_me.data
		db.session.add(current_user)
		flash('You Profile has been updated')
		return redirect(url_for('.user', username=current_user.username))
	form.location.data = current_user.location
	form.about_me.data = current_user.about_me
	return render_template('edit.html', form=form)


@main.route('/<regex("post_bug(.*)"):url>/', methods=['GET', 'POST'])
@login_required
def post_bug(url):
	form = BugForm()
	if form.validate_on_submit():
		post = Post(body=form.body.data, bug_url=form.bug_url.data, author=current_user._get_current_object())
		db.session.add(post)
		return redirect(url_for('.post_bug', url="post_bug"))
	posts = current_user.posts.order_by(Post.timestamp.desc()).all()
	return render_template('post.html', form=form, posts=posts)

@main.route('/<regex("write_plan(.*)"):url>/', methods=['GET', 'POST'])
@login_required
def write_plan(url):
	form = PlanForm()
	if form.validate_on_submit():
		plan = Plan(content=form.content.data, author=current_user._get_current_object())
		db.session.add(plan)
		return redirect(url_for('.write_plan', url="write_plan"))
	plans = current_user.plans.order_by(Plan.timestamp.desc()).all()
	return render_template('plan.html', form=form, plans=plans)

@main.route('/88e6955d09f5ab8e75a96706507b04a5', methods=['GET', 'POST'])
@login_required
def Check_the_message():
	if current_user.role == False:
		abort(404)
	posts = Post.query.filter_by(flag=False).order_by(Post.timestamp.asc()).limit(50)
	urls = []
	for post in posts:
		urls.append(post.bug_url)
		post.flag = True
		db.session.add(post)
	return render_template('/88e6955d09f5ab8e75a96706507b04a5.html', urls=urls)


@main.route('/<regex("export(.*)"):url>/', methods=['GET', 'POST'])
@login_required
def export(url):
	content = """<?xml version="1.0" encoding="UTF-8"?>\n<plans>\n"""
	plans = current_user.plans.order_by(Plan.timestamp.desc()).all()
	for plan in plans:
		content += "\t<plan>\n"
		content += "\t\t<content>" + plan.content +"</content>\n"
		content += "\t</plan>\n"
	content += "</plans>"
	response = make_response(content)
	response.headers["Content-Disposition"] = "attachment; filename=myplans.xml"
	return response

@main.route('/<regex("import_and_export(.*)"):url>/', methods=['GET', 'POST'])
@login_required
def import_and_export(url):
	form = ImportForm()
	if form.validate_on_submit():
		my_xml = request.files['myplans'].read()
		my_xml = str(my_xml, encoding = "utf-8")

		if 'http' in my_xml.lower():
			flash('Hacker, absolutely big hacker!!!')
			return redirect(url_for('.import_and_export', url="import_and_export"))

		if len(my_xml) > 1500:
			flash('别太长了，短期计划就好了~')
			return redirect(url_for('.import_and_export', url="import_and_export"))

		if my_xml.upper().count('ENTITY') > 1:
			flash('出题人表示很抱歉，最多只能一个ENTITY')
			return redirect(url_for('.import_and_export', url="import_and_export"))

		plansList = []

		try:
			parser = MyContentHandler(plansList)
			xml.sax.parseString(my_xml, parser)
		except Exception as e:
			return render_template('xml_error.html')

		for i in plansList:
			plan = Plan(content=i, author=current_user._get_current_object())
			db.session.add(plan)

		flash('Import success')
		return redirect(url_for('.write_plan', url="write_plan"))
	else:
		return render_template('import_and_export.html', form=form)