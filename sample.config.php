<?php
#             SAMPLE FILE FOR .config.php 
# This file contains various constants for login and authentication. 
# It is called by common.php.

# It MUST NOT  be in the same folder that is served via Apache2 (open to the internet)
# This is written as a php file rather than a text .env file since it's a lot easier to read in constants this way.
#===========================================================================

/** Authentication for Database */
define('MYSQL_HOST','localhost');
define('MYSQL_USER','libDBxxxxx');
define('MYSQL_PASS','xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=');
define('MYSQL_DB','libraryDB');

/** Authentication key to validate each page */
define('AUTHKEY','xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');  

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. */
define( 'DB_COLLATE', '' );

//print_r(get_defined_constants(true));

# how to make a new hashed password
#$pwd = password_hash("password", PASSWORD_DEFAULT);
