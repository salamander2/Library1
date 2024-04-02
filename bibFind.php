<?php
/*******************************************************************************
  Name: bibFind.php
  Called from: bibSearch.php
  Calls:
  Links to: bibEdit.php

  ** AJAX Version **

  Purpose: uses search criteria passed in (via POST) to find matching books.
         - returns valid data to bibSearch.php via AJAX. 
  		 - if a match is found using a barcode, then that book is displayed (bibEdit.php)
         - If there is an error in the barcode, or no Bib record is found, an error message is returned (via AJAX)
		 - ISBN will not go directly to bibEdit.php but instead will show a list
		   since our data has duplicate ISBNs.
  NOTE: it does not use NOTIFY array to send back error messages, because they are going back via AJAX.
		The calling program has to detect ERROR and LOCATION
 ******************************************************************************/
session_start();
require_once('common.php');

if (!isset($_SESSION["authkey"]) || $_SESSION["authkey"] != AUTHKEY) {
	echo "LOGOUT";
	return;
}

/*
> describe bib;
+-------------+-----------------+------+-----+-------------------+-------------------+
| Field       | Type            | Null | Key | Default           | Extra             |
+-------------+-----------------+------+-----+-------------------+-------------------+
| id          | int unsigned    | NO   | PRI | NULL              | auto_increment    |
| title       | varchar(255)    | NO   |     | NULL              |                   |
| author      | varchar(50)     | NO   |     | NULL              |                   |
| pubDate     | int             | NO   |     | NULL              |                   |
| ISBN        | bigint unsigned | YES  |     | NULL              |                   |
| callNumber  | varchar(50)     | YES  |     | NULL              |                   |
| subjects    | varchar(200)    | YES  |     | NULL              |                   |
| createDate  | timestamp       | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+-------------+-----------------+------+-----+-------------------+-------------------+

> describe holdings;
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


//Initialize all input variables (for POST form)
$title=$title2=$author=$ISBN=$subjects="";
$callNumber=$barcode="";

if (isset($_POST['barcode'])) $barcode= filter_var($_POST['barcode'], FILTER_SANITIZE_NUMBER_INT);

//if a barcode is being searched for, ignore all of the other fields.
if (strlen($barcode) > 0) {
	if (strlen($barcode) != 10) {
		echo 'ERROR Invalid barcode.';
		return;
	}
	if (substr($barcode,0,5) != ("3".$libCode)) {
		echo "ERROR Barcode ($barcode) has invalid library code.";
		return;
	}
	//barcode is valid, now find the Bib record.
	$sql = "SELECT bibID FROM holdings WHERE barcode = ?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("i", $barcode );
		$stmt->execute(); 
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();                 
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
	if ($result == "") echo "ERROR No book with this barcode";
	else echo "LOCATION bibEdit.php?ID=$result";
	return;
}

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
	if (isset($_POST['title']))      { $title = clean_input($_POST['title']) .'%'; $title2 = "THE ".$title;}
	if (isset($_POST['author']))     $author = clean_input($_POST['author']) .'%';
	if (isset($_POST['subjects']))   $subjects = clean_input($_POST['subjects']) .'%';
	if (isset($_POST['callNumber'])) $callNumber= clean_input($_POST['callNumber']) .'%';

	# $sql = "SELECT students.studentID, students.firstname, students.lastname FROM students WHERE firstname LIKE '$q%' or lastname LIKE '$q%' or studentID LIKE '$q%' ORDER BY lastname, firstname";
	# $sql = "SELECT id as bibID, title, author, pubDate, ISBN, callNumber, subjects, createDate FROM bib WHERE title LIKE ? AND author LIKE ? AND callNumber LIKE ? ORDER BY author, pubdate";
	# Handle titles that start with "the"
	$sql = "SELECT id as bibID, title, author, pubDate, ISBN, callNumber, subjects, createDate FROM bib WHERE (title LIKE ? OR title LIKE ?) AND author LIKE ? AND callNumber LIKE ? ORDER BY author, pubdate";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("ssss", $title, $title2, $author, $callNumber );
		$stmt->execute(); 
		$resultArray = $stmt->get_result();
		$stmt->close();                 
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
	return $resultArray;
}

//general HTML now being written
echo '<p class="text-primary">Please click on the desired book to select/edit the record.';
echo '<table class="table table-secondary table-striped table-hover table-bordered">';
echo '<thead>';
echo '<tr>';
echo '<th>Author</th>';
echo '<th>Title</th>';
echo '<th>ISBN</th>';
echo '<th>Pub. date</th>';
echo '<th>Call Number</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// printing table rows: student name, student number
while ($row = mysqli_fetch_assoc($resultArray)){ 
	
	$tit = $row['title'];
	if (strlen($tit) > 74) $tit = substr($tit,0,70)." ...";
# onclick() for <TR> is now supported in all modern browsers. I've just left it applied to each <TD> for now.
# should look like this: <tr onclick="window.document.location='commentPage.php?ID=339671216';">
#           echo "<tr onclick=\"window.document.location='commentPage.php?ID=". $row['studentID'] . "';\" >";
	echo "<tr>";
	echo "<td onclick=\"window.document.location='bibEdit.php?ID=". $row['bibID'] . "';\" >".$row['author']."</td>";
	echo "<td onclick=\"window.document.location='bibEdit.php?ID=". $row['bibID'] . "';\" >".$tit. "</td>";
	echo "<td onclick=\"window.document.location='bibEdit.php?ID=". $row['bibID'] . "';\" >".$row['ISBN']. "</td>";
	echo "<td onclick=\"window.document.location='bibEdit.php?ID=". $row['bibID'] . "';\" >".$row['pubDate']. "</td>";
	echo "<td onclick=\"window.document.location='bibEdit.php?ID=". $row['bibID'] . "';\" >".$row['callNumber']. "</td>";
#print_r($row);
	echo "</tr>";

} //this is the end of the while loop

echo '</tbody>';
echo '</table>';

mysqli_free_result($resultArray);
?>


