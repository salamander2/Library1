<?php

# common.php has a set of common utility methods. 
# It also sets the Error reporting (to /var/log/apache2/error.log)
# It sets various session variables if they are not already set.
# and it links to config.php which has the variables needed to log in to MySQL.

/**********  ERROR REPORTING  **********/
// Development
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Production
/*
#error_reporting(0); ini_set('display_errors','0');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Same as error_reporting(E_ALL);
//ini_set('error_reporting', E_ALL);
*/

//And to print a message to the error log: which for web apps is /var/log/apach2/error.log
#error_log("There is something wrong!", 0);

require_once '../library.config.php';

/**********  SESSION VARIABLES  **********/
$userdata="";
if (isset($_SESSION["userdata"])) $userdata = $_SESSION["userdata"];

/**********  COMMON VARIABLES  **********/
$home="index.php"; 
$institution="Harwood";
#$directory="."; 

/**********  COMMON FUNCTIONS  **********/

function connectToDB() {
    $db = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if ($db->connect_errno) {
        echo "<script>";
        echo 'alert("Error connecting to database '.$database.'. Your connection has probably timed out. Please log in again");';
        echo "window.location='index.php';";
        echo "</script>";
        // header("Location: index.php");
#       echo "Failed to connect to MySQL database $database : " . mysqli_connect_error();
#       die("Program terminated");
    }
    //mysqli_query($db, "set names UTF8;");
    return $db;
}

// This is a legacy function.
// ONly use this for queries that do not use any variables. Otherwise SQL injection attacks can happen.
function runSimpleQuery($mysqli, $sql_) {
    $result = mysqli_query($mysqli, $sql_);
//  if (!$mysqli->error) {
//      printf("Errormessage: %s\n", $mysqli->error);
//  }

    // Check result. This shows the actual query sent to MySQL, and the error. Useful for debugging.
    if (!$result) {
       $message_  = 'Invalid query: ' . mysqli_error($mysqli) . "\n<br>";
       $message_ .= 'SQL: ' . $sql_;
       die($message_);
    }
    return $result;
}

function clean_html($string) {
    $string = trim(htmlspecialchars(addslashes($string)));
    #$string = trim(htmlentities(addslashes($string)));
    return $string;
}

function clean_input($string) {
    $string = trim(strip_tags(addslashes($string)));
    return $string;
}

