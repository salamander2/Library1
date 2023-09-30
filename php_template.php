<?php

/* Name of file (program)
   What it does
   Where it is called from
   Where it transfers to (what it calls
*/

/* Session variables:
*/
session_start();

# common.php has a set of common utility methods. 
# It also sets the Error reporting (to /var/log/apache2/error.log)
# It sets various session variables if they are not already set.
# and it links to config.php which has the variables needed to log in to MySQL.
require_once('common.php');

$db = connectToDB();

# Check authorization (ie. that the user is logged in) or go back to login page
if ($_SESSION["authkey"] != AUTHKEY) { 
    header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
}

# Check user access level for the page (ie. Does the user have appropriate permissions to do this?)

$error_message = "";

# get POST and GET data
$user = $_POST["user"];
$patron = $_GET['ID'];

# do error checking on the data. Set error message if needed:
$dob = clean_input($_POST['dob']);
//if (!validate_date($dob)) $error_message = "Invalid date or incorrect format";
//...
if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";
	
//if corrent, then add to database
if (empty($error_message)) { }

# Run SQL select statements (or others if needed), using prepared statements

/* If you're not using any variables in the SQL statements, you could use mysqli
	//get all of the users (students)
	#$sql = "SELECT username,fullname,lastLogin FROM users ORDER BY fullname";
	$sql = "SELECT username,fullname,DATE_FORMAT(lastLogin,'%a, %b %e %Y') FROM users ORDER BY fullname";
	$result=runSimpleQuery($db,$sql);
	$response = mysqli_fetch_all($result);
*/


# PHP methods

?>
<!-- HTML template inserted from here to end -->
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Library Database — 2023</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./resources/bootstrap5.min.css" >
    <!-- our project just needs Font Awesome Solid + Brands -->
    <!-- <link href="resources/fontawesome-6.4.2/css/fontawesome.min.css" rel="stylesheet"> -->
    <link href="./resources/fontawesome6.min.css" rel="stylesheet">
    <link href="./resources/fontawesome-6.4.2/css/brands.min.css" rel="stylesheet">
    <link href="./resources/fontawesome-6.4.2/css/solid.min.css" rel="stylesheet">
</head>

<body>
<div class="container">
	<p>The <u>container or container-fluid</u> has the whole page in it (except for perhaps a header or footer)</p>

	//Is this used??
	<div id="error_message"></div>  
	<?php if ($error_message != "") echo $error_message; ?>

	<h2 class="text-danger">A Heading : This is a template for HTML &amp; Bootstrap</h2>
	<div id="error_message"></div>
	<div class="card">

		<div class="card-header bg-info text-white"><b>This is the card header</b></div>
		<div class="card-body">
			<p class="">This is the card body</p>

			<p class="text-success">Here is a form (inside the yellow border)</p>
			<form class="border border-warning p-1" action="something.php" method="post" onsubmit="return validateData()">
				<div class="input-group mb-3">
					<input type="text" name="username" size="20" id="username" class="form-control" placeholder="Username" autofocus>
				</div>
				<div class="input-group mb-3">
					<input type="password" name="password" id="password" class="form-control" placeholder="Password">
				</div>
				<div class="row">
					<div class="col-lg-2 col-md-4 col-12 mt-1">
						<button type="submit" name="submit" class="btn btn-primary btn-block">Sign In</button>
					</div>
					<!-- /.col -->
					<div class="col-lg-3 col-md-4 col-12 mt-1">
						<a href="register.php" class="btn btn-outline-primary btn-block">Register a new user</a>
					</div>
					<!-- /.col -->
					<div class="col-lg-3 col-md-4 col-12 mt-1">
						<a href="help.html" class="btn btn-outline-primary btn-block">I forgot my password</a>
					</div>
					<!-- /.col -->
				</div>
			</form>

		</div><!-- /card-body -->
	</div><!-- /card -->
	<div class="alert alert-secondary mt-3">You would still need to learn how to use &lt;row&gt; and &lt;col&gt; classes to make the page work on different size displays.</div>
 </div><!-- /container -->

</body>
</html>
