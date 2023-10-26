<?php
/*******************************************************
 * This is the AJAX version of cardStatus.php
 * cardStatus2.php 
 * Called from: patronEdit.php
 * This updates the Library Card status

 * INCOMPLETE:  I just need to copy the large chunk of code that 
   generates that library card table from "patronEdit.php". (line 310-355)
   as well as the code that grabs all of the cards for the patron.

* and then in patronEdit, I'll need to change it so that it uses AJAX 
 ********************************************************/
session_start();
require_once('common.php');

$barcode=$stCode=$patronID="";
$barcode = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

//This should never happen, but we have to make sure that there is a valid barcode
if (strlen($barcode) != 10) {
	echo 'ERROR Invalid barcode.';
	return;
}

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
		echo 'ERROR Invalid status.';
		return;
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

echo "SUCCESS Library card status changed.";
#$_SESSION['notify'] = array("type"=>"success", "message"=>"Library Card status changed.");

