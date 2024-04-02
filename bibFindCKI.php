<?php
/*******************************************************************************
  Name: bibFindCKI.php
  Called from: checkin.php
  Calls:
  Links to: ?

  ** AJAX Version **

  Purpose: uses search criteria passed in (via POST) to find matching books.
         - returns valid data to checkin.php via AJAX. 
  NOTE: this is different from "bibFind.php" because (i) it searches both Title and Author at one
		(ii) it also displays barcodes and statuses of holdings
  NOTE: it does not use NOTIFY array to send back error messages, because they are going back via AJAX.
		The calling program has to detect ERROR and LOCATION
 ******************************************************************************/
session_start();
require_once('common.php');

if (!isset($_SESSION["authkey"]) || $_SESSION["authkey"] != AUTHKEY) {
	echo "LOGOUT";
	return;
}

// Get the query parameter from the URL
$q = clean_input($_REQUEST["q"]);

$q = $q.'%';
$q2 = $q;

//$sql = "SELECT id as bibID, LEFT(title,30) AS title, author, pubDate, callNumber, barcode, status FROM bib INNER JOIN holdings ON bib.id = holdings.bibID WHERE title LIKE ? OR author LIKE ?";
$sql = <<<"SQL"
	SELECT id as bibID, LEFT(title,30) AS title, author, pubDate, callNumber, barcode, status FROM bib 
	INNER JOIN holdings ON bib.id = holdings.bibID 
	WHERE title LIKE ? OR author LIKE ?
	SQL;

if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("ss", $q, $q2);
	$stmt->execute(); 
	$resultArray = $stmt->get_result();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

//general HTML now being written
echo '<p class="text-primary">Please click on the desired barcode to check it in.';
echo '<table class="table Xtable-secondary Xtable-striped Xtable-hover table-bordered">';
echo '<thead>';
echo '<tr>';
echo '<th>Author</th>';
echo '<th>Title</th>';
echo '<th>Pub. date</th>';
echo '<th>Call Number</th>';
echo '<th>Barcode</th>';
echo '<th>Status</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// printing table rows: student name, student number
$prevID="-1";
while ($row = mysqli_fetch_assoc($resultArray)){ 
	
	$currID = $row['bibID'];
	$tit = $row['title'];
	//Note: this is done in the SQL statement  just to see how it's done
	//if (strlen($tit) > 74) $tit = substr($tit,0,70)." ...";
	//FIXME: need an ONLCICK to a function. This would popup  an "Are you sure?" confirm if the status is not OUT
	echo "<tr>";
	if ($prevID != $currID) {
		echo "<td>".$row['author']."</td>";
		echo "<td>".$tit. "</td>";
		echo "<td>".$row['pubDate']. "</td>";
		echo "<td>".$row['callNumber']. "</td>";
	} else {
		echo "<td colspan=4 style=\"background-color:#EEE\"></td>";
	}
/*	//NOTE: checkin.php will NOT checkin a book with any status other than OUT
	$onclick1 = <<<END
		onclick="if(confirm('Book is not OUT. Are you sure? '))  
		window.document.location='checkin.php?barcode={$row['barcode']}';"
		END;
*/
	$onclick1 = <<<END
		onclick="window.alert('Book is not OUT. It cannot be checked in.');"
		END;
	$onclick2 = <<<END
		onclick="window.document.location='checkin.php?barcode={$row['barcode']}';"
		END;
	if ($row['status'] == "OUT") {
		echo "<td class=\"bg-success\" $onclick2 ><b>".$row['barcode']. "</b></td>";
		echo "<td class=\"bg-success\" $onclick2 >".$row['status']. "</td>";
	} else {
		//echo "<td onclick=\"window.document.location='bibEdit.php?ID=". $row['bibID'] . "';\" ><b>".$row['barcode']. "</b></td>";
		echo "<td $onclick1 ><b>".$row['barcode']. "</b></td>";
		echo "<td $onclick1 >".$row['status']. "</td>";
	}
	echo "</tr>";

	$prevID = $currID;
} //this is the end of the while loop

echo '</tbody>';
echo '</table>';

mysqli_free_result($resultArray);
?>


