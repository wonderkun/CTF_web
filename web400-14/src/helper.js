var exports = module.exports = {
    clone: function(obj) {

        if (typeof obj !== 'object' ||
            obj === null) {

            return obj;
        }

        var newObj;
        var cloneDeep = false;

        if (!Array.isArray(obj)) {
            if (Buffer.isBuffer(obj)) {
                newObj = new Buffer(obj);
            }
            else if (obj instanceof Date) {
                newObj = new Date(obj.getTime());
            }
            else if (obj instanceof RegExp) {
                newObj = new RegExp(obj);
            }
            else {

                var proto = Object.getPrototypeOf(obj);
                if (proto &&
                    proto.isImmutable) {

                    newObj = obj;
                }
                else {
                    newObj = Object.create(proto);
                    cloneDeep = true;
                }
            }
        }
        else {
            newObj = [];
            cloneDeep = true;
        }
     
        if (cloneDeep) {
            var keys = Object.getOwnPropertyNames(obj);

            for (var i = 0; i < keys.length; ++i) {
                var key = keys[i];
                var descriptor = Object.getOwnPropertyDescriptor(obj, key);
                if (descriptor &&
                    (descriptor.get ||
                     descriptor.set)) {

                    Object.defineProperty(newObj, key, descriptor);
                }
                else {
                    newObj[key] = this.clone(obj[key]);
                }
            }
        }

        return newObj;
    }, 
    validUser: function(inp) {
        var block = ["source","port","font","country",
                     "location","status","lastname"];
        if(typeof inp !== 'object') {
            return false;
        } 

        var keys = Object.keys( inp);
        for(var i = 0; i< keys.length; i++) {
            key = keys[i];
            
            if(block.indexOf(key) !== -1) {
                return false;
            }
        }

        var r =/^[a-z0-9]+$/gi;
        if(inp.name === undefined || !r.test(inp.name)) {
            return false;
        }

        return true;
    },
    getAscii: function(message) {
        var e = require('child_process');
        return e.execSync("cowsay '" + message + "'").toString();
    }
}