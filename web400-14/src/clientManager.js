var helper = require('./helper')
var exports = module.exports = {
    clients: {},
    getUserByClient: function(client) {
        return this.clients[client.id]
    },
    registerClient: function (client, user) {
        this.clients[client.id] = { 'c': client, 
                                    'u': user, 
                                    'ch': {} 
        };
    },
    removeClient: function (client) {
        var client_old = this.clients[client.id]
        if(client_old === undefined)
            return client_old

        delete client_old.c
        client_old = helper.clone(client_old)
        delete this.clients[client.id];
        return client_old
    },
    isUserAvailable: function (userName) {
        for (var [key, user] of Object.entries(this.clients)) {
          if(user.u.name == userName) {
            return false;
          }
        }
        return true;
    },
    getUsername: function (client) {
        return this.clients[client.id].u.name;
    },
    getLastname: function (client) {
        return this.clients[client.id].u.lastname;
    },
    getCountry: function (client) {
        return this.clients[client.id].u.country;
    },
    getLocation: function (client) {
        return this.clients[client.id].u.location;
    },
    getStatus: function (client) {
        return this.clients[client.id].u.status;
    },
    joinChannel: function (client, channel) {
        this.clients[client.id].ch[channel] = true; 
    },
    leaveChannel: function (client, channel) {
        this.clients[client.id].ch[channel] = false; 
    },
    getSubscribedToChannel: function(channel) {
        var subscribed = [];
        for (var [key, user] of Object.entries(this.clients)) {
            if(user.ch[channel] === true) {
                subscribed.push(user.c);
            }
        } 
        return subscribed;
    },
    isSubscribedTo: function(client, channel) {
        var user = this.getUserByClient(client)

        for (var [chs, state] of Object.entries(user.ch)) {
            if(state === true && chs === channel) {
                return true;
            }
        }

        return false;    
    },
};

