from django.urls import path,re_path
from django.views.static import serve
from django.conf import settings

from . import views

urlpatterns = [
    path('',views.index,name="index"),
    path('create', views.create, name='create'),
    path('sandbox', views.sandbox, name='sandbox'),
    path('view/<uuid>', views.view, name='view'),
    path('report', views.report, name='report'),
    re_path(r'^static/(?P<path>.*)$', serve,{'document_root': settings.STATIC_ROOT})
]
