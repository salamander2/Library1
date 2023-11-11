<?php
/*******************************************************************************
  Name: bibFindPAC.php
  Called from: PAC.php
  Calls:
  ## same as bibFind, but no barcode search

  Purpose: uses search criteria passed in (via POST) to find matching books.
         - returns valid data to bibSearch via AJAX. 
		 - ISBN will not go directly to bibEdit.php but instead will show a list
		   since our data has duplicate ISBNs.
  NOTE: it does not use NOTIFY array to send back error messages, because they are going back via AJAX.
		The calling program has to detect ERROR and LOCATION
 ******************************************************************************/
session_start();
require_once('common.php');

//Cannot initialize all variables here as they are hidden from the functions
$ISBN="";

if (isset($_POST['ISBN']))  $ISBN = filter_var($_POST['ISBN'], FILTER_SANITIZE_NUMBER_INT);

if (strlen($ISBN) > 0) {
	$resultArray = queryISBN();
} else {
	$resultArray = queryMain();
}

function queryISBN() {
	global $db, $ISBN;
	//Find all BIB records
	$sql = "SELECT id as bibID, title, author, pubDate, ISBN, callNumber, subjects, createDate FROM bib WHERE ISBN = ?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("i", $ISBN);
		$stmt->execute(); 
		$resultArray = $stmt->get_result();
		$stmt->close();                 
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
    return $resultArray;
}

function queryMain() {
	global $db;
//Initialize all input variables (for POST form)
	$title=$title2=$author="%";
	$sortBy = "author, pubdate";
	if (isset($_POST['title2']))     { $title = clean_input($_POST['title2']) .'%'; $title2 = "THE ".$title; $sortBy = "title, ".$sortBy;}
	if (isset($_POST['title']))      { $title = clean_input($_POST['title']) .'%'; $title2 = "THE ".$title;}
	if (isset($_POST['author']))       $author = clean_input($_POST['author']) .'%';

	$sql = "SELECT id as bibID, title, author, pubDate, ISBN, callNumber, createDate FROM bib WHERE (title LIKE ? OR title LIKE ?) AND author LIKE ? ORDER BY $sortBy";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("sss", $title, $title2, $author);
		$stmt->execute(); 
		$resultArray = $stmt->get_result();
		$stmt->close();                 
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
	return $resultArray;
}

//general HTML now being written
echo '<p class="text-primary"><i class="fa fa-arrow"></i>Please click on the desired book to view details.';
echo '<table class="table table-secondary table-striped table-hover table-bordered">';
echo '<thead>';
echo '<tr>';
echo '<th>Title</th>';
echo '<th>Author</th>';
echo '<th>Pub. date</th>';
echo '<th>Call Number</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// printing table rows: student name, student number
while ($row = mysqli_fetch_assoc($resultArray)){ 
	
	$tit = $row['title'];
	if (strlen($tit) > 74) $tit = substr($tit,0,70)." ...";
	echo "<tr>";
	echo "<td onclick=\"window.document.location='bibView.php?ID=". $row['bibID'] . "';\" >".$tit. "</td>";
	echo "<td onclick=\"window.document.location='bibView.php?ID=". $row['bibID'] . "';\" >".$row['author']."</td>";
	echo "<td onclick=\"window.document.location='bibView.php?ID=". $row['bibID'] . "';\" >".$row['pubDate']. "</td>";
	echo "<td onclick=\"window.document.location='bibView.php?ID=". $row['bibID'] . "';\" >".$row['callNumber']. "</td>";
	echo "</tr>";

} //this is the end of the while loop

echo '</tbody>';
echo '</table>';

mysqli_free_result($resultArray);
?>


