<?php
/*******************************************************
* patronUpdate.php 
* Called from patronEdit.php
*
* This updates the patron record. 
* It does not write HTML, it just updates the record and then returns to patronEdit.php
* Validation has been done by JS, but more is done here.
* This returns to patronEdit.php with a message upon success. 
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Patron Update");
	header("location:main.php");
	exit;
}
/********************************************************/

$id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

$firstname=$lastname=$address=$city=$prov=$postalCode=$phone=$email=$birthdate="";

/***** INPUT VALIDATION ******/
//All validation still needs to be done here (and on patronAdd.php).
if (isset($_POST['firstname']))	$firstname = clean_input($_POST['firstname']);
if (isset($_POST['lastname'])) 	$lastname = clean_input($_POST['lastname']);
if (isset($_POST['address'])) 	$address = clean_input($_POST['address']);
if (isset($_POST['city'])) 		$city = clean_input($_POST['city']);
if (isset($_POST['prov'])) 		$prov = clean_input($_POST['prov']);
if (isset($_POST['postalCode'])) $postalCode = clean_input($_POST['postalCode']);
if (isset($_POST['phone'])) 	$phone = clean_input($_POST['phone']);
if (isset($_POST['email'])) 	$email = clean_input($_POST['email']);
if (isset($_POST['birthdate'])) $birthdate = clean_input($_POST['birthdate']);
//Check for required values
if ($firstname == "" || $lastname == "" || $address == "" || $city == "" || $prov == "" || $postalCode == "" || $birthdate == "") {
	$_SESSION['notify'] = array("type"=>"error", "message"=>"Missing required fields.");
	header("location:patronEdit.php?ID=$id");
	exit;
}

//validate Prov/State
$prov = strtoupper($prov);
if (strlen($prov) == 2 && ctype_alpha($prov)) { // OK
} else {
	$_SESSION['notify'] = array("type"=>"error", "message"=>"Invalid Province/State.");
	header("location:patronEdit.php?ID=$id");
	exit;
}

//validate postal code
if (strlen($postalCode) == 5 && is_numeric($postalCode)) { 
	// OK. USA.
} else {
	if (strlen($postalCode) != 6 || is_numeric($postalCode)) {
		$_SESSION['notify'] = array("type"=>"error", "message"=>"Invalid Postal Code!");
		header("location:patronEdit.php?ID=$id");
		exit;
	}
}

//validate email
//Remove all characters except letters, digits and !#$%&'*+-=?^_`{|}~@.[].
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	$_SESSION['notify'] = array("type"=>"error", "message"=>"Invalid email address");
	header("location:patronEdit.php?ID=$id");
	exit;
}
//TODO validate phone

$sql = "UPDATE patron SET firstname=?, lastname=?, address=?, city=?, prov=?, postalCode=?, phone=?, email=?, birthdate=? WHERE id=?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("sssssssssi", $firstname, $lastname, $address, $city, $prov, $postalCode, $phone, $email, $birthdate, $id );
	$stmt->execute();
	$stmt->close();
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

$_SESSION['notify'] = array("type"=>"success", "message"=>"Patron record has been updated.");
header("location:patronEdit.php?ID=$id");
exit;
