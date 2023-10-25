<?php
/*******************************************************
* userAdd.php
* Called from userMaint.php
* Calls: returns to userMaint.php upon success or failure
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

	//FIXME All validation still needs to be done here and on patronEdit.php
	$username=$fullname="";
	if (isset($_POST['username'])) $username = clean_input($_POST['username']);
	if (isset($_POST['fullname'])) $fullname = clean_input($_POST['fullname']);
	$authlevel = $_POST['authlevel'];

	//FIXME It's slighty more robust to use teh actual values in an array. "MIN,ST" would be seen here as a valid string.
    if (strlen($authlevel) > 6 || strpos("ADMIN,STAFF,PATRON,PUBLIC", $authlevel) === false) $authlevel = "PATRON";

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
