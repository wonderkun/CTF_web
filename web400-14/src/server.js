var fs       = require('fs'); 
var server   = require('http').createServer()
var io       = require('socket.io')(server)
var clientManager = require('./clientManager')
var helper = require('./helper')
 
var defaultSettings = JSON.parse(fs.readFileSync('default_settings.json', 'utf8'));

function sendMessageToClient(client, from, message) {
    var msg = {
        from: from,
        message: message
    };

    client.emit('message', msg);
    console.log(msg)
    return true;
}

function sendMessageToChannel(channel, from, message) {
    var msg = {
        from: typeof from !== 'string' ? clientManager.getUsername(from): from,
        message: message,
        channel: channel
    };

    if(typeof from !== 'string') {
        if(!clientManager.isSubscribedTo(from, channel)) {
            console.log('Could not send message',msg,' from', 
                clientManager.getUsername(from),'to',channel,'because he is not subscribed.')
            return false;
        }
    }

    var clients = clientManager.getSubscribedToChannel(channel);
    
    for(var i = 0; i<clients.length;i++) {
        if(typeof from !== 'string') {
            if(clients[i].id == from.id) {
                continue;
            }
        }
        
        clients[i].emit('message', msg);
    }
    
    console.log(msg)
    return true;
}

io.on('connection', function (client) { 
    client.on('register', function(inUser) {
        try {
            newUser = helper.clone(JSON.parse(inUser))

            if(!helper.validUser(newUser)) {
                sendMessageToClient(client,"Server", 
                    'Invalid settings.')
                return client.disconnect();
            } 

            var keys = Object.keys(defaultSettings);
            for (var i = 0; i < keys.length; ++i) {
                if(newUser[keys[i]] === undefined) {
                    newUser[keys[i]] = defaultSettings[keys[i]]
                }
            } 

            if (!clientManager.isUserAvailable(newUser.name)) {
                sendMessageToClient(client,"Server", 
                    newUser.name + ' is not available')
                return client.disconnect(); 
            }
         
            clientManager.registerClient(client, newUser)
            return sendMessageToClient(client,"Server", 
                newUser.name + ' registered')
        } catch(e) { console.log(e); client.disconnect() }
    });

    client.on('join', function(channel) {
        try {
            clientManager.joinChannel(client, channel);
            sendMessageToClient(client,"Server", 
                "You joined channel", channel)

            var u = clientManager.getUsername(client);
            var c = clientManager.getCountry(client);

            sendMessageToChannel(channel,"Server", 
                helper.getAscii("User " + u + " living in " + c + " joined channel"))
        } catch(e) { console.log(e); client.disconnect() }
    });

    client.on('leave', function(channel) {
        try {
            client .join(channel);
            clientManager.leaveChannel(client, channel);
            sendMessageToClient(client,"Server", 
                "You left channel", channel)

            var u = clientManager.getUsername(client);
            var c = clientManager.getCountry(client);
            sendMessageToChannel(channel, "Server", 
                helper.getAscii("User " + u + " living in " + c + " left channel"))
        } catch(e) { console.log(e); client.disconnect() }
    });

    client.on('message', function(message) {
        try {
            message = JSON.parse(message);
            if(message.channel === undefined) {
                console.log(clientManager.getUsername(client),"said:", message.msg);
            } else {
                sendMessageToChannel(message.channel, client, message.msg);
            }
        } catch(e) { console.log(e); client.disconnect() }
    });

    client.on('disconnect', function () {
        try {
            console.log('client disconnect...', client.id)

            var oldclient = clientManager.removeClient(client);
            if(oldclient !== undefined) {
                for (const [channel, state] of Object.entries(oldclient.ch)) {
                    if(!state) continue;
                    sendMessageToChannel(channel, "Server", 
                        "User " + oldclient.u.name + " left channel");
                } 
            }
        } catch(e) { console.log(e); client.disconnect() }
    })

  client.on('error', function (err) {
    console.log('received error from client:', client.id)
    console.log(err)
  })
});

server.listen(3000, function (err) {
  if (err) throw err;
  console.log('listening on port 3000');
});
