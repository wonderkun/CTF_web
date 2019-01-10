# 'post' challenge

This was one of the web challenges. Congrats to 0daysober and LC/BC for solving it!

## Run
To run it locally just do `docker-compose build && docker-compose up`.

## Exploit
There are several steps to successfully exploit it.

1. **nginx misconfiguration**

   You can leak the source code by navigating to `/uploads../`.
2. **arbitrary unserialize**

   After auditing the source code, you will find that the application unserializes strings from the database that have the prefix `$serializedobject$`. However, there is a check to prevent you from injecting strings of that form into the database. Luckily, MSSQL automatically converts full-width unicode characters to their ASCII representation. For example, if a string contains `0xEF 0xBC 0x84`, it will be stored as `$`.
3. **SoapClient SSRF**

   SoapClient can perform POST requests if any method is called on the object. The `Attachment` class implements a `__toString` method, which calls `open` on its `za` property. Serializing a SoapClient as `za` property will therefore lead to SSRF.
   
4. **SoapClient CRLF injection**

   There is a proxy running on `127.0.0.1:8080`, which you want to reach. Looking at the nginx configuration, it only accepts GET requests. However, SoapClient generates POST requests. But the `_user_agent` property of SoapClient is vulnerable to CRLF injection and thus you can perform a request splitting. By injection `\n\n` followed by a valid GET request, you can reach the proxy via a GET.

5. **miniProxy URL scheme bypass**

   Here I fucked up a bit. Intended solution was to bypass the check for http/https in miniProxy. This is possible by using `gopher:///...` as miniProxy only verifies http/https if the host is set. Unfortunately, you can also just bypass it with a 301 redirect to gopher... SAD! :D

6. **Connect to MSSQL via gopher**

   Final step was to connect to MSSQL via gopher using the credentials from the source code leak. The only thing to look out for here is that gopher automatically adds a `\r\n` to the request, which has to be accounted for when creating the MSSQL packets.

7. **Get flag**

   The miniProxy does not return the output of the request if the resulting URL is different from the requested URL (which it is in our case). Therefore to get the flag you want to copy it to one of your posts: `INSERT INTO posts (userid, content, title, attachment) VALUES (123, (select flag from flag.flag), "foo", "bar");-- -`. You can find your user id by sending a request to the application with the header `Debug: 1`.
   
To run the exploit do `python exploit.py` 
