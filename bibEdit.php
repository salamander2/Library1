<?php
/*******************************************************
* bibEdit.php
* called from : bibSearch.php
* calls: bibUpdate.php, bibDelete.php, holdingsAdd.php
* This displays the title data for editing.
* It also shows the copies (holdings). 
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - BIB Edit");
	header("location:main.php");
}
/********************************************************/

$bibID = filter_var($_GET['ID'], FILTER_SANITIZE_NUMBER_INT);

if (strlen($bibID) == 0) header("Location:bibSearch.php"); 

$bibData = "";

$sql = "SELECT * FROM bib WHERE id = ?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $bibID);
	$stmt->execute(); 
	$bibData = $stmt->get_result()->fetch_assoc();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

//someone is trying to look at a bib record that doesn't exist
if ($bibData == null) {
	$_SESSION['notify'] = array("type"=>"error", "message"=>"That book  does not exist!");
	header("Location:bibSearch.php");
}

/************ Get the Holdings records for this BIB *********/
#$sql = "SELECT * FROM holdings where bibID = ?";
//This will get the name of the patron if the book is out.
$sql = "SELECT holdings.*, patron.lastname, patron.firstname FROM holdings LEFT JOIN patron on holdings.patronID = patron.id WHERE holdings.bibID = ?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $bibID);
	$stmt->execute(); 
	$holdings = $stmt->get_result(); //->fetch_assoc();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

//We also want to find the previous patron name. I think it's too hard to get the prev. patron name, so we'll do a separate search later.
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
		return true;
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
<form id="myForm" action="bibUpdate.php" method="POST" onsubmit="return validateForm()">

	<div class="row my-2">
		<div class="col-12"><div class="input-group rounded">
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

	<div class="row">
		<div class="col-sm-6 col-md-4 my-2">
			<div class="input-group rounded">
			<label for="pubDate" class="input-group-prepend btn btn-secondary">Pub. Date</label>
			<input class="form-control bgS rounded-end" type="text" id="pubDate" name="pubDate" required value="<?=$bibData['pubDate']?>"><span class="text-danger">&nbsp;*</span>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 my-2">
			<div class="input-group rounded">
			<label for="callNumber" class="input-group-prepend btn btn-secondary">Call NUmber</label>
			<input class="form-control bgS rounded-end" type="text" id="callNumber" name="callNumber" value="<?=$bibData['callNumber']?>">
			</div>
		</div>
		<div class="col-sm-6 col-lg-6 col-xl-4 my-2">
			<div class="input-group rounded">
			<label for="ISBN" class="input-group-prepend btn btn-secondary">ISBN</label>
			<input class="form-control bgS rounded-end" type="text" id="ISBN" name="ISBN" value="<?=$bibData['ISBN']?>">
			</div>
		</div>
	</div>

	<input type="hidden" id="id" name="id" value="<?=$bibID?>">

	<br clear="both">
	<button type="submit" name="submit" id="submit" class="btn btn-warning">Submit</button>

</form>
</div>
<!-- ******** Anchor for Javascript and PHP notification popups ********** -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
<!-- ********************************************************************* -->
		

</div> <!-- end of card-body and card -->

<a class="btn btn-outline-dark rounded" href="bibEdit.php?ID=<?php echo $bibID-1; ?>"><i class="fa fa-arrow-left"></i></a> Title 
<a class="btn btn-outline-dark rounded" href="bibEdit.php?ID=<?php echo $bibID+1; ?>"><i class="fa fa-arrow-right"></i></a>

<div class="card border-success mt-3">
<div class="card-body">
	<div class="card-head alert alert-success"> 
	<h2>Copies <span class="smaller">&mdash; Holdings record</span>
<?php
	//Using a button instead of a form.		 echo "<td><button type=\"submit\" onclick=\"updateRow(".$id.")\">Update</button></td>".PHP_EOL;
	echo '<a class="float-end btn btn-outline-success rounded" Xhref="holdingsAdd.php?id='.$bibID.'"><i class="fa fa-circle-plus"></i>  Add Copy</a>';
?>
	</h2></div>

<?php

$num_rows = mysqli_num_rows($holdings);
if($num_rows > 0) {
	// printing table rows: student name, student number
	echo '<table class="table table-secondary Xtable-striped table-hover table-bordered">';
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
		$patron = "";
		if ($copy['patronID'] != NULL) $patron = $copy['lastname'].", ".$copy['firstname'];
		$prevPatron = $copy['prevPatron'];

		//Get name of previous patron
		if ($prevPatron != "") {
			$sql = "SELECT lastname, firstname FROM patron WHERE patron.id = ?";
			if ($stmt = $db->prepare($sql)) {
				$stmt->bind_param("i", $prevPatron);
				$stmt->execute(); 
				$stmt->bind_result($prevNameL,$prevNameF);
				$stmt->fetch(); 
				$stmt->close();                 
			} else {
				die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
			}
		}

		echo "<tr class='align-middle'>";
		echo "<td>".$barcode. "</td>";
		echo "<td class=\"$status\">".$status."</td>";
		//echo "<td>".$copy['patronID']."<br>(".$prevPatron.")</td>";
		echo "<td>".$patron;
#		echo "<td>".$copy['patronID'];
		if ($prevPatron != "") echo"<br>($prevNameL, $prevNameF)";								//(".$prevPatron.")";
		echo "</td>";
		echo "<td>".$cost. "</td>";
		echo "<td>".$copy['dueDate'];
		if ($status != "IN") echo "<br>(".$copy['ckoDate'].")";
		echo "</td>";
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
