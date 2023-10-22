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

		$sql = "UPDATE users SET password=?, defaultPWD=0 WHERE username=?";

		if ($stmt = $schoolDB->prepare($sql)) {
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
<?php loadHeader("main.php"); ?>
	<div id="header">
		<h1>Change your login password: <span class="green"><?php echo $userdata['fullname']; ?></span></h1>
	</div>


	<form class="pure-form" method="post" action="<?php echo ($_SERVER["PHP_SELF"]);?>">
		<p class="white">You will have to login again after changing your password.</p>
		<fieldset>
			<legend>

				<table>
					<tr>
						<td class="tcol1">
							<p>New Password:</p>
						</td><td class="tcol2">
							<input name="newpass" style="color:#777;" type="password" size="15" maxlength="15" value=""><br>
						</td>
					</tr>
				</table>
			</legend>
			<button type="submit" name="submit" class="pure-button fleft" style="margin:0 0.75em;font-weight:bold;">Submit</button>
		</fieldset>
	</form>
	</div>

	<!-- This is the JAVASCRIPT error message -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
</body>
</html>

