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
$title=$author=$ISBN=$callNumber=$subjects=$barcode="";

//if a barcode is being searched for, ignore all of the other fields.
if (isset($_POST['barcode'])) {
	$barcode= filter_var($_GET['barcode'], FILTER_SANITIZE_NUMBER_INT);
	$sql = "SELCT * FROM BIB";
}


#$query = "SELECT students.studentID, students.firstname, students.lastname FROM students WHERE firstname LIKE '$q%' or lastname LIKE '$q%' or studentID LIKE '$q%' ORDER BY lastname, firstname";
$q = $q.'%';
$q2 = $q;
$q3 = $q;
$sql = "SELECT id as patronID, firstname, lastname, phone, birthdate, postalCode FROM patron  WHERE firstname LIKE ? or lastname LIKE ? or phone LIKE ? ORDER BY lastname, firstname";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("sss", $q, $q2, $q3);
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
echo '<th>Patron Name</th>';
echo '<th>Patron Phone</th>';
echo '<th>Birthdate</th>';
echo '<th>Postal Code</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// printing table rows: student name, student number
while ($row = mysqli_fetch_assoc($resultArray)){ 

# onclick() for <TR> is now supported in all modern browsers. I've just left it applied to each <TD> for now.
# should look like this: <tr onclick="window.document.location='commentPage.php?ID=339671216';">
#           echo "<tr onclick=\"window.document.location='commentPage.php?ID=". $row['studentID'] . "';\" >";
		echo "<tr>";
	echo "<td onclick=\"window.document.location='patronEdit.php?ID=". $row['patronID'] . "';\" >".$row['lastname'], ", ", $row['firstname'] ."</td>";
	echo "<td onclick=\"window.document.location='patronEdit.php?ID=". $row['patronID'] . "';\" >".$row['phone']. "</td>";
	echo "<td onclick=\"window.document.location='patronEdit.php?ID=". $row['patronID'] . "';\" >".$row['birthdate']. "</td>";
	echo "<td onclick=\"window.document.location='patronEdit.php?ID=". $row['patronID'] . "';\" >".$row['postalCode']. "</td>";
#print_r($row);
	echo "</tr>";

} //this is the end of the while loop

echo '</tbody>';
echo '</table>';

mysqli_free_result($resultArray);
?>


