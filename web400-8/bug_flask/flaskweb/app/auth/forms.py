from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField, BooleanField, SubmitField, ValidationError
from wtforms.validators import Required, Length, Regexp, EqualTo
from .. import models

class LoginForm(FlaskForm):
	username = StringField('Username', validators=[Required(), Length(5,20)])
	password = PasswordField('Password', validators=[Required()])
	remember_me = BooleanField('Keep')
	submit = SubmitField('Login In')

class RegistForm(FlaskForm):
	username = StringField('Username', validators=[Required(), Length(5,20), Regexp('^[A-Za-z][A-Za-z0-9_.]*$', 0, 'Username must hava only letters, numbers, dots or underscores')])
	password = PasswordField('Password', validators=[Required(), EqualTo('repassword', message='Password must match')])
	repassword = PasswordField('Repassword', validators=[Required()])
	submit = SubmitField('Register')

	def vaildate_username(self, field):
		if User.query.filter_by(username=field.data).firset():
			raise ValidationError('Username already in use')