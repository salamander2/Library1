<?php
//Used to logout the user (destroys the session variables)
session_start();
// use both unset and destroy for compatibility
// with all browsers and all versions of PHP
session_unset();
session_destroy();
header("Location: index.php");
