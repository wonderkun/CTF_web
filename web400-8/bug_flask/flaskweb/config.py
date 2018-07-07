import os

basedir = os.path.abspath(os.path.dirname(__file__))

class Config:
	SECRET_KEY=os.environ.get('SECRET_KEY') or 'axni2SDlcCmBZyU9nWvyDDXHRfPYOkJ5NcVCDHQDGpJvxBxoqvTQFPtRYXAfelMJ5ARK42DQekYShCTIHaYpjKUqOKXdniGm8Mne3kzzCtY70Z4ies9oFTdK8G4mki8u';
	SQLALCHEMY_COMMIT_ON_TEARDOWN = True
	SQLALCHEMY_TRACK_MODIFICATIONS = True
	
	@staticmethod
	def init_app(app):
		pass

class DevelopmntConfig(Config):
	Debug = 0
	SQLALCHEMY_DATABASE_URI = os.environ.get('SQLALCHEMY_DATABASE_URI') or 'sqlite:///' + os.path.join(basedir, 'data.sqlite')

class ProductionConfig(Config):
	SQLALCHEMY_DATABASE_URI = os.environ.get('SQLALCHEMY_DATABASE_URI') or 'sqlite:///' + os.path.join(basedir, 'product.sqlite')

config = {
	'development' : DevelopmntConfig,
	'product' : ProductionConfig,
}