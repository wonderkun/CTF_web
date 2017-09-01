"use strict";
var http = require('http');

(function(){
    http.createServer(function (req, res) {
            res.writeHead( 200, { "Content-Type" : "text/html;charset=utf-8", "X-XSS-Protection" : "0" } );
            res.end( '<html><head><title>' + req.headers["host"] + '</title></head><body>It works!</body></html>' );
        
    }).listen(80);
    console.log( "Running server on port 80" );
})();