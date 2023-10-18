<?php
/*******************************************************
 * cardStatus.php 
 * Called from: patronEdit.php
 * This updates the Library Card status
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

$barcode = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
//This should never happen, but we have to make sure that there is a valid barcode
if (strlen($barcode) == 0) header("Location:".$_SERVER['HTTP_REFERER']);

$stCode=$patronID="";
if (isset($_GET['status'])) $stCode = $_GET['status'];
if (isset($_GET['patron'])) $patronID = $_GET['patron'];

$status = "";
switch($stCode) {
	case "L":
		$status = "LOST";
		break;
	case "R":
	case "A":
		$status = "ACTIVE";
		break;
	default:
		header("Location:".$_SERVER['HTTP_REFERER']);
}

$sql = "UPDATE libraryCard SET status=? WHERE barcode=?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("si", $status, $barcode );
	$stmt->execute();
	$stmt->close();
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

$newDate = date('Y-m-d', strtotime('+ 1 year'));
//also update the expiry date for cards that are being renewed.
if ($stCode == "R") {
	$sql = "UPDATE libraryCard SET expiryDate=? WHERE barcode=?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("si", $newDate, $barcode );
		$stmt->execute();
		$stmt->close();
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
}

$_SESSION['success_message'] = "Library Card status changed. ";

header("location:patronEdit.php?ID=$patronID");
