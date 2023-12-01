<?php
/*******************************************************
  NAME:  userDelete.php
  CALLED FROM: userMaint.php
  TRANSFERS control to: userMaint.php
  PURPOSE: deletes a user
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - delete users");
	header("location:userMaint.php");
	exit;
}
/********************************************************/
$userLogin = "";
if (isset($_GET['ID'])) $userLogin = clean_input($_GET['ID']);

$hashPassword = password_hash($defaultPWD, PASSWORD_DEFAULT);

#$sql = "UPDATE users SET password=?, defaultPWD=1 WHERE userName=?";
$sql = "SELECT authlevel FROM users WHERE username = ?";

if ($stmt = $db->prepare($sql)) {
   $stmt->bind_param("s", $userLogin);
   $stmt->execute();
   $stmt->bind_result($result);
   $stmt->fetch();
   $stmt->close();
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

if ($result === "ADMIN") {
	$_SESSION['notify'] = array("type"=>"warning", "message"=>"You cannot delete ADMIN users.");
	header("Location: userMaint.php");
	exit;
}

$sql = "DELETE FROM users WHERE username = ?";

if ($stmt = $db->prepare($sql)) {
   $stmt->bind_param("s", $userLogin);
   $stmt->execute();
   $stmt->close();
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

//FIXME For some reason, this notify message does not always display! Others do.
$_SESSION['notify'] = array("type"=>"success", "message"=>"User \"$userLogin\" has been deleted.");
header("Location: userMaint.php");

?>

