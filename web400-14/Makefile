run:chat
	npm install 
	node client.js test test
chat :server
	docker run -d -p 3000:3000 chat
server:
	echo "building server ..."
	docker build -t chat ./
	