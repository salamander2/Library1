<?php
/*******************************************************
* cardStatus.php 
* called from: patronEdit.php
*
*  ** AJAX Version **
*
* This updates the Library Card status
********************************************************/
session_start();
require_once('common.php');

if ($_SESSION["authkey"] != AUTHKEY) {
	echo "LOGOUT";
	return;
}

$barcode=$stCode="";
$barcode = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

//This should never happen, but we have to make sure that there is a valid barcode
if (strlen($barcode) != 10) {
	echo 'ERROR Invalid barcode.';
	return;
}

if (isset($_GET['status'])) $stCode = $_GET['status'];

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

