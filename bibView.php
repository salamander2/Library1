<?php
/*******************************************************
* bibView.php
* called from : PAC.php
* calls: placeHold.php (eventually)
* This displays the title data and the number of copies available.
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF","PATRON", "PUBLIC");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - BIB Edit");
	header("location:main.php");
	exit;
}
/********************************************************/

$bibID = filter_var($_GET['ID'], FILTER_SANITIZE_NUMBER_INT);

if (strlen($bibID) == 0) {
	header("Location:PAC.php"); 
	exit;
}

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
	header("Location:PAC.php");
	exit;
}

/************ Get the Holdings records for this BIB *********/
//$sql = "SELECT holdings.*, patron.lastname, patron.firstname FROM holdings LEFT JOIN patron on holdings.patronID = patron.id WHERE holdings.bibID = ?";
$sql = "SELECT status, dueDate FROM holdings WHERE bibID = ? ORDER BY status ASC";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $bibID);
	$stmt->execute(); 
	$holdings = $stmt->get_result(); //->fetch_assoc();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

//see if "place hold" is available. As long as at least one is out and no copies are in.
$allowHold = false;
while ($copy = $holdings->fetch_assoc()){ 
	$status = $copy['status'];
	if ($status == "OUT") $allowHold = true;
}
mysqli_data_seek( $holdings, 0 );

if ($allowHold) { //at least one is out
	while ($copy = $holdings->fetch_assoc()){ 
		$status = $copy['status'];
		if ($status == "IN") $allowHold = false;
	}
	mysqli_data_seek( $holdings, 0 );
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
</head>
<body>

<div class="container-md mt-2">

<!-- page header -->
<div id="pageheader" class="alert alert-warning text-center rounded py-3">
	<!-- The spacing of the H2 and H1 can be aligned by adding a similar sized button at the beginning or by floating a button over the text (on the left) -->
	<!-- <div style="z-index:20; position:absolute;"><a class="btn btn-secondary" href="logout.php"><i class="fa fa-sign-out"></i>   Logout</a> </div> -->
	<a class="float-start btn btn-warning rounded" href="PAC.php"><i class="fa fa-arrow-left"></i>  Back</a>
	<a class="float-end btn btn-secondary" href="logout.php"><i class="fa fa-sign-out"></i>   Logout</a>
	<h2 class="fw-bold">The <?=$institution?> Public Libary</h2>
	<h1 class=""><i class="fa fa-xs fa-star-of-life"></i>&nbsp;Public Access Catalog&nbsp;<i class="fa fa-star-of-life fa-xs"></i></h1>
	<br clear="both">
    <hr class="py-0 mb-0">
</div>
<!-- end page header.-->


<div class="card border-success mt-3">
	<div class="card-head alert alert-success mb-0"> 
	<h2>Title Information</h2>
	</div>

<div class="card-body">
	<div class="row text-secondary smaller">
		<div class="col-sm-2">ID: <?=$bibID?></div><div class="col-sm-6"></div><div class="col-sm-4 text-end"> Date added: <?php echo strtok($bibData['createDate'], " ")?></div>
	</div>

	<div class="row my-2">
		<div class="col-12"><div class="input-group rounded">
			<!-- FIXME ? on smaller screens the title might run past the end. So maybe make it a textArea -->
			<label for="title" class="input-group-prepend btn btn-success">Title</label>
			<input class="form-control bgU rounded-end" type="text" id="title" name="title" readonly value="<?=$bibData['title']?>">
		</div></div>
	</div>

	<div class="row my-2">
		<div class="col-md-6"><div class="input-group rounded">
			<label for="author" class="input-group-prepend btn btn-success">Author</label>
			<input class="form-control bgU rounded-end" type="text" id="author" name="author" readonly value="<?=$bibData['author']?>">
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
			<input class="form-control bgS rounded-end" type="text" id="pubDate" name="pubDate" readonly value="<?=$bibData['pubDate']?>">
			</div>
		</div>
		<div class="col-sm-6 col-md-4 my-2">
			<div class="input-group rounded">
			<label for="callNumber" class="input-group-prepend btn btn-secondary">Call NUmber</label>
			<input class="form-control bgS rounded-end" type="text" id="callNumber" name="callNumber" readonly value="<?=$bibData['callNumber']?>">
			</div>
		</div>
		<div class="col-sm-6 col-lg-6 col-xl-4 my-2">
			<div class="input-group rounded">
			<label for="ISBN" class="input-group-prepend btn btn-secondary">ISBN</label>
			<input class="form-control bgS rounded-end" type="text" id="ISBN" name="ISBN" readonly value="<?=$bibData['ISBN']?>">
			</div>
		</div>
	</div>

</div>
<!-- ******** Anchor for Javascript and PHP notification popups ********** -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
<!-- ********************************************************************* -->
		

</div> <!-- end of card-body and card -->


<div class="card border-primary mt-3 col-md-6">
<div class="card-body">
	<div class="card-head alert alert-primary"> 
<?php

if ($allowHold) {
	echo "<a class=\"float-end btn btn-primary rounded\" href=\"placeHold.php?id=<?=$bibID?>\"><i class=\"fa fa-circle-plus\"></i>  Place Hold</a>";
}
echo "<h2>Status of Copies</h2>";
echo "</div>";


$num_rows = mysqli_num_rows($holdings);
if($num_rows > 0) {
	// printing table rows: student name, student number
	echo '<table class="table table-secondary Xtable-striped table-hover table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Status</th>';
	echo '<th>Due Date</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	// Reset our pointer since we've already done fetch_assoc
	mysqli_data_seek( $holdings, 0 );

	while ($copy = $holdings->fetch_assoc()){ 
		$status = $copy['status'];
		echo "<tr class='align-middle'>";
		echo "<td class=\"$status\">".$status."</td>";
		echo "<td>".$copy['dueDate']."</td>";
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
