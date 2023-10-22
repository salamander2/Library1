<?php
/*******************************************************************************
  Name: changePWD.php
  Called from: admin.php
  Purpose: change user password 
 ***NOTE: does not work with prepared statements
 Tables used: schoolDB/users
 Calls: 
 Transfers control to: logout.php
 ******************************************************************************/

session_start();
require_once('common.php');

$newpass = "";

//if the submit button has been pressed:
if(isset($_POST['submit'])) {  

	$newpass = clean_input($_POST['newpass']);
	if (strlen($newpass) < 7) $notify["message"] = "Your password must be at least 7 characters";
	if (empty($newpass)) $notify["message"] = "Please enter a password";

	//if correct, then add to database
	if (empty($notify["message"])) {
		$hashPassword = password_hash($newpass, PASSWORD_DEFAULT);

		$sql = "UPDATE users SET password=?, defaultPWD=0 WHERE username=BINARY ?";
echo $userdata['username'];  
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("ss", $hashPassword, $userdata['username'] );
			$stmt->execute();
			$stmt->close();
		} else {
			die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
		}

		header("Location: logout.php");
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

</head>

<body>

<div class="container-md mt-2">

<!-- page header -->
<?php loadHeader("main.php"); ?>

<div class="card alert alert-secondary">
	<div class="card-body">
		<h3 class="text-success">Change your login password: <span class="text-dark"><?php echo $userdata['fullname']; ?></h3>
		<br>
	</div>

	<form class="form" method="post" action="<?php echo ($_SERVER["PHP_SELF"]);?>">
			<div class="input-group">
			<label for="newpass" class="input-group-prepend btn btn-success">New Password: </label>
			<input class="Xform-control bgU rounded-end" type="password" id="newpass" name="newpass" size="25" value="" required autofocus>
			</div>
			<br>
			<p class="white">You will have to login again after changing your password.</p>
			<button type="submit" name="submit" class="btn btn-secondary" style="margin:0 0.75em;font-weight:bold;">Submit</button>
	</form>

	<!-- This is the JAVASCRIPT error message -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
</div>
</div>
</body>
</html>

