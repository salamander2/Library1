<?php
/*******************************************************
********************************************************/
session_start();
require_once('common.php');

# Check authorization (ie. that the user is logged in) or go back to login page
if ($_SESSION["authkey"] != AUTHKEY) { 
    header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
}

# Check user access level for the page (ie. Does the user have appropriate permissions to do this?)

$db = connectToDB();
$error_message = "";

$patronID = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

$firstname=$lastname="";
if (isset($_POST['firstname'])) $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
$frm_lastname = clean_input($_POST['lastname']);
$frm_address= clean_input($_POST['address']);
$frm_city = clean_input($_POST['city']);
$frm_phone = clean_input($_POST['phone']);

$sql = "UPDATE patron SET firstname=?, lastname=?, address=?, city=?, phone=? WHERE id=?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("sssssi", $firstname, $frm_lastname, $frm_address, $frm_city, $frm_phone, $patronID );
	$stmt->execute();
	$stmt->close();
} else {
	$message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
	$message_ .= 'SQL: ' . $sql;
	die($message_);
}
$_SESSION['success_message'] = "Patron record has been updated.";
header("location:patronEdit.php?ID=$patronID");

