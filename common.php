<?php

# common.php has a set of common utility methods. 
# It also sets the Error reporting (to /var/log/apache2/error.log)
# It sets various session variables if they are not already set.
# and it links to config.php which has the secure variables needed to log in to MySQL.

/**********  ERROR REPORTING  **********/
// Development
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
// These values are reset each time a page loads.

$home="index.php"; 
$institution="Harwood";
$libCode='0748';
#$directory="."; 
$defaultPWD="CairParavel";

//Create the notification array and set it to an empty message. If there is a message from the previous page, set it now.
$notify = array("type"=>"error", "message"=>"","duration"=>"");
if(isset($_SESSION["notify"])) {
	 $notify = $_SESSION["notify"];
	 //and prevent the same message from displaying next time the page loads
	 unset($_SESSION["notify"]);
}


/********** COMMON PHP HEADER CODE *******/
/* EXCEPT for the login page (index.php) and any PAC related pages */
$allowed = array("index.php","PAC.php","bibFindPAC.php");
$page = basename($_SERVER['PHP_SELF']);
if (false === array_search($page,$allowed)) { 
	if ($_SESSION["authkey"] != AUTHKEY)  {
		header("Location:$home?ERROR=Failed%20Auth%20Key"); 
		exit;
	}
	$db = connectToDB();  //PAC has special startup
}

$page = basename($_SERVER['PHP_SELF']);
if ($page !== "index.php" && $page !== "PAC.php") {
	# Check authorization (ie. that the user is logged in) or go back to login page
	if ($_SESSION["authkey"] != AUTHKEY)  {
		header("Location:$home?ERROR=Failed%20Auth%20Key"); 
		exit;
	}
	$db = connectToDB();
}


/**********  COMMON FUNCTIONS  **********/

/********************************
* Connect to the database specified in the config file
********************************/
function connectToDB() {
    $db = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if ($db->connect_errno) {
        echo "<script>";
        echo 'alert("Error connecting to database '.$database.'. Your connection has probably timed out. Please log in again");';
        echo "window.location='index.php';";
        echo "</script>";
        // header("Location: index.php");
    }
    //mysqli_query($db, "set names UTF8;");
    return $db;
}

/*
// This is a legacy function.
// ONly use this for queries that do not use any variables. Otherwise SQL injection attacks can happen.
function runSimpleQuery($mysqli, $sql_) {
    $result = mysqli_query($mysqli, $sql_);
	//  if (!$mysqli->error) {
	//      printf("Errormessage: %s\n", $mysqli->error);
	//  }

    // Check result. This shows the actual query sent to MySQL, and the error. Useful for debugging.
    if (!$result) {
		die("Invalid query: " . mysqli_error($mysqli) . "\n<br>SQL: $sql_");
    }
    return $result;
}
*/

/*************************************************
This ensures a standard header on all pages.
The header HTML is in "pageHeader.html"
The only things that change are where the back button redirects to 
and whether or not to show the Administer button
*************************************************/
function loadHeader(String $backHref="main.php"){
	global $institution;
	$text = file_get_contents("pageHeader.html");
	$text = str_replace("BACK", $backHref,$text);
	$text = str_replace("INSTITUTION", $institution,$text);
	echo $text;
//TODO add in ELSE statement in case the html file is missing. Then just print this standard code.
}

/*************************************************
* Simple code to sanitize strings. 
  AddSlashes is a pain. The would have to be removed before being displayed.
  It's unnecessary since I'm using prepared statements.
*
* filter_var() FILTER_SANITIZE_STRING  ---- DEPRECATED. USE htmlspecialchars()
* strip_tags()   Strip HTML and PHP tags from a string, as well as HTML comments
    Because strip_tags() does not actually validate the HTML, partial or broken tags can result in the removal of more text/data than expected. 
* htmlspecialchars Convert special characters to HTML entities
     &amp; &lt; &gt;  " and ' -- options to allow/disable quotes
* htmlspecialchars() is good enough and better than using htmlentities()
* addslashes  -- quote string with slashes
**************************************************/
function clean_input($string) {
    $string = trim(htmlspecialchars($string));
    //$string = trim(strip_tags(addslashes($string)));
    //$string = trim(htmlspecialchars(addslashes($string)));
    //$string = trim(htmlentities(addslashes($string)));
    return $string;
}

