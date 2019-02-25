const io = require('socket.io-client')
const socket = io.connect('http://localhost:3000/')
 
if(process.argv.length != 4) {
  console.log('name and channel missing')
   process.exit()
}
console.log('Logging as ' + process.argv[2] + ' on ' + process.argv[3])
var inputUser = {
  name: process.argv[2], 
};

socket.on('message', function(msg) {
  console.log(msg.from,"[", msg.channel!==undefined?msg.channel:'Default',"]", "says:\n", msg.message);
});

socket.on('error', function (err) {
  console.log('received socket error:')
  console.log(err)
})



socket.emit('register', JSON.stringify(inputUser));
socket.emit('message', JSON.stringify({ msg: "hello" }));
socket.emit('join', process.argv[3]);//ps: you should keep your channels private
socket.emit('message', JSON.stringify({ channel: process.argv[3], msg: "hello channel" }));
socket.emit('message', JSON.stringify({ channel: "test", msg: "i own you" }));
