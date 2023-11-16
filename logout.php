<?php
/*******************************************************
* logout.php
* called from : a variety of places. Logout button, any authentication error
* calls: 
* transfers control to: index.php
*
* Used to logout the user (destroys the session variables)
********************************************************/
session_start();
// Use both unset and destroy for compatibility
// with all browsers and all versions of PHP.
session_unset();
session_destroy();
header("Location: index.php");
