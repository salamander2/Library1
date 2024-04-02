<?php
/*******************************************************
* patronViewCKO.php
* 
* This is a modification of patronView. It is specifically for checkout, presenting a summary of the patron information
* and then proceeding to checkout or to modify the patron record.
*
* TODO:  This page needs to be actually written properly. It's just a copy of patronEdit so far.  It's not used yet. It will be used by a patron to view his/her own record
* 
* called from patronFindCKO (by clicking on a patron) 
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF","PATRON");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - View Patron Info");
	header("location:main.php");
	exit;
}
/********************************************************/

# Check authorization (ie. that the user is logged in) or go back to login page
if (!isset($_SESSION["authkey"]) || $_SESSION["authkey"] != AUTHKEY)  {
    header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
	exit;
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

$postal = $patronData['postalCode'];
if (strlen($postal) ==6 ) {
  $postal = substr($postal,0,3)." ".substr($postal,4);
}

//Assemble patron data
$patName = $patronData['lastname'].", ".$patronData['firstname'];
$patAddress = $patronData['address'].", ".$patronData['city'].", ".$patronData['prov'].".  ".$postal;

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
	#pageheader {background-color:#DDF;}
</style>

</head>
<body>

<div class="container-md mt-2">

<!-- Page header -->
<div id="pageheader" class="alert alert-primary text-center rounded py-3">
	<a class="float-start btn btn-outline-dark rounded" onclick="history.back()"><i class="fa fa-arrow-left"></i>  Back</a>
	<a class="btn btn-outline-dark float-end" href="logout.php"><i class="fa fa-sign-out"></i>   Logout</a>
	<h2 class="fw-bold">The <?=$institution?> Public Libary</h2>
	<br clear="both">
    <hr class="py-0 mb-0">
</div>
<!-- end page header -->

<div class="card border-primary mt-3">

	<div class="card-body">
		
	<div class="card-head alert alert-primary pb-0"> <h2>Patron Information</div>
		<div class="border rounded"><!-- border around patron info -->
		<div class="row">
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="lastname" class="input-group-prepend btn btn-primary">Name</label>
				<input class="form-control bgP rounded-end" type="text" id="lastname" name="lastname" readonly value="<?=$patName?>">
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="birthdate" class="input-group-prepend btn btn-primary">Birth date</label>
				<input class="form-control bgP rounded-end" type="date" id="birthdate" name="birthdate" readonly value="<?=$patronData['birthdate'] ?>"><span class="text-danger"></span>
				</div>
			</div>
		<div class="text-secondary col-sm-4 text-end"> Date added: <?php echo strtok($patronData['createDate'], " ")?></div>
		</div>

		<div class="row my-2">
			<div class="col-md-6">
				<div class="input-group rounded">
				<label for="address" class="input-group-prepend btn btn-secondary">Address</label>
				<input class="form-control bgS rounded-end" type="text" id="address" name="address" readonly value="<?=$patAddress?>"><span class="text-danger"></span>
				</div>
			</div>
		</div>

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
		</div>
&nbsp;
	<div class="card-head alert alert-success mb-0 pb-0"> <h2>Library Cards </div>
<?php

$num_rows = mysqli_num_rows($libCards);
if($num_rows > 0) {
	//general HTML now being written
	echo '<table class="table table-secondary table-striped table-hover table-bordered mb-0">';
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
&nbsp;
<p>
<a href="checkout2.php"><button class="btn btn-success">Proceed to checkout</button></a> &nbsp;
<a href="patronEdit.php?ID=<?=$patronID?>"><button class="btn btn-primary">Edit Patron Record</button></a>
</p>
</div>

<br><br><br>
</body>
</html>
