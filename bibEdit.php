<?php
/*******************************************************
* bibEdit.php
* called from : bibSearch.php
* calls: xxx
* This displays the title data for editing.
* It also shows the copies (holdings). 
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

$bibID = filter_var($_GET['ID'], FILTER_SANITIZE_NUMBER_INT);

//FIXME add message when returning. PatronList needs to handle messages.
if (strlen($bibID) == 0) header("Location:bibSearch.php"); 

$bibData = "";

$sql = "SELECT * FROM bib WHERE id = ?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $bibID);
	$stmt->execute(); 
	$bibData = $stmt->get_result()->fetch_assoc();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
	$message_ .= 'SQL2: ' . $query;
	die($message_); 
}

//someone is trying to look at a bib record that doesn't exist
//FIXME add message when returning. PatronList needs to handle messages.
if ($bibData == null) header("Location:bibSearch.php");


$sql = "SELECT * FROM holdings where bibID = ?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $bibID);
	$stmt->execute(); 
	$holdings = $stmt->get_result(); //->fetch_assoc();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
	$message_ .= 'SQL2: ' . $query;
	die($message_); 
}

/*  describe bib;
+------------+-----------------+------+-----+-------------------+-------------------+
| Field      | Type            | Null | Key | Default           | Extra             |
+------------+-----------------+------+-----+-------------------+-------------------+
| id         | int unsigned    | NO   | PRI | NULL              | auto_increment    |
| title      | varchar(255)    | NO   |     | NULL              |                   |
| author     | varchar(50)     | NO   |     | NULL              |                   |
| pubDate    | int             | NO   |     | NULL              |                   |
| ISBN       | bigint unsigned | YES  |     | NULL              |                   |
| callNumber | varchar(50)     | YES  |     | NULL              |                   |
| subjects   | varchar(200)    | YES  |     | NULL              |                   |
| createDate | timestamp       | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+-----------------+------+-----+-------------------+-------------------+
*/

/* describe holdings;
+------------+--------------+------+-----+-------------------+-------------------+
| Field      | Type         | Null | Key | Default           | Extra             |
+------------+--------------+------+-----+-------------------+-------------------+
| barcode    | int unsigned | NO   | PRI | NULL              | auto_increment    |
| bibID      | int unsigned | NO   | MUL | NULL              |                   |
| patronID   | int unsigned | YES  | MUL | NULL              |                   |
| cost       | int unsigned | NO   |     | NULL              |                   |
| status     | varchar(20)  | NO   | MUL | NULL              |                   |
| ckoDate    | date         | YES  |     | NULL              |                   |
| dueDate    | date         | YES  |     | NULL              |                   |
| prevPatron | int unsigned | YES  | MUL | NULL              |                   |
| createDate | timestamp    | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+--------------+------+-----+-------------------+-------------------+

*/
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
	<!-- <script src="resources/jquery-3.7.1.min.js"></script> -->
    <link rel="stylesheet" href="resources/library.css" >

<script>
document.addEventListener("DOMContentLoaded", () => {
  // anonymous inner (lambda) function goes here
}); 

/* Javascript input validation:
	When possible, it's best to use JS validation. PHP validation is server based and slower.
	PHP validation is still necessary, however, as Postman or similar apps can submit invalid information.
	We never have to make sure that the fields are filled in because "required" does that just fine.
	So validate the actual data.
	Jquery validation is not really worth it - unless you add in the validation plugin/library.
	Validate: (1) email, (2) year of birth (patron must be between 6 and 120 years old
	(3) Prov. two letters, capitalize them (4) Phone: 10 digits when () and - are removed.
	
	PHONE:  var phoneno = /^\d{10}$/;
	  if (!(inputtxt.value.match(phoneno)) return false;

	EMAIL: 
		var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		if(! inputText.value.match(mailformat)) ...
}
 
*/

	function validateForm(){
		const inputs = ["firstname", "lastname", "birthdate", "address", "city", "prov", "postalCode"];
		let retval = true;

		//make sure all the the inputs are filled
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
/*		//validate email
		let email = document.getElementById("email");
		if(validateEmail(email.value.trim())){
			email.className = "form-control is-valid";
		} else {
			email.className = "form-control is-invalid";
			retval = false;
		}
*/
		if (retval === false) document.getElementById("error_message").innerHTML = "Invalid Input";
		return retval;
	}
	 
</script>

</head>
<body>

<div class="container-md mt-2">

<!-- page header -->
<?php loadHeader("bibSearch.php"); ?>

<div class="card border-success mt-3">
	<div class="card-head alert alert-success mb-0"> 
	<h2>Title Information <span class="smaller">&mdash; Bib record
		<a class="float-end btn btn-outline-danger rounded" href="bibDelete.php"><i class="fa fa-circle-minus"></i>  Delete Title</a>
	</h2>
	</div>

<div class="card-body">
	<div class="row text-secondary smaller">
		<div class="col-sm-2">ID: <?=$bibID?></div><div class="col-sm-6"></div><div class="col-sm-4 text-end"> Date added: <?php echo strtok($bibData['createDate'], " ")?></div>
	</div>
<form id="myForm" Xaction="bibUpdate.php" method="POST" onsubmit="return removeTHE()">

	<div class="row my-2">
		<div class="col-12"><div class="input-group rounded">
			<!-- FIXME title needs to be textarea -->
			<label for="title" class="input-group-prepend btn btn-success">Title</label>
			<input class="form-control bgU rounded-end" type="text" id="title" name="title" required value="<?=$bibData['title']?>"><span class="text-danger">&nbsp;*</span>
		</div></div>
	</div>

	<div class="row my-2">
		<div class="col-md-6"><div class="input-group rounded">
			<label for="author" class="input-group-prepend btn btn-success">Author</label>
			<input class="form-control bgU rounded-end" type="text" id="author" name="author" required value="<?=$bibData['author']?>"><span class="text-danger">&nbsp;*</span>
		</div></div>
	</div>

	<div class="row my-2">
		<div class="col-md-6"><div class="input-group rounded">
			<label for="subjects" class="input-group-prepend btn btn-success">Subject</label>
			<input type="text" class="form-control bgU rounded-end" name="subjects" name="subjects" disabled placeholder="Subject field not available" readonly>
		</div></div>
		<span class="smaller text-secondary">&nbsp;&nbsp;&nbsp;Sorry, the database does not contain "subjects" for the books.</span> 
	</div>

	<div class="row my-2">
		<div class="col-sm-6 col-md-4">
			<div class="input-group rounded">
			<label for="pubDate" class="input-group-prepend btn btn-secondary">Pub. Date</label>
			<input class="form-control bgS rounded-end" type="text" id="pubDate" name="pubDate" required value="<?=$bibData['pubDate']?>"><span class="text-danger">&nbsp;*</span>
			</div>
		</div>
		<div class="col-sm-6 col-md-4">
			<div class="input-group rounded">
			<label for="callNumber" class="input-group-prepend btn btn-secondary">Call NUmber</label>
			<input class="form-control bgS rounded-end" type="text" id="callNumber" name="callNumber" value="<?=$bibData['callNumber']?>">
			</div>
		</div>
		<div class="col-sm-6 col-lg-6 col-xl-4">
			<div class="input-group rounded">
			<label for="ISBN" class="input-group-prepend btn btn-secondary">ISBN</label>
			<input class="form-control bgS rounded-end" type="text" id="ISBN" name="ISBN" value="<?=$bibData['ISBN']?>">
			</div>
		</div>
	</div>

	<input type="hidden" id="id" name="id" value="<?=$bibID?>">

	<br clear="both">
	<h4><button type="submit" name="submit" id="submit" class="btn btn-warning">Submit</button>
		<!-- This is the JAVASCRIPT error message -->
		<div id="error_message"></div>
		<!-- This is the PHP error message -->
		<?php if ($error_message != "") echo $error_message; ?>
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

<a class="btn btn-outline-dark rounded" href="bibEdit.php?ID=<?php echo $bibID-1; ?>"><i class="fa fa-arrow-left"></i></a> Title 
<a class="btn btn-outline-dark rounded" href="bibEdit.php?ID=<?php echo $bibID+1; ?>"><i class="fa fa-arrow-right"></i></a>

<div class="card border-success mt-3">
<div class="card-body">
	<div class="card-head alert alert-success"> 
	<h2>Copies <span class="smaller">&mdash; Holdings record</span>
<?php
	//Using a button instead of a form.		 echo "<td><button type=\"submit\" onclick=\"updateRow(".$id.")\">Update</button></td>".PHP_EOL;
	echo '<a class="float-end btn btn-outline-success rounded" Xhref="cardAdd.php?id='.$bibID.'"><i class="fa fa-circle-plus"></i>  Add Copy</a>';
?>
	</h2></div>

<?php

$num_rows = mysqli_num_rows($holdings);
if($num_rows > 0) {
	// printing table rows: student name, student number
	echo '<table class="table table-secondary table-striped table-hover table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Barcode</th>';
	echo '<th>Status</th>';
	echo '<th>Patron<br>(Prev. Patron)</th>';
	echo '<th>Cost</th>';
	echo '<th>Due Date<br>(Checkout date)</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	// Reset our pointer since we've already done fetch_assoc
	mysqli_data_seek( $holdings, 0 );

	while ($copy = $holdings->fetch_assoc()){ 
		$status = $copy['status'];
		$barcode = $copy['barcode'];
		$cost = '$'.($copy['cost']/100);
		echo "<tr>";
		echo "<td>".$barcode. "</td>";
		echo "<td>".$status."</td>";
		echo "<td>".$copy['patronID']."<br>(".$copy['prevPatron'].")</td>";
		echo "<td>".$cost. "</td>";
		//echo "<td>".strtok($copy['createDate']," "). "</td>";
		echo "<td>".$copy['dueDate']."<br>(".$copy['ckoDate'].")</td>";
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
