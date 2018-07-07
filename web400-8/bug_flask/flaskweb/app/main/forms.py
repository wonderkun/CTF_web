from flask_wtf import FlaskForm
from wtforms import StringField, SubmitField, TextAreaField, FileField
from wtforms.validators import Length, URL, DataRequired

class EditProfileForm(FlaskForm):
	location = StringField("Location", validators=[Length(0,60)])
	about_me = StringField("About me")
	submit = SubmitField("Submit")

class BugForm(FlaskForm):
	bug_url = StringField("bug's URL", validators=[URL(message='This Not a URL'),Length(0,2048)])
	body = TextAreaField("Please describe the problem in detail")
	submit = SubmitField("Submit")

class PlanForm(FlaskForm):
	content = TextAreaField("Write your plan")
	submit = SubmitField("Submit")

class ImportForm(FlaskForm):
	myplans = FileField("upload your plans", validators=[DataRequired()])
	submit = SubmitField("Import")