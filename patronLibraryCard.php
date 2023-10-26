<?php
/*******************************************
* patronLibraryCard.php
* This is called via AJAX from patronEdit.php
* It lists all of the library cards that the patron has
* as well as their statuses.
* The code is from cardStatus.php
*********************************************/
session_start();
require_once('common.php');

if (isset($_GET['patron'])) $patronID = $_GET['patron'];
else  return;

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

echo '<div class="card-head alert fg2 bg2"> <h2>Library Cards';
	if ($validCard == false) {
		echo '<a class="float-end btn btn-outline-success rounded" href="cardAdd.php?id='.$patronID.'"><i class="fa fa-circle-plus"></i>  Add Card</a>';
	}
echo '</h2></div>';


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
			echo "<button onclick='updateCardStatus(".$barcode.",\"L\")' class='btn btn-outline-danger shadow'>Lost</button> &nbsp; ".PHP_EOL;
		if ($status == 'EXPIRED' && !$validCard) 
			echo "<button onclick='updateCardStatus(".$barcode.",\"R\")' class='btn btn-outline-success shadow'>Renew</button> &nbsp; ".PHP_EOL;
		if ($status == 'LOST') 
			echo "<button onclick='updateCardStatus(".$barcode.",\"A\")' class='btn btn-outline-primary shadow'>Found</button> &nbsp; ".PHP_EOL;
		echo "</td>";
		echo "</tr>";
	} 

	echo '</tbody>';
	echo '</table>';
}
?>

