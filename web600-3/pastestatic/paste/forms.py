from django import forms

class CodeForm(forms.Form):
    name = forms.CharField(label='name', max_length=200)
    lang = forms.CharField(label='lang',max_length=50)
    text = forms.CharField(label='text',max_length=1024)
