<?php
/*******************************************************
  NAME:  userResetPWD.php
  CALLED FROM: userMaint.php
  TRANSFERS control to: userMaint.php
  PURPOSE: resets user password to default
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - reset passwords");
	header("location:userMaint.php");
	exit;
}
/********************************************************/
$userLogin = "";
if (isset($_GET['ID'])) $userLogin = clean_input($_GET['ID']);

$hashPassword = password_hash($defaultPWD, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password=?, defaultPWD=1 WHERE userName=?";

if ($stmt = $db->prepare($sql)) {
   $stmt->bind_param("ss", $hashPassword, $userLogin);
   $stmt->execute();
   $stmt->close();
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

$_SESSION['notify'] = array("type"=>"success", "message"=>"Password has been reset");

header("Location: userMaint.php");

?>

