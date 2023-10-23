<?php
/*******************************************************
 * cardAdd.php 
 * Called from: patronEdit.php
 * This adds a new Library Card to the patron.
 * It should not happen if there is already a card with ACTIVE status 
 *	(patronEdit.php should prevent this)
 * This returns to patronEdit.php with a message upon success. 
 ********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Add Card");
	header("location:main.php");
}
/********************************************************/

$patronID = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
//This should never happen, but we have to make sure that there is a patronID
if (strlen($patronID) == 0) header("Location:".$_SERVER['HTTP_REFERER']);
echo $patronID;
$sql = "INSERT INTO libraryCard ( patronID, status) VALUES(?, 'ACTIVE');";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $patronID);
	$stmt->execute();
	$stmt->close();
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

$_SESSION['notify'] = array("type"=>"success", "message"=>"Library card added.");

header("location:patronEdit.php?ID=$patronID");
