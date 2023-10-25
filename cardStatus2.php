<?php
/*******************************************************
 * This is the AJAX version of cardStatus.php
 * cardStatus2.php 
 * Called from: patronEdit.php
 * This updates the Library Card status

 * INCOMPLETE:  I just need to copy the large chunk of code that 
   generates that library card table from "patronEdit.php". (line 310-355)
   as well as the code that grabs all of the cards for the patron.

* and then in patronEdit, I'll need to change it so that it uses AJAX 
 ********************************************************/
session_start();
require_once('common.php');

$barcode=$stCode=$patronID="";
$barcode = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

//This should never happen, but we have to make sure that there is a valid barcode
if (strlen($barcode) != 10) {
	echo 'ERROR Invalid barcode.';
	return;
}

if (isset($_GET['status'])) $stCode = $_GET['status'];
if (isset($_GET['patron'])) $patronID = $_GET['patron'];

$status = "";
switch($stCode) {
	case "L":
		$status = "LOST";
		break;
	case "R":
	case "A":
		$status = "ACTIVE";
		break;
	default:
		echo 'ERROR Invalid status.';
		return;
}

$sql = "UPDATE libraryCard SET status=? WHERE barcode=?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("si", $status, $barcode );
	$stmt->execute();
	$stmt->close();
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

$newDate = date('Y-m-d', strtotime('+ 1 year'));
//also update the expiry date for cards that are being renewed.
if ($stCode == "R") {
	$sql = "UPDATE libraryCard SET expiryDate=? WHERE barcode=?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("si", $newDate, $barcode );
		$stmt->execute();
		$stmt->close();
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
}

#$_SESSION['notify'] = array("type"=>"success", "message"=>"Library Card status changed.");

//header("location:patronEdit.php?ID=$patronID");
//GET THE LIST OF ALL THE CARDS FOR THE PATRON
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



//GENERATE HTML FOR AJAX
$num_rows = mysqli_num_rows($libCards);
if($num_rows > 0) {
	// printing table rows: student name, student number
	echo '<table class="table table-secondary table-striped table-hover table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Barcode</th>';
	echo '<th>Status</th>';
	echo '<th>Date Issued</th>';
	echo '<th>Expiry Date</th>';
	echo '<th>Change status to:</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	// Reset our pointer since we've already done fetch_assoc
	mysqli_data_seek( $libCards, 0 );

	while ($card = $libCards->fetch_assoc()){ 
		$status = $card['status'];
		$barcode = $card['barcode'];
		echo "<tr>";
		echo "<td>".$barcode. "</td>";
		echo "<td>".$status."</td>";
		echo "<td>".strtok($card['createDate']," "). "</td>";
		echo "<td>".$card['expiryDate']. "</td>";
		echo '<td class="btns">';
		//for the status change buttons, we need to send barcode, new status, and patronID. It's shorter just to write the GET URL instead of a POST FORM.
		if ($status == "ACTIVE") 
			echo "<a href='cardStatus.php?id=".$barcode."&status=L&patron=".$patronID."'><button class='btn btn-outline-danger shadow'>Lost</button></a> &nbsp; ".PHP_EOL;
		if ($status == 'EXPIRED' && !$validCard) 
			echo "<a href='cardStatus.php?id=".$barcode."&status=R&patron=".$patronID."'><button class='btn btn-outline-success shadow'>Renew</button></a> &nbsp; ".PHP_EOL;
			#echo "<form class='d-inline' method='POST' action='cardStatus.php'><input name='id' value='$barcode' hidden><input name='status' value='R' hidden><button class='btn btn-success shadow'>Renew</button></form> &nbsp; ".PHP_EOL;
		if ($status == 'LOST') 
			echo "<a href='cardStatus.php?id=".$barcode."&status=A&patron=".$patronID."'><button class='btn btn-outline-primary shadow'>Found</button></a> &nbsp; ".PHP_EOL;
			#echo "<form class='d-inline' method='POST' action='cardStatus.php?='id' value='$barcode' hidden><input name='status' value='A' hidden><button class='btn btn-primary shadow'>Found</button></form> &nbsp; ".PHP_EOL;
		echo "</td>";
		echo "</tr>";
	} 

	echo '</tbody>';
	echo '</table>';
}
