## Programmer Documentation

* Most of the documentation is in the code.
* The top of each php file describes its function and where it is called from.
* php is used for most data manipulation, and most of the formatting of data for display, but also for data validation.     
Javascript is used for data validation and Ajax.

--------------------

:star: I'm not using `if(isset($_POST['submit'])) {...}` a whole lot. Instead I'm calling other PHP pages to do things like update the database.
This is because each page is getting longer as I'm adding more JS to do input validations and I'm making the UI look better. 

:star: All php query strings are created using the variable name $sql

:star: **Error message displays** There are different ways to pop up error messages / success messages.  [Styled Notifications](https://github.com/salamander2/styled-notifications) is a ready made library, however, the popups are in the corners of the screen.  Another alternative is to write my own - and this is what I've done.  The message appears in a &lt;div&gt; with the id of "notification". The styling of this is in the CSS file. Note that there are two ways you can implement this (by changing the CSS): (i) the notification appears wherever the &lt;div&gt; is placed in the HTML page, (ii) the notification has an absolute position of 250px from the top and floats above the page. I'm using (i), but it can easily be changed.

-- to be continued --
