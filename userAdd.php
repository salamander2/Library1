<?php
/*******************************************************
* userAdd.php
* Called from userMaint.php
* Calls: returns to userMaint.php upon success or failure
*
*  ** AJAX Version **
*
* Purpose: adds new user to database
* 		validates input and adds record
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Add User");
	header("location:main.php");
}
/********************************************************/

	$username=$fullname="";
	if (isset($_POST['username'])) $username = clean_input($_POST['username']);
	if (isset($_POST['fullname'])) $fullname = clean_input($_POST['fullname']);
	$authlevel = $_POST['authlevel'];

	$valid = array("ADMIN","STAFF","PATRON","PUBLIC");
	//If the authlevel is invalid, set it to PATRON
	if (! in_array($authlevel, $valid)) $authlevel = "PATRON";

	if ($username == "") {
		$_SESSION['notify'] = array("type"=>"error", "message"=>"Missing username.");
		header("location:userMaint.php");
	}
	if ($fullname == "") {
		$_SESSION['notify'] = array("type"=>"error", "message"=>"Missing fullname.");
		header("location:userMaint.php");
	}
		
	$password = password_hash($defaultPWD, PASSWORD_DEFAULT);
	$sql = "INSERT INTO users (username, fullname, authlevel, password) VALUES (?, ?, ?, ? )";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("ssss", $username, $fullname, $authlevel, $password);
		$stmt->execute();
		$stmt->close();
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}

	$_SESSION['notify'] = array("type"=>"success", "message"=>"User record has been added.");
	header("location:userMaint.php");
?>
