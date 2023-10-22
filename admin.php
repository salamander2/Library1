<?php
/*******************************************************
* admin.php
* 
* This is just a simple page to hold links to other pages.
* Called from main.php
* Calls userMaint.php and ____
********************************************************/
session_start();
require_once('common.php');

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
    <link rel="stylesheet" href="resources/library.css" >
	<script src="resources/library.js"></script>

</head>

<body>
<div class="container-md mt-2">

<!-- page header -->
<?php loadHeader("main.php"); ?>

<div class="card alert alert-secondary">
	<div class="card-body">
		<h2 class="text-center"><u>Administrative Functions</u></h2>
		<br>
		<a class="btn btn-warning my-2" href="userMaint.php"><i class="fa fa-users"></i>  User Maintenance</a><br>
		<a class="btn btn-info my-2" href="reports.php"><i class="fa fa-clipboard-list"></i>  Reports</a><br>
		<a class="btn btn-primary my-2" href=""><i class="fa fa-plus-circle"></i>  Another Button</a>
	</div>
</div>

</div>
</body>
</html>
