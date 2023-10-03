<?php
/*******************************************************
* patronUpdate.php 
* Called from patronEdit.php
* This updates the patron record, validation ... by JS
* also some here.
* This returns to patronEdit.php with a message upon success. 
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
	$address=$city=$prov=$postalCode="";
	$phone=$email="";
	if (isset($_POST['firstname'])) $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
	$lastname = clean_input($_POST['lastname']);
	$address= clean_input($_POST['address']);
	$city = clean_input($_POST['city']);
	$prov = clean_input($_POST['prov']);
	$postalCode = clean_input($_POST['postalCode']);
	$phone = clean_input($_POST['phone']);
	$email = clean_input($_POST['email']);

	$sql = "UPDATE patron SET firstname=?, lastname=?, address=?, city=?, prov=?, postalCode=?, phone=?, email=? WHERE id=?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("ssssssssi", $firstname, $lastname, $address, $city, $prov, $postalCode, $phone, $email, $patronID );
		$stmt->execute();
		$stmt->close();
	} else {
		$message_  = 'Invalid query: ' . mysqli_error($schoolDB) . "\n<br>";
		$message_ .= 'SQL: ' . $sql;
		die($message_);
	}

	$_SESSION['success_message'] = "Patron record has been updated.";

header("location:patronEdit.php?ID=$patronID");
