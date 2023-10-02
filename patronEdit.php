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
if(isset($_SESSION["success_message"])) {
	$success_message = $_SESSION["success_message"];
	unset($_SESSION["success_message"]);
}
else $success_message = "";

/* patron table
+------------+--------------+------+-----+-------------------+-------------------+
| Field      | Type         | Null | Key | Default           | Extra             |
+------------+--------------+------+-----+-------------------+-------------------+
| id         | int unsigned | NO   | PRI | NULL              | auto_increment    |
| firstname  | varchar(30)  | NO   |     | NULL              |                   |
| lastname   | varchar(30)  | NO   |     | NULL              |                   |
| address    | varchar(255) | NO   |     | NULL              |                   |
| city       | varchar(100) | NO   |     | NULL              |                   |
| prov       | varchar(2)   | NO   |     | NULL              |                   |
| postalCode | varchar(6)   | NO   |     | NULL              |                   |
| phone      | varchar(20)  | YES  |     | NULL              |                   |
| email      | varchar(50)  | YES  |     | NULL              |                   |
| birthdate  | date         | NO   |     | NULL              |                   |
| createDate | timestamp    | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+--------------+------+-----+-------------------+-------------------+
*/

$patronID = filter_var($_GET['ID'], FILTER_SANITIZE_NUMBER_INT);
$patronData = "";

$sql = "SELECT * FROM patron WHERE id = ?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $patronID);
	$stmt->execute(); 
	$patronData = $stmt->get_result()->fetch_assoc();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
	$message_ .= 'SQL2: ' . $query;
	die($message_); 
}

//TODO Postal code: needs to be split into two parts. Need JS to check input for it (and remove all spaces)

/*
+------------+--------------------------------+------+-----+-------------------+-------------------+
| Field      | Type                           | Null | Key | Default           | Extra             |
+------------+--------------------------------+------+-----+-------------------+-------------------+
| barcode    | int unsigned                   | NO   | PRI | NULL              | auto_increment    |
| patronId   | int unsigned                   | NO   | MUL | NULL              |                   |
| status     | enum('VALID','LOST','EXPIRED') | NO   |     | VALID             |                   |
| expiryDate | date                           | YES  |     | NULL              |                   |
| createDate | timestamp                      | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+--------------------------------+------+-----+-------------------+-------------------+
*/
$sql = "SELECT * FROM libraryCard WHERE patronID = ? ORDER BY expiryDate DESC";
 
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $patronID);
	$stmt->execute(); 
	$libCards = $stmt->get_result(); //->fetch_assoc();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
	$message_ .= 'SQL2: ' . $query;
	die($message_); 
}
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

<style>
.fg1 {color:#620;}
.bg1 {background-color:#FFA;}
.fg2 {color:#518;}
.bg2 {background-color:#CAF;}
.bg3 {background-color:#cfe2ff;} /* primary */
.bg4 {background-color:#C9D5D5;} /* secondary */
</style>



</head>
<body>

<div class="container-md mt-2">

<div id="" class="alert alert-warning text-center rounded py-3">
	<a class="fa fa-sign-out btn btn-outline-dark float-start m-2" href="logout.php">  Logout</a>
	<span class="float-end"> <a class="d-block fa fa-cogs btn btn-outline-dark m-2" href="admin.php">  Administer</a> </span>
	<h2 class="fw-bold">The <?=$institution?> Public Libary</h2>
	<br clear="both">
	<a class="float-start btn btn-dark rounded" href="patronList.php"><i class="fa fa-arrow-left"></i>  Back</a>
    <hr>
	<br clear="both">
</div>

<a class="btn btn-info rounded" href="patronEdit.php?ID=<?php echo $patronID-1; ?>"><i class="fa fa-arrow-left"></i>  Prev. Patron</a>
<a class="btn btn-info rounded" href="patronEdit.php?ID=<?php echo $patronID+1; ?>"><i class="fa fa-arrow-right"></i>  Next Patron</a>
<br clear="both">

<div class="card border-primary mt-3">
	<div class="card-head alert alert-primary mb-0"> <h2>Patron Information
	<a class="float-end btn btn-outline-danger rounded" href="patronDelete.php"><i class="fa fa-circle-minus"></i>  Delete Patron</a></h2>
	</div>

<div class="card-body">
	<form action="patronUpdate.php" method="post">
		<div class="row text-secondary">
		<div class="col-sm-2">ID: <?=$patronID?></div><div class="col-sm-6"></div><div class="col-sm-4 text-end"> Date added: <?php echo strtok($patronData['createDate'], " ")?></div>
		</div>
		
		<div class="row">
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="lastname" class="input-group-prepend btn btn-info">Last name</label>
				<input class="form-control bg3 rounded-end" type="text" id="lastname" name="lastname" required value="<?=$patronData['lastname']?>"</input><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="firstname" class="input-group-prepend btn btn-info">First name</label>
				<input class="form-control bg3 rounded-end" type="text" id="firstname" name="firstname" required value="<?=$patronData['firstname']?>"</input><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<h5 class="mt-3"><u>Address:</u></h5>
		<div class="row my-2">
			<div class="col-md-6">
				<div class="input-group rounded">
				<label for="address" class="input-group-prepend btn btn-secondary">Street</label>
				<input class="form-control bg4 rounded-end" type="text" id="address" name="address" required value="<?=$patronData['address']?>"</input><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<div class="row my-2">
			<div class="col-sm-6 col-md-4">
				<div class="input-group rounded">
				<label for="city" class="input-group-prepend btn btn-secondary">City</label>
				<input class="form-control bg4 rounded-end" type="text" id="city" name="city" required value="<?=$patronData['city']?>"</input><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-4 col-lg-3 col-xxl-2">
				<div class="input-group rounded">
				<label for="prov" class="input-group-prepend btn btn-secondary">Prov./State</label>
				<input class="form-control bg4 rounded-end" type="text" id="prov" name="prov" required value="<?=$patronData['prov']?>"</input><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="input-group rounded">
				<label for="postalCode" class="input-group-prepend btn btn-secondary">Postal Code</label>
				<input class="form-control bg4 rounded-end" type="text" id="postalCode" name="postalCode" required value="<?=$patronData['postalCode']?>"</input><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<h5 class="mt-4 fg1"><u>Contact:</u></h5>
		<div class="row">
			<div class="col-sm-8 col-md-4">
				<div class="input-group rounded">
				<label for="phone" class="input-group-prepend btn btn-outline-warning fg1"><b>Phone</b></label>
				<input class="form-control bg1" type="text" id="phone" name="phone" required value="<?=$patronData['phone']?>"</input>
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-5">
				<div class="input-group rounded">
				<label for="email" class="input-group-prepend btn btn-outline-warning fg1"><b>Email</b></label>
				<input class="form-control bg1" type="text" id="email" name="email" required value="<?=$patronData['email']?>"</input>
				</div>
			</div>
		</div>
		<input type="hidden" id="d" name="id" value="<?=$patronID?>">

		<br clear="both">
		<h4><button type="submit" class="btn btn-success">Submit</button>
		<?php
			if (strlen($success_message)>0) {
			echo '<span class="float-end rounded fg2 bg2 px-2 py-1">';
			echo $success_message;
			echo "</span>";
			}
		?>
		</h4>

	</form>
</div></div> <!-- end of card-body and card -->

<div class="card border-success mt-3">
<div class="card-body">
	<div class="card-head alert alert-success"> <h2>Library Cards
	<a class="float-end btn btn-outline-success rounded" href="patronDelete.php"><i class="fa fa-circle-plus"></i>  Add Card</a></h2>
	</div>
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
