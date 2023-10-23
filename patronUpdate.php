<?php
/*******************************************************
* patronUpdate.php 
* Called from patronEdit.php
* This updates the patron record.
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
}
/********************************************************/

$id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

$firstname=$lastname=$address=$city=$prov=$postalCode=$phone=$email=$birthdate="";

//FIXME All validation still needs to be done here and on patronAdd.php
if (isset($_POST['firstname'])) $firstname = clean_input($_POST['firstname']);
$lastname = clean_input($_POST['lastname']);
$address= clean_input($_POST['address']);
$city = clean_input($_POST['city']);
$prov = clean_input($_POST['prov']);
$postalCode = clean_input($_POST['postalCode']);
$phone = clean_input($_POST['phone']);
$email = clean_input($_POST['email']);
$birthdate = clean_input($_POST['birthdate']);

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
