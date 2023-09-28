<?php
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
	<title>WebDev Project: Library Database</title>
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

<div class="container-md mt-2">
	<span class="float-end px-2 pt-1" style="background-color: rgba(255,255,255,0.35);"><img width=200 height=170 src="images/logoBG.png"></span>
	<h2 class="bg-warning text-center rounded py-3">The GHOSTS Public Libary</h2>

&nbsp;

	<div class="row">
	<div class="col-md-8 p-0">
		<div class="card border border-primary p-2">
			<div class="alert alert-warning">Welcome "<?=$fullname?>"</div>
		</div>
	</div>
	</div>
&nbsp;

	<div class="card border border-secondary alert alert-warning">
		<div class="card-body">
<!-- SEARCH FORM -->
    <div class="ml-3">
        <fieldset>
            <b>Search:</b> <input size=35 id="inputName" type="text" onkeyup="findPatron(this.value)" onkeydown="if (event.keyCode === 27) resetTerminal();"
            placeholder="Enter Patron last name, phone, or barcode">
		<span class="float-end"><a href="addPatron.php"><button type="button" class="btn btn-success">Add Patron</button></a></span>
        </fieldset>
		<p><i>This does not work yet</i></p>
    </div>

&nbsp;
			<p class="">* <a href="listPatron1.php">List all Patrons</a><br>
			Here's our initial list patron page. Eventually, it will pop up from the search bar above.</p>
			<p class="">* <a href="">Edit a Patron</a><br>
			(This will normally be accessed by clicking on a patron from the listing above.)<br>
			(This page will show the patron information and allow editing (update) or deletion. Upon deletion it will return here.)</p>

		</div><!-- /card-body -->
	</div><!-- /card -->

	<div class="card border border-secondary alert alert-warning">
		<div class="card-body">

		<h3>This is the main page for library staff</h3>
		<span class="float-end"><a href="logout.php"><button type="button" class="btn btn-primary">Logout</button></a></span>
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
</body>

</html>
