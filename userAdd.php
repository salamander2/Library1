<?php
/*******************************************************
* userAdd.php
* Called from userMaint.php
* Calls: returns to userMaint.php upon success or failure
* Purpose: adds new user to database
* 		validates input and adds record
********************************************************/
session_start();
require_once('common.php');


	//FIXME All validation still needs to be done here and on patronEdit.php
	$username=$fullname="";
	if (isset($_POST['username'])) $username = clean_input($_POST['username']);
	if (isset($_POST['fullname'])) $fullname = clean_input($_POST['fullname']);
	$authlevel = $_POST['authlevel'];

	//FIXME It's slighty more robust to use teh actual values in an array. "MIN,ST" would be seen here as a valid string.
    if (strlen($authlevel) > 6 || strpos("ADMIN,STAFF,PATRON,PUBLIC", $authlevel) === false) $authlevel = "PATRON";

	if ($username == "") {
		$_SESSION['notify'] = array("type"=>"error", "message"=>"Missing username.");
		header("location:userMaint.php");
	}
	if ($fullname == "") {
		$_SESSION['notify'] = array("type"=>"error", "message"=>"Missing fullname.");
		header("location:userMaint.php");
	}
		
/*
	$sql = "INSERT INTO patron (firstname, lastname, address, city, prov, postalCode, phone, email, birthdate ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ? )";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("sssssssss", $firstname, $lastname, $address, $city, $prov, $postalCode, $phone, $email, $birthdate );
		$stmt->execute();
		$patronID = $stmt->insert_id;
		$stmt->close();
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
*/
	$_SESSION['notify'] = array("type"=>"success", "message"=>"User record has been added.");

	header("location:userMaint.php");


/*

<div class="card-body">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		
		<div class="row">
			<div class="col-sm-8 col-md-6 col-lg-4 mt-1">
				<div class="input-group rounded">
				<label for="lastname" class="input-group-prepend btn btn-info">Last name</label>
				<input class="form-control bgP rounded-end" type="text" id="lastname" name="lastname" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-4 mt-1">
				<div class="input-group rounded">
				<label for="firstname" class="input-group-prepend btn btn-info">First name</label>
				<input class="form-control bgP rounded-end" type="text" id="firstname" name="firstname" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>
		<div class="row mt-2">
		<div class="col-sm-8 col-md-6 col-lg-4">
			<div class="input-group rounded">
				<label for="birthdate" class="input-group-prepend btn btn-info">Birth date</label>
				<input class="form-control bgP rounded-end" type="date" id="birthdate" name="birthdate" required><span class="text-danger">&nbsp;*</span>
			</div>
		</div></div>

		<h5 class="mt-3"><u>Address:</u></h5>
		<div class="row my-2">
			<div class="col-md-6">
				<div class="input-group rounded">
				<label for="address" class="input-group-prepend btn btn-secondary">Street</label>
				<input class="form-control bgS rounded-end" type="text" id="address" name="address" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<br clear="both">
		<div class="row">
		<div class="col">
			<button type="submit" id="submit" name="submit" class="btn btn-success">Create Patron</button> &nbsp;
		</div>
	</form>
*/
?>
