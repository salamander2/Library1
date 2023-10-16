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

# Check authorization (ie. that the user is logged in) or go back to login page
if ($_SESSION["authkey"] != AUTHKEY) { 
	header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
}

#TODO  Check user access level for the page (ie. Does the user have appropriate permissions to do this?)

$db = connectToDB();
$error_message = "";

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
	$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
	$message_ .= 'SQL: ' . $sql;
	die($message_);
}

$_SESSION['success_message'] = "Library Card added.";

header("location:patronEdit.php?ID=$patronID");
