from django.db import models

# Create your models here.

class Code(models.Model):
    name = models.CharField(max_length=200)
    lang = models.CharField(max_length=50)
    text = models.CharField(max_length=1024)
    uuid = models.CharField(max_length=32)

class Report(models.Model):
    page = models.CharField(max_length=200)