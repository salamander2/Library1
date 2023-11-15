<?php
/*******************************************************
* This is AJAX version : using AJAX to update card status
* patronEdit.php
* called from patronList.php (by clicking on a patron)
* 		 and also from patronUpdate and patronAdd
* calls patronUpdate.php
* This displays the patron data for editing.
* It also displays library cards, and books out.
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Patron Edit");
	header("location:main.php");
}
/********************************************************/

$patronID = filter_var($_GET['ID'], FILTER_SANITIZE_NUMBER_INT);
if (strlen($patronID) == 0) {
	$_SESSION['notify'] = array("type"=>"error", "message"=>"Invalid patron id. That patron doesn't exist.");	
	header("Location:patronList.php"); 
}

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

//someone is trying to look at a patron record that doesn't exist
//FIXME add message when returning. PatronList needs to handle messages.
if ($patronData == null) {
	$_SESSION['notify'] = array("type"=>"error", "message"=>"Invalid patron id.");	
	header("Location:patronList.php");
}

//TODO Postal code: needs to be split into two parts. Need JS to check input for it (and remove all spaces)

/*  PATRON'S LIBRARY CARD DATA
+------------+---------------------------------+------+-----+-------------------+-------------------+
| Field      | Type                            | Null | Key | Default           | Extra             |
+------------+---------------------------------+------+-----+-------------------+-------------------+
| barcode    | int unsigned                    | NO   | PRI | NULL              | auto_increment    |
| patronId   | int unsigned                    | NO   | MUL | NULL              |                   |
| status     | enum('ACTIVE','LOST','EXPIRED') | NO   |     | VALID             |                   |
| expiryDate | date                            | YES  |     | NULL              |                   |
| createDate | timestamp                       | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+---------------------------------+------+-----+-------------------+-------------------+
*/
$sql = "SELECT * FROM libraryCard WHERE patronID = ? ORDER BY expiryDate DESC";
 
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $patronID);
	$stmt->execute(); 
	$libCards = $stmt->get_result(); //->fetch_assoc();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

$validCard=false;
while ($card = $libCards->fetch_assoc()){ 
	if ($card['status'] == 'ACTIVE') $validCard = true;
}

/* PATRON: LIST OF BOOKS CHECKED OUT */
//call_number!!! needs to be changed in SQL to callNumber
$sql = "SELECT holdings.barcode, holdings.status, holdings.dueDate, holdings.bibID, bib.title, bib.author, bib.callNumber FROM holdings INNER JOIN bib ON holdings.bibID = bib.id WHERE holdings.patronID = ?;";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $patronID);
	$stmt->execute(); 
	$booksOut = $stmt->get_result(); //->fetch_assoc();
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
	<!-- <script src="resources/jquery-3.7.1.min.js"></script> -->
    <link rel="stylesheet" href="resources/library.css" >
	<script src="resources/library.js"></script>		

<script>
<?php echo "const patronID = $patronID;"; ?>

document.addEventListener("DOMContentLoaded", () => {
	getLibraryCards();
});

//This function grabs all of the cards that the patron has and diplays them with the appropriate buttons.
function getLibraryCards() {
	const xhr = new XMLHttpRequest();
	xhr.onload = () => {
		//The responseText can begin with "ERROR". If so, it is handled differently
		if (xhr.responseText.startsWith("ERROR ")) {
			errorMsg = xhr.responseText.replace("ERROR ","");
			displayNotification("error", errorMsg);
			return;
		}
		document.getElementById("libCards").innerHTML = xhr.responseText;
	}
	xhr.open("GET", "patronLibraryCard.php?&patron="+patronID);
	xhr.send();
}

//This function changes the status of a specific card
function updateCardStatus(barcode, newStatus) {

	const xhr = new XMLHttpRequest();
	xhr.onload = () => {
		//The responseText can begin with "ERROR". If so, it is handled differently
		if (xhr.responseText.startsWith("ERROR ")) {
			errorMsg = xhr.responseText.replace("ERROR ","");
			displayNotification("error", errorMsg);
			return;
		}
		if (xhr.responseText.startsWith("SUCCESS ")) {
			errorMsg = xhr.responseText.replace("SUCCESS ","");
			displayNotification("success", errorMsg);
			getLibraryCards();
			return;
		}
	}
	xhr.open("GET", "cardStatus.php?&id="+barcode+"&patron="+patronID+"&status="+newStatus);
	xhr.send();
	//getLibraryCards();
}


//FIXME: I need to write a general function for this. It's too much repeated code.
	function validateForm() {
		const inputs = ["firstname", "lastname", "birthdate", "address", "city", "prov", "postalCode"];

		//Make sure all the the inputs are filled. This is actually done by the "required" attribute in <input>
		let retval = true;
		inputs.forEach( function(input) {
			let element = document.getElementById(input);
			console.log(input);
			if(element.value === "") {
				element.className = "form-control is-invalid";
				retval = false;
			} else {
				element.className = "form-control is-valid";
			}
		});
		if (retval === false) {
			document.getElementById("error_message").innerHTML = "Missing Input";
			return false;
		}

		//validate email if it exists
		const email = document.getElementById("email");
		email.className = "form-control is-valid";
		let emailText = email.value.trim();
		//if (emailText.length > 0) {
		if (emailText != "") {
			let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
			if(! emailText.match(mailformat)) {
				email.className = "form-control is-invalid";
				document.getElementById("error_message").innerHTML = "Email is invalid";
				return false;
			} 		
		}

		//validate PROV.
		const prov = document.getElementById("prov");
		prov.className = "form-control is-valid";
		let provText = prov.value.trim().toUpperCase();
		if (! provText.match('^[A-Z]{2}$')) {
			prov.className = "form-control is-invalid";
			document.getElementById("error_message").innerHTML = "Province is invalid";
			return false;
		}

/*
		//FIXME this does not work!
		let regex = '^[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$';
		const phone = document.getElementById("phone");
		phone.className = "form-control is-valid";
		let phoneText = prov.value.trim();
		if (! phoneText.match(regex)) {
			phone.className = "form-control is-invalid";
			document.getElementById("error_message").innerHTML = "Invalid phone number format";
			return false;
		}
*/

		return true;
	}

	function resetPWD(n) {
        window.alert("delete " + n);
    } 

</script>

</head>
<body>

<div class="container-md mt-2">

<!-- page header -->
<?php loadHeader("patronList.php"); ?>

<div class="card border-primary mt-3">
	<div class="card-head alert alert-primary mb-0"> <h2>Patron Information
<!-- <button type="button" name="resetPWD" class="btn btn-secondary"
  onclick="if(confirm('Please confirm password reset ')) resetPWD(88);">Reset Password</button>
-->                                   
	<a class="float-end btn btn-outline-danger rounded" onclick="if(confirm('Please confirm deletion ')) " href="patronDelete.php"><i class="fa fa-circle-minus"></i>  Delete Patron</a></h2>
	</div>

<div class="card-body">
	<form action="patronUpdate.php" onsubmit="return validateForm()" method="post">
		<div class="row text-secondary">
		<div class="col-sm-2">ID: <?=$patronID?></div><div class="col-sm-6"></div><div class="col-sm-4 text-end"> Date added: <?php echo strtok($patronData['createDate'], " ")?></div>
		</div>
		
		<div class="row">
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="lastname" class="input-group-prepend btn btn-info">Last name</label>
				<input class="form-control bgP rounded-end" type="text" id="lastname" name="lastname" required value="<?=$patronData['lastname']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-4">
				<div class="input-group rounded">
				<label for="firstname" class="input-group-prepend btn btn-info">First name</label>
				<input class="form-control bgP rounded-end" type="text" id="firstname" name="firstname" required value="<?=$patronData['firstname']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>
		<div class="row mt-2">
		<div class="col-sm-8 col-md-6 col-lg-4">
			<div class="input-group rounded">
			<label for="birthdate" class="input-group-prepend btn btn-info">Birth date</label>
			<input class="form-control bgP rounded-end" type="date" id="birthdate" name="birthdate" required value="<?=$patronData['birthdate'] ?>"><span class="text-danger">&nbsp;*</span>
		</div></div></div>

		<h5 class="mt-3"><u>Address:</u></h5>
		<div class="row my-2">
			<div class="col-md-6">
				<div class="input-group rounded">
				<label for="address" class="input-group-prepend btn btn-secondary">Street</label>
				<input class="form-control bgS rounded-end" type="text" id="address" name="address" required value="<?=$patronData['address']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<div class="row my-2">
			<div class="col-sm-6 col-md-4">
				<div class="input-group rounded">
				<label for="city" class="input-group-prepend btn btn-secondary">City</label>
				<input class="form-control bgS rounded-end" type="text" id="city" name="city" required value="<?=$patronData['city']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-4 col-lg-3 col-xxl-2">
				<div class="input-group rounded">
				<label for="prov" class="input-group-prepend btn btn-secondary">Prov./State</label>
				<input class="form-control bgS rounded-end" type="text" id="prov" name="prov" required value="<?=$patronData['prov']?>"><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="input-group rounded">
				<label for="postalCode" class="input-group-prepend btn btn-secondary">Postal Code</label>
				<input class="form-control bgS rounded-end" type="text" id="postalCode" name="postalCode" required value="<?=$patronData['postalCode']?>"><span class="text-danger">&nbsp;*</span>
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
		<button type="submit" name="submit" id="submit" class="btn btn-success">Submit</button>

<!-- ******** Anchor for Javascript and PHP notification popups ********** -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
<!-- ********************************************************************* -->

	</form>
</div></div> <!-- end of card-body and card -->

<!-- 
<a class="btn btn-info rounded" href="patronEdit.php?ID=<?php echo $patronID-1; ?>"><i class="fa fa-arrow-left"></i></a> Patron 
<a class="btn btn-info rounded" href="patronEdit.php?ID=<?php echo $patronID+1; ?>"><i class="fa fa-arrow-right"></i></a>
-->

<div class="card border-success mt-3">
<div class="card-body">

<!-- *********** LIBRARY CARD CODE & CHANGING STATUS ********* -->
<div id="libCards">
</div>

<hr>

<?php
	/*********** List of books out (table) ***************/
	echo '<div class="card-head alert alert-success"> <h2>Books Out </h2></div>';


$num_rows = mysqli_num_rows($booksOut);
//Fields:  "SELECT holdings.barcode, holdings.status, holdings.dueDate, holdings.bibID, bib.title, bib.author, bib.callNumber .... ";
if($num_rows > 0) {
	// printing table rows: student name, student number
	echo '<table class="table table-secondary table-striped table-hover table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Title</th>';
	echo '<th>Author</th>';
	echo '<th>Barcode</th>';
	echo '<th>Status</th>';
	echo '<th>Due Date</th>';
	echo '<th>Call Number</th>';
	echo '</tr>';
	echo '</thead>';

	// Reset our pointer since we've already done fetch_assoc
	mysqli_data_seek( $booksOut, 0 );

	while ($row = $booksOut->fetch_assoc()){ 
		$status = $row['status'];
		$barcode = $row['barcode'];
		echo "<tr>";
		echo "<td>".$row['title']. "</td>";
		echo "<td>".$row['author']. "</td>";
		echo "<td>".$barcode. "</td>";
		echo "<td>".$status."</td>";
		echo "<td>".$row['dueDate']. "</td>";
		echo "<td>".$row['callNumber']. "</td>";
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
