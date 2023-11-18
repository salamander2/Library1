<?php
/*******************************************************
  NAME:  resetPWD.php
  CALLED FROM: userMaint.php

//TODO This program is not implemented yet. 
  It needs rewriting.

*  ** AJAX Version **

  PURPOSE: resets user password to default
********************************************************/
session_start();
require_once('common.inc.php');

/* -- Access is allowed to all levels of users */

$frm_login = $_GET['ID'];
#$frm_login = clean_input($_POST['ID']);

$hashPassword = password_hash($defaultPWD, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password=?, defaultPWD=1 WHERE login_name=?";

if ($stmt = $schoolDB->prepare($sql)) {
   $stmt->bind_param("ss", $hashPassword, $frm_login);
   $stmt->execute();
   $stmt->close();
} else {
   $message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
   $message_ .= 'SQL: ' . $sql;
   die($message_);
}

header("Location: userMaint.php");
exit;

?>
