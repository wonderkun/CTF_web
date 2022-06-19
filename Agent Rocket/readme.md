### Chall Desc:
I need to launch the BCA CTF ROCKET to the moon! Unfortunately I lost my launch code (flag).   
You can find the launch code in the control panel.  

Hint 1 of 2  
Have you tried looking at the website more CLOSELY?  

Hint 2 of 2  
You can't access the control panel from every device out there.  

### Link to webpage:
http://web.bcactf.com:49197/

### Soln:

If you look at the source page, u can find the login credentials: username: `admin`, password: `password`.  

> This is the comment we receive from the source code of admin page.  
```py 
The name of the device is "BCACTF Rocket Control Panel" in case you forgot.
``` 

Now, We can change the user agent and send the request to the admin page again using curl.

Command --> `curl -X POST http://web.bcactf.com:49197/ -d "username=admin&password=password" -A "BCACTF Rocket Control Panel"`

We get the flag in output of curl. The website is down now so cannot show the result.

### THE FLAG: 
bcactf{u53r_4g3Nt5_5rE_c0Ol_1023}

Thank you
