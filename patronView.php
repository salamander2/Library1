<?php
/*******************************************************
* patronView.php
* TODO:  This page needs to be actually written properly. It's just a copy of patronEdit so far.
* ############################################################
* called from patronList (by clicking on a patron)
* 		 and also from patronUpdate
* calls patronUpdate
* It also displays library cards, and books out.
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF","PATRON");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - View Patron Info");
	header("location:main.php");
}
/********************************************************/

# Check authorization (ie. that the user is logged in) or go back to login page
if ($_SESSION["authkey"] != AUTHKEY) { 
    header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
}

# Check user access level for the page (ie. Does the user have appropriate permissions to do this?)

$db = connectToDB();
$error_message = "";
if(isset($_SESSION["success_message"])) {
	$success_message = $_SESSION["success_message"];
	unset($_SESSION["success_message"]);
}
else $success_message = "";

$patronID = filter_var($_GET['ID'], FILTER_SANITIZE_NUMBER_INT);
$patronData = "";

$sql = "SELECT * FROM patron WHERE id = ?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $patronID);
	$stmt->execute(); 
	$patronData = $stmt->get_result()->fetch_assoc();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

//TODO Postal code: needs to be split into two parts. Need JS to check input for it (and remove all spaces)

$sql = "SELECT * FROM libraryCard WHERE patronID = ? ORDER BY expiryDate DESC";
 
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $patronID);
	$stmt->execute(); 
	$libCards = $stmt->get_result(); //->fetch_assoc();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title><?=$institution?> Library Database</title>
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

<style>
	/* for Patron View page only */
	#pageheader {background-color:#DBF;}
</style>


</head>
<body>

<div class="container-md mt-2">

<!-- Page header -->
<div id="pageheader" class="alert alert-info text-center rounded py-3">
	<a class="btn btn-outline-dark float-start" href="logout.php"><i class="fa fa-sign-out"></i>   Logout</a>
	<h2 class="fw-bold">The <?=$institution?> Public Libary</h2>
	<!-- <a class="float-start btn btn-warning rounded" onclick="history.back()"><i class="fa fa-arrow-left"></i>  Back</a> -->
	<br clear="both">
    <hr class="py-0 mb-0">
</div>
<!-- end page header -->


<div class="card border-primary mt-3">
	<div class="card-head alert alert-primary mb-0"> <h2>View Patron Information</div>

<div class="card-body">
		<div class="row text-secondary">
		<div class="col-sm-2"></div><div class="col-sm-6"></div><div class="col-sm-4 text-end"> Date added: <?php echo strtok($patronData['createDate'], " ")?></div>
		</div>
		
		<div class="row">
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="lastname" class="input-group-prepend btn btn-info">Last name</label>
				<input class="form-control bgP rounded-end" type="text" id="lastname" name="lastname" readonly value="<?=$patronData['lastname']?>"><span class="text-danger"></span>
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="firstname" class="input-group-prepend btn btn-info">First name</label>
				<input class="form-control bgP rounded-end" type="text" id="firstname" name="firstname" readonly value="<?=$patronData['firstname']?>"><span class="text-danger"></span>
				</div>
			</div>
		</div>
		<div class="row mt-2">
		<div class="col-sm-8 col-md-6 col-lg-4">
			<div class="input-group rounded">
			<label for="birthdate" class="input-group-prepend btn btn-info">Birth date</label>
			<input class="form-control bgP rounded-end" type="date" id="birthdate" name="birthdate" readonly value="<?=$patronData['birthdate'] ?>"><span class="text-danger"></span>
		</div></div></div>

		<h5 class="mt-3"><u>Address:</u></h5>
		<div class="row my-2">
			<div class="col-md-6">
				<div class="input-group rounded">
				<label for="address" class="input-group-prepend btn btn-secondary">Street</label>
				<input class="form-control bgS rounded-end" type="text" id="address" name="address" readonly value="<?=$patronData['address']?>"><span class="text-danger"></span>
				</div>
			</div>
		</div>

		<div class="row my-2">
			<div class="col-sm-6 col-md-4">
				<div class="input-group rounded">
				<label for="city" class="input-group-prepend btn btn-secondary">City</label>
				<input class="form-control bgS rounded-end" type="text" id="city" name="city" readonly value="<?=$patronData['city']?>"><span class="text-danger"></span>
				</div>
			</div>
			<div class="col-sm-4 col-lg-3 col-xxl-2">
				<div class="input-group rounded">
				<label for="prov" class="input-group-prepend btn btn-secondary">Prov./State</label>
				<input class="form-control bgS rounded-end" type="text" id="prov" name="prov" readonly value="<?=$patronData['prov']?>"><span class="text-danger"></span>
				</div>
			</div>
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="input-group rounded">
				<label for="postalCode" class="input-group-prepend btn btn-secondary">Postal Code</label>
				<input class="form-control bgS rounded-end" type="text" id="postalCode" name="postalCode" readonly value="<?=$patronData['postalCode']?>"><span class="text-danger"></span>
				</div>
			</div>
		</div>

		<h5 class="mt-4 fg1"><u>Contact:</u></h5>
		<div class="row">
			<div class="col-sm-8 col-md-4">
				<div class="input-group rounded">
				<label for="phone" class="input-group-prepend btn btn-outline-warning fg1"><b>Phone</b></label>
				<input class="form-control bg1" type="text" id="phone" name="phone" readonly value="<?=$patronData['phone']?>">
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-5">
				<div class="input-group rounded">
				<label for="email" class="input-group-prepend btn btn-outline-warning fg1"><b>Email</b></label>
				<input class="form-control bg1" type="text" id="email" name="email" readonly value="<?=$patronData['email']?>">
				</div>
			</div>
		</div>

</div></div> <!-- end of card-body and card -->

<div class="card border-success mt-3">
<div class="card-body">
	<div class="card-head alert alert-success"> <h2>Library Cards </div>
<?php

$num_rows = mysqli_num_rows($libCards);
if($num_rows > 0) {
	//general HTML now being written
	echo '<table class="table table-secondary table-striped table-hover table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Barcode</th>';
	echo '<th>Status</th>';
	echo '<th>Date Issued</th>';
	echo '<th>Expiry Date</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	// printing table rows: student name, student number
	while ($row = $libCards->fetch_assoc()){ 
		echo "<tr>";
		echo "<td>".$row['barcode']. "</td>";
		echo "<td>".$row['status']. "</td>";
		echo "<td>".strtok($row['createDate']," "). "</td>";
		echo "<td>".$row['expiryDate']. "</td>";
		echo "</tr>";
	} 

	echo '</tbody>';
	echo '</table>';
}

?>

</div></div> <!-- end of card-body and card -->
</div>

<br><br><br>
</body>
</html>
