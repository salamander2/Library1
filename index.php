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
require_once('common.php');

//Override the common.php functionality. Username needs to be cleared because this is a login page.
if (isset($username)){
	$username = "";
	$_SESSION["username"] = "";
}

//TODO: Add in a connect time, that's udpdated for every action. If the connect time is more than 6 hours old, logout the user.

//TODO: If this page is ever loaded, logout the user.

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
		$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
		$message_ .= 'SQL: ' . $sql;
		die($message_);
	}

	//Check if user exists, then verify password
	$row_cnt = mysqli_num_rows($result);
	if (0 === $row_cnt) {		
		$error_message = "That user does not exist. <br><span class='small'>(Check case of username or talk to admin.)</span>";
	} elseif (!password_verify ($password, $userdata['pwdHash'])) {
		$error_message = "Invalid password";
	}
	//Password has been checked, now clear the variable for security reasons.
	$password = "---";
	$userdata['pwdHash'] = "";
	
	// error message ...
	if ($error_message != "") $error_message = '<div class="alert text-white bg-danger w-50 mt-3"><b> '. $error_message .' </b></div>';
	if (empty($error_message)) {
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
			$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
			$message_ .= 'SQL: ' . $sql;
			die($message_);
		}
/*
		if ($username == ADMINUSER) {
			header('Location:adminMain.php');
		} else {
			header('Location:main.php');
		}
*/

		header('Location:main.php');
	}
}

//For development:
//"shell_exec() or exec() do not allow full ls listing.
//Also, running "git branch" doesn't work either
//$gitbranch = "Current branch: ".(exec('git branch --show-current'));
$gitbranch = file('.git/HEAD', FILE_USE_INCLUDE_PATH)[0];
$gitbranch = explode("/", $gitbranch, 3)[2]; //seperate out by the "/" in the string, take branchname
if (trim($gitbranch) == "master") $gitbranch = "";
else $gitbranch = "Current branch:<br><b>$gitbranch</b>";
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
</head>

<body>
	<!-- This form will call either login.php or register.php with the same fields. -->
	<script>
		function validateData() {
			var x, text;
			x = document.getElementById("username").value;
			if (!x || 0 === x.length) {
				text = "You must include a username";
				//text = "<div class=\"error\">" + text + "</div>";
				document.getElementById("error_message").outerHTML =
					'<div id="error_message" class="alert alert-danger w-50 mt-2"></div>';
				document.getElementById("username").outerHTML =
					'<input type="text" name="username" id="username"  class="form-control border-danger" placeholder="Username">';
				document.getElementById("error_message").innerHTML = text;
				document.getElementById("username").value = "";
				return false;
			}
			x = document.getElementById("password").value;
			if (!x || 0 === x.length) {
				text = "You must include a password";
				//text = "<div class=\"error\">" + text + "</div>";
				document.getElementById("error_message").outerHTML =
					'<div id="error_message" class="alert alert-danger w-50 mt-2"></div>';
				document.getElementById("password").outerHTML =
					'<input type="password" name="password" id="password" class="form-control border-danger" placeholder="Password">';
				document.getElementById("error_message").innerHTML = text;
				document.getElementById("password").value = "";
				return false;
			}

			return true;
		}
	</script>

<span class="small" style="position:absolute;left:0px;top:0px;z-index:-1;"><?=$gitbranch ?></span>

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
	<div class="col-3 offset-1"><img width=200 height=170 src="images/logoBG.png">
	</div>
	</div> 
	<div>&nbsp;</div>
	<!-- This is the JAVASCRIPT error message -->
	<div id="error_message"></div>
	<!-- This is the PHP error message -->
	<?php if ($error_message != "") echo $error_message; ?>

	<div class="card border border-secondary alert alert-warning">
		<div class="card-body">
		<h3>Welcome to our library database project</h3>
        <b><u>Status</u></b>
		<p>So far the following is working:</p>
		<ul>
		<li>login and log out
		<li>listing patrons
		<li>adding a patron
		</ul>

		</div><!-- /card-body -->
	</div><!-- /card -->

</div>
</body>

</html>
