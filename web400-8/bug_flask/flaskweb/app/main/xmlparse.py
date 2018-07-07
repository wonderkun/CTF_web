import xml.sax

class MyContentHandler(xml.sax.ContentHandler):
	def __init__(self,plansList):
		xml.sax.ContentHandler.__init__(self)
		self.currentData = ""
		self.chars = plansList

	def startElement(self, tag, attrs):
		self.currentData = tag

	def endElement(self, tag):
		if tag == "content":
			self.currentData = ""

	def characters(self, content):
		if self.currentData == "content":
			self.chars.append(content)