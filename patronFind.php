<?php
/*******************************************************************************
  Name: studentFind.php
  Called from: home.php
  Purpose: This file holds the function for finding students and diplaying them as a table
  Tables used: db/students, sssDB/sssInfo
  Transfers control to: commentPage.php or studentInfo.php (for non TEAM members)
 ******************************************************************************/

error_reporting(E_ALL);
session_start();
require_once('common.php');

$db = connectToDB();

/*************************
$isTeam means that the user is a member of the at-risk team
$activate means that this search function was called by pressing the button "list all at-risk students"
***************************/

// get the q parameter from URL
$q = clean_input($_REQUEST["q"]);

#$query = "SELECT students.studentID, students.firstname, students.lastname FROM students WHERE firstname LIKE '$q%' or lastname LIKE '$q%' or studentID LIKE '$q%' ORDER BY lastname, firstname";
$q = $q.'%';
$q2 = $q;
$q3 = $q;
$query = "SELECT id as patronID, firstname, lastname, phone FROM patron  WHERE firstname LIKE ? or lastname LIKE ? or phone LIKE ? ORDER BY lastname, firstname";
if ($stmt = $db->prepare($query)) {
	$stmt->bind_param("sss", $q, $q2, $q3);
	$stmt->execute(); 
	$resultArray = $stmt->get_result();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
	$message_ .= 'SQL2: ' . $query;
	die($message_); 
}


//general HTML now being written
echo '<table class="table table-secondary table-striped table-hover table-bordered">';
echo '<thead>';
echo '<tr>';
echo '<th>Patron Name</th>';
echo '<th>Patron Number</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// printing table rows: student name, student number
while ($row = mysqli_fetch_assoc($resultArray)){ 

#  <!-- select page based on "$nextPage"  -->
# should look like this: <tr onclick="window.document.location='commentPage.php?ID=339671216';" class="row0">
# old code: echo "<tr onclick=".'"'."window.document.location='commentPage.php?ID=".$row['studentID'] ."';".'" class="row0">';
#echo "<tr onclick=\"window.document.location='commentPage.php?ID=". $row['studentID'] . "';\" class=\"row$num_rows\">";
		echo "<tr>";
	echo "<td onclick=\"window.document.location='patronEdit.php?ID=". $row['patronID'] . "';\" >".$row['lastname'], ", ", $row['firstname'] ."</td>";
	echo "<td onclick=\"window.document.location='patronEdit.php?ID=". $row['patronID'] . "';\" >".$row['phone']. "</td>";
#print_r($row);
	echo "</tr>";

} //this is the end of the while loop

echo '</tbody>';
echo '</table>';

// mysqli_free_result($resultArray);
?>


