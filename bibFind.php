<?php
/*******************************************************************************
  Name: 
  Called from: 
  Note that in both cases, it returns data (via AJAX), not as a new HTML page.
 ******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('common.php');

$db = connectToDB();

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
*/


//Initialize all input variables (for POST form)
$title=$title2=$author=$ISBN=$subjects="";
$callNumber=$barcode="";

//if a barcode is being searched for, ignore all of the other fields.
if (isset($_POST['barcode'])) {
	$barcode= filter_var($_POST['barcode'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELCT * FROM BIB";
}
if (isset($_POST['ISBN']))  $ISBN = filter_var($_POST['ISBN'], FILTER_SANITIZE_NUMBER_INT);

if (isset($_POST['title']))      { $title = clean_input($_POST['title']) .'%'; $title2 = "THE ".$title;}
if (isset($_POST['author']))     $author = clean_input($_POST['author']) .'%';
if (isset($_POST['subjects']))   $subjects = clean_input($_POST['subjects']) .'%';
if (isset($_POST['callNumber'])) $callNumber= clean_input($_POST['callNumber']) .'%';

#$query = "SELECT students.studentID, students.firstname, students.lastname FROM students WHERE firstname LIKE '$q%' or lastname LIKE '$q%' or studentID LIKE '$q%' ORDER BY lastname, firstname";
$sql = "SELECT id as bibID, title, author, pubDate, ISBN, callNumber, subjects, createDate FROM bib WHERE title LIKE ? AND author LIKE ? AND callNumber LIKE ? ORDER BY author, pubdate";
$sql = "SELECT id as bibID, title, author, pubDate, ISBN, callNumber, subjects, createDate FROM bib WHERE (title LIKE ? OR title LIKE ?) AND author LIKE ? AND callNumber LIKE ? ORDER BY author, pubdate";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("ssss", $title, $title2, $author, $callNumber );
	$stmt->execute(); 
	$resultArray = $stmt->get_result();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
	$message_ .= 'SQL2: ' . $sql;
	die($message_); 
}

//general HTML now being written
echo '<p class="text-primary">Please click on the desired patron to edit the record.';
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


