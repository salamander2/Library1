## Programmer Documentation

* Most of the documentation is in the code.
* The top of each php file describes its function and where it is called from.
* php is used for most data manipulation, and most of the formatting of data for display, but also for data validation.     
Javascript is used for data validation and Ajax.
* Security and authentication is descibed in a [separate document](1_Security.md)

--------------------

:star: I'm not using `if(isset($_POST['submit'])) {...}` a whole lot. Instead I'm calling other PHP pages to do things like update the database.
This is because each page is getting longer as I'm adding more JS to do input validations and I'm making the UI look better. 

:star: All php query strings are created using the variable name $sql

### Displaying Error Message notifications
:star: We need a method to display messages (normally errors) from both PHP and Javascript.
:star: There are different ways to pop up error messages / success messages.  [Styled Notifications](https://github.com/salamander2/styled-notifications) is a ready made library, however, the popups are in the corners of the screen.      
Another alternative is to write my own in JS - and this is what I've done.  The message appears in a &lt;div&gt; with the id of "notif_container". The styling of this is in the CSS file. Note that there are two ways you can implement this (by changing the CSS): (i) the notification appears wherever the &lt;div&gt; is placed in the HTML page, (ii) the notification has an absolute position of 250px from the top and floats above the page. I'm using (i), but it can easily be changed.

**Usage**

* The JS function is `displayNotification(type, message, duration = 3500)`  where "type" is one of "error", "warning", "info", "success". "message" is the message to be displayed. Duration is the length time it will be displayed (ms).
* The HTML must have these lines which should be located at the place in the page where you want the error message to appear (normally fairly close to the top).
    `<!-- This is the JAVASCRIPT error message --><div id="notif_container"></div>`
* The HTML must have these lines in order to display errors originating from php. Add this code to the HTML right after `<div id="notif_container"></div>`    
`<!-- // This is the PHP error message. The php variables are not JS variables, so we need to add \"  -->`    
`<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>`

* Calling the notification function:
 * In JS just add a line like this: `displayNotification("error", "You must include a username");`
 * In php we are using an array for 'type' and 'message': `$notify = array("type"=>"error", "message"=>"");`
It is declared in common.php and thus is set to these values for each page.
To make an error popup via php, just set $notify['message'] to a non-empty string. Change 'type' as well if needed.
 
If you want to make the notifications "hover" over the page instead of taking up space in the page, uncomment the line indicated in resources/library.js (in the displayNotification function). 

The functionality to have multiple messages at the same time does not yet exist.  "Styled notifications" can do this nicely.

-- to be continued --
