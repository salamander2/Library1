<?php
/*******************************************************
* patronEdit.php
* called from patronList (by clicking on a patron)
* 		 and also from patronUpdate
* calls patronUpdate
* This displays the patron data for editing.
* It also displays library cards, and books out.
* FIXME the next and previous patron buttons will show a blank entry if that patronID has been deleted.
********************************************************/
session_start();
require_once('common.php');

# Check authorization (ie. that the user is logged in) or go back to login page
if ($_SESSION["authkey"] != AUTHKEY) { 
    header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
}

# Check user access level for the page (ie. Does the user have appropriate permissions to do this?)

$db = connectToDB();
if(isset($_SESSION["error_message"])) {
	$error_message = $_SESSION["error_message"];
	unset($_SESSION["error_message"]);
} else $error_message = "";
if(isset($_SESSION["success_message"])) {
	$success_message = $_SESSION["success_message"];
	unset($_SESSION["success_message"]);
} else $success_message = "";

/* 
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
	<script src="resources/jquery-3.7.1.min.js"></script>
<style>
	.fg1 {color:#620;}	/* yellow */
	.bg1 {background-color:#FFA;}
	.fg2 {color:#518;} /* purple */
	.bg2 {background-color:#CAF;}
	.bg3 {background-color:#cfe2ff;} /* primary */
	.bg4 {background-color:#C9D5D5;} /* secondary */
</style>

<script>
$(function(){ //document ready function
	$.fn.validateForm = function() { 

		const inputs = ["email", "firstname", "lastname", "city"];
		let retVal = true;

		//make sure all the the inputs are filled
		inputs.forEach( function(input) {
			let element = document.getElementById(input);
			console.log(input);
			if(element.value === "") {
				element.className = "form-control is-invalid";
				retVal = false;
			} else {
				element.className = "form-control is-valid";
			}
		});
/*		//validate email
		let email = document.getElementById("email");
		if(validateEmail(email.value.trim())){
			email.className = "form-control is-valid";
		} else {
			email.className = "form-control is-invalid";
			retVal = false;
		}
*/
		return retVal;
	}
	 
}); 
</script>

</head>
<body>

<div class="container-md mt-2">

<!-- page header -->
<?php loadHeader("patronList.php"); ?>

<div class="card border-primary mt-3">
	<div class="card-head alert alert-primary mb-0"> <h2>Patron Information
	<a class="float-end btn btn-outline-danger rounded" href="patronDelete.php"><i class="fa fa-circle-minus"></i>  Delete Patron</a></h2>
	</div>

<div class="card-body">
	<form action="patronUpdate.php" onsubmit="return $.fn.validateForm()" method="post">
		<div class="row text-secondary">
		<div class="col-sm-2">ID: <?=$patronID?></div><div class="col-sm-6"></div><div class="col-sm-4 text-end"> Date added: <?php echo strtok($patronData['createDate'], " ")?></div>
		</div>
		
		<div class="row">
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="lastname" class="input-group-prepend btn btn-info">Last name</label>
				<input class="form-control bg3 rounded-end" type="text" id="lastname" name="lastname" required value="<?=$patronData['lastname']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="firstname" class="input-group-prepend btn btn-info">First name</label>
				<input class="form-control bg3 rounded-end" type="text" id="firstname" name="firstname" required value="<?=$patronData['firstname']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>
		<div class="row mt-2">
		<div class="col-sm-8 col-md-6 col-lg-4">
			<div class="input-group rounded">
			<label for="birthdate" class="input-group-prepend btn btn-info">Birth date</label>
			<input class="form-control bg3 rounded-end" type="date" id="birthdate" name="birthdate" required value="<?=$patronData['birthdate'] ?>"><span class="text-danger">&nbsp;*</span>
		</div></div></div>

		<h5 class="mt-3"><u>Address:</u></h5>
		<div class="row my-2">
			<div class="col-md-6">
				<div class="input-group rounded">
				<label for="address" class="input-group-prepend btn btn-secondary">Street</label>
				<input class="form-control bg4 rounded-end" type="text" id="address" name="address" required value="<?=$patronData['address']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<div class="row my-2">
			<div class="col-sm-6 col-md-4">
				<div class="input-group rounded">
				<label for="city" class="input-group-prepend btn btn-secondary">City</label>
				<input class="form-control bg4 rounded-end" type="text" id="city" name="city" required value="<?=$patronData['city']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-4 col-lg-3 col-xxl-2">
				<div class="input-group rounded">
				<label for="prov" class="input-group-prepend btn btn-secondary">Prov./State</label>
				<input class="form-control bg4 rounded-end" type="text" id="prov" name="prov" required value="<?=$patronData['prov']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="input-group rounded">
				<label for="postalCode" class="input-group-prepend btn btn-secondary">Postal Code</label>
				<input class="form-control bg4 rounded-end" type="text" id="postalCode" name="postalCode" required value="<?=$patronData['postalCode']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<h5 class="mt-4 fg1"><u>Contact:</u></h5>
		<div class="row">
			<div class="col-sm-8 col-md-4">
				<div class="input-group rounded">
				<label for="phone" class="input-group-prepend btn btn-outline-warning fg1"><b>Phone</b></label>
				<input class="form-control bg1" type="text" id="phone" name="phone" value="<?=$patronData['phone']?>">
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-5">
				<div class="input-group rounded">
				<label for="email" class="input-group-prepend btn btn-outline-warning fg1"><b>Email</b></label>
				<input class="form-control bg1" type="text" id="email" name="email" value="<?=$patronData['email']?>">
				</div>
			</div>
		</div>
		<input type="hidden" id="id" name="id" value="<?=$patronID?>">

		<br clear="both">
		<h4><button type="submit" name="submit" id="submit" class="btn btn-success">Submit</button>
		<?php
			if (strlen($success_message)>0) {
			echo '<span class="float-end rounded fg2 bg2 px-2 py-1">';
			echo $success_message;
			echo "</span>";
			}
		?>
		<div id="error_message" class="float-end rounded text-white bg-danger px-2 py-1"></div>
		</h4>

	</form>
</div></div> <!-- end of card-body and card -->

<a class="btn btn-info rounded" href="patronEdit.php?ID=<?php echo $patronID-1; ?>"><i class="fa fa-arrow-left"></i></a> Patron 
<a class="btn btn-info rounded" href="patronEdit.php?ID=<?php echo $patronID+1; ?>"><i class="fa fa-arrow-right"></i></a>
<br clear="both">

<div class="card border-success mt-3">
<div class="card-body">
	<div class="card-head alert alert-success"> <h2>Library Cards
	<a class="float-end btn btn-outline-success rounded" href="cardAdd.php"><i class="fa fa-circle-plus"></i>  Add Card</a></h2>
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
