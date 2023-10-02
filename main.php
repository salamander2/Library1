<?php
/*******************************************************
* This is the main landing page after one has logged on
* Other possibilities are: pac page and patron page
* Visible options vary depending on the access level of the user (admin or staff)
*
* This is called from index.php
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title><?=$institution?> Library Database</title>
	<!-- Required meta tags -->
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

<!-- this page has a special header, not the normal one that other pages use -->
<div class="container-md mt-2">
	<span class="float-end px-2 pt-1" style="background-color: rgba(255,255,255,0.35);"><img width=200 height=170 src="images/logoBG.png"></span>
	<h2 class="bg-warning text-center rounded py-3">The <?=$institution?> Public Libary</h2>

&nbsp;

	<div class="row">
	<div class="col-md-8 p-0">
		<div class="card border border-primary p-2">
			<div class="alert alert-warning mb-0">Welcome "<b><?=$userdata['fullname']?></b>"</div>
		</div>
	</div>
	</div>
&nbsp;

	<div class="card border border-secondary alert alert-warning">
		<div class="card-body">
		<div class="ml-3">
		<a href="patronList.php"><button type="button" class="btn btn-success">Search Patrons</button></a>
		<a href="" class="px-2"><button type="button" class="btn btn-outline-primary">Books</button></a>
		<a href=""><button type="button" class="btn btn-outline-primary">Fines</button></a>
		<a href=""><button type="button" class="btn btn-outline-primary">Reports</button></a>
		<span class="float-end"><a href="logout.php"><button type="button" class="btn btn-primary">Logout</button></a></span>
		</div>

		</div><!-- /card-body -->
	</div><!-- /card -->

	<div class="card border border-secondary alert alert-warning">
		<div class="card-body">

		<h3>Staff Announcements</h3>
		<h5><span class="border border-warning p-1">Today is <span id="date"></span></span></h5>

		<p> Here we will do the following:</p>
		<ul>
			<li>Patrons: update address, phone; add, delete; renew library card;
			<li>Books: add new books to the library, discard, repair
			<li>Reports: list of overdues, fines, etc.
			<li>Handle fines, lost books, etc.
		</ul>

		</div><!-- /card-body -->
	</div><!-- /card -->
</div>

<script>
	let text = (new Date()).toDateString();
	document.getElementById("date").innerHTML = text;
</script>
</body>

</html>
