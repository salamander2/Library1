<?php

/****************************************************************************
File: index.php
Purpose: This is the login page for the application.
	     (Taken from ics_upload)
Called from: This is the default page after logout.php
			 or any failure of authentication.
Calls: main.php (the home page)
	   also: public access catalog page
*****************************************************************************/

session_start();
//NOTE: common.php has an exclusion clause for this page (index.php)
require_once('common.php');

//Override the common.php functionality. Username needs to be cleared because this is a login page.
if (isset($username)){
	$username = "";
	$_SESSION["username"] = "";
}

// If this page is ever loaded, logout the user.
$_SESSION["authkey"] = "";

$db = connectToDB();
$error_message = "";

/**** LOGIN LOGIC *******/

if(isset($_POST['submit'])) {
	$username = clean_input($_POST['username']);
	$password = $_POST["password"];

	//Retrieve all data for that user and verify the password for that user. It is stored into an array "userdata".
	$sql = "SELECT username, fullname, password as pwdHash, authlevel, createDate, lastLogin FROM users WHERE username = BINARY ?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		$userdata = $result->fetch_array(MYSQLI_ASSOC);
		$stmt->close();
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}

	//Check if user exists, then verify password
	$row_cnt = mysqli_num_rows($result);
	if (0 === $row_cnt) {		
		$notify["message"] = "That user does not exist. <br><span class='small'>(Check case of username or talk to admin.)</span>";
	} elseif (!password_verify ($password, $userdata['pwdHash'])) {
		$notify["message"] = "Invalid password";
	}
	//Password has been checked, now clear the variable for security reasons.
	$password = "---";
	$userdata['pwdHash'] = "";
	
	// error message ...
	if (empty($notify["message"])) {
		$_SESSION["userdata"] = $userdata;
		//This is set here upon login (AND ALSO IN register.php)  and then session-authkey is never set again.
		$_SESSION["authkey"] = AUTHKEY;

		//Update last login timestamp (which is deliberately not updated in $userdata, in case we want to know when the last logon was
		$sql = "UPDATE users set lastLogin=NOW() WHERE username = BINARY ?";
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("s", $username);
			$stmt->execute();
			$stmt->close();
		} else {
			die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
		}
/*
		if ($username == ADMINUSER) {
			header('Location:adminMain.php');
		} else {
			header('Location:main.php');
		}
*/

		header('LOCATION:main.php');
	}
	
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Library Database — 2023</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="resources/bootstrap5.min.css" >
    <!-- our project just needs Font Awesome Solid + Brands -->
    <!-- <link href="resources/fontawesome-6.4.2/css/fontawesome.min.css" rel="stylesheet"> -->
    <link href="resources/fontawesome6.min.css" rel="stylesheet">
    <link href="resources/fontawesome-6.4.2/css/brands.min.css" rel="stylesheet">
    <link href="resources/fontawesome-6.4.2/css/solid.min.css" rel="stylesheet">
    <link rel="stylesheet" href="resources/library.css" >
	<script src="resources/library.js"></script>

	<script>
		function validateData() {
			var x = document.getElementById("username").value;
			if (!x || 0 === x.length) {
				displayNotification("error", "You must include a username");
				//document.getElementById("username").classList.add("border-danger");
				document.getElementById("username").classList.toggle("is-invalid");
				document.getElementById("username").value = "";
				return false;
			}
			x = document.getElementById("password").value;
			if (!x || 0 === x.length) {
				displayNotification("error", "You must include a password");
				document.getElementById("password").classList.toggle("is-invalid");
				document.getElementById("password").value = "";
				return false;
			}

			return true;
		}
	</script>

</head>

<body>

<div class="container-md mt-2">
	<h2 class="bg-warning text-center rounded py-3">The <?=$institution?> Public Libary</h2>

&nbsp;

	<div class="row">
	<div class="col-md-8">
	<div class="card border border-primary p-2">
		<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post" onsubmit="return validateData()">
		<div class="alert alert-warning"><b>Sign in</b></div>
		<!-- <div class="input-group mb-3"> -->
		<div class="row">
			<div class="col-4">
				<input type="text" name="username" id="username" class="form-control" placeholder="Username" >
			</div>
			<div class="col-4">
				<input type="password" name="password" id="password" class="form-control" placeholder="Password">
			</div>
			<div class="col-md-2">
			<!--<div class="col-lg-2 col-md-4 col-12 mt-1"> -->
				<button type="submit" name="submit" class="btn btn-primary">
					Login
				</button>
			</div>
		</div>
		<p class="small mt-3">Temp username: "staff", password "SnowyMarch"</p>
		</form>
		</div>
	</div>
	<div class="d-none d-md-block col-3 offset-1"><img width=200 height=170 src="images/logoBG.png">
	</div>
	</div> 

	<div>&nbsp;</div>
	<!-- This is the JAVASCRIPT error message -->
	<div id="notif_container"></div>
	<!-- // This is the PHP error message. The php variables are not JS variables, so we need to add \"  -->
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>

	<div class="card border border-secondary alert alert-warning">
	<div class="card-body">
		<div style="text-align:center">
			<h3><b>Welcome to our library database project.</b><br>-= Status =-</h3>
		</div>
		<div class="row">
			<div class="col">
			<p class="alert alert-success fw-bold">So far the following is working:</p>
			<ul>
				<li>login and log out
				<li>patrons: responsive search, add, edit
				<li>library cards: add, change status (via patron page)
				<li>books: seach (multiple fields), edit, add
			</ul>
			</div>
			<div class="col">
			<p class="alert alert-danger fw-bold">The following is NOT YET working:</p>
			<ul>
				<li>checkout/check in books
				<li>fines
				<li>crontab to update status/fines overnight
				<li>delete patron
				<li>delete copies and titles
				<li>placing and reconciling holds
				<li>public access console (search)
				<li>different user levels and different users
				<li>patrons searching and placing holds
				<li>administrative reports
			</ul>
			</div>
		</div>
		<div class="row">
			<div class="col-6">
			<p class="alert alert-dark fw-bold">The following will NOT be implemented</p>
			<ul>
				<li>multiple library branches
				<li>various patron types (senior, child, ...)
				<li>different fine amounts for different materials/patron types
			</ul>
			</div>
		</div>
	</div><!-- /card-body -->
	</div><!-- /card -->

</div>
<div id="footer" class="centered">
Created by Michael Harwood &copy; 2023.
</div>
</body>

</html>
