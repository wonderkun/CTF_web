### Chall Desc:
Us at BCACTF love staircases so much that we decided to make a trivia quiz about them!   
Are you a staircase expert? Can you solve all three steps?  

Hint 1 of 1
The internet is a helpful resource

### Link to webpage:
http://web.bcactf.com:49207/

### Soln:

There is a webpage with a question asked.  
`According to the 2018 IRC standard, what is the maximum riser height for stair risers in inches?`  
The answer to that is 7.75 but we cannot input decimal into the submit box.
Therefore we just put the answer in the Url: http://web.bcactf.com:49207/7_75

![i6.png](https://github.com/ckc1404/CTF_writeups/blob/main/BCACTF/WEB/Three%20step%20trivia/i6.png)

The second question is `I've had to visit many different URLs to find information about the longest staircase in the world, and if I were to walk up it, I will feel like I'm in an entirely different location. How many steps are there in this never-ending flight of steps?`  
The answer is 11674 but now there is not submit box. Therefore, we again pass it in URL: http://web.bcactf.com:49207/11674

![i5.png](https://github.com/ckc1404/CTF_writeups/blob/main/BCACTF/WEB/Three%20step%20trivia/i5.png)

The third webpage has no submit button so we change the visibility in developer's tool and we get the flag.  

![i4.png](https://github.com/ckc1404/CTF_writeups/blob/main/BCACTF/WEB/Three%20step%20trivia/i4.png)

Thank you
