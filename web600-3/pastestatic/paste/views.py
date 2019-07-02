from django.shortcuts import render,reverse,redirect
from django.views.decorators.csrf import csrf_exempt


# Create your views here.
from django.http import HttpResponse
from .forms import CodeForm
from .models import Code
import hashlib
import time


def index(request):    
    return redirect(reverse("create"))

def getMd5(content):

    md5 = hashlib.md5()
    md5.update(content.encode("utf-8"))
    md5.update( str(time.time()).encode("utf-8")) 
    return md5.hexdigest()


def makeHeader(response):

    # print(dir(response))
    response['X-Frame-Options']= 'ALLOWALL'
    response["Content-Security-Policy"] = "script-src 'nonce-3ra+TpXGQImBZW8NNdCJ2A==' 'unsafe-eval' 'strict-dynamic' https: http:; base-uri 'none'; object-src 'none'"
    # 假装nonce 是动态的，不想写了 。。
    return response

def create(request):    
    if request.method == 'POST':
        codeForm = CodeForm(request.POST)
        if codeForm.is_valid():
            # print(codeForm.name)
            name = codeForm.cleaned_data.get('name')
            lang = codeForm.cleaned_data.get('lang')
            text = codeForm.cleaned_data.get('text')
            # print(text)
            uuid = getMd5(name+lang+text)
            code = Code(name=name,lang=lang,text=text,uuid=uuid)
            # print(code)
            code.save()
            if code.id :
                return makeHeader(redirect( reverse('view',args=(uuid,) )))

            else:
                return  makeHeader(HttpResponse("create error."))
            # print(uuid)
            # return "ok"

    return render(request,'paste/index.html')
def view(request,uuid):

    code = Code.objects.get(uuid=uuid)
    # print(code.text)
    return makeHeader(render(request,'paste/view.html',context={"code":code,'uuid':uuid}))

def sandbox(request):
    return  makeHeader(render(request,'paste/sandbox.html'))

@csrf_exempt
def report(request):
    if request.method == "POST":
        page = request.POST.get("page","")
        return makeHeader(HttpResponse("report {} ok, admin will open it soon!.".format(page)))

    return makeHeader(redirect( reverse('create') ))