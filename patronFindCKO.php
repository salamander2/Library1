<?php
/*******************************************************************************
  Name: patronFindCKO.php
	This is 95% the same as patronFind.  Barcode is displayed. Some text changed.
	It also checks to make sure that the patron has a valid barcode. 
	If not (or if there's more than one, then it returns an error-> redirect to patronEdit.php

  Called from: checkout.php

  ** AJAX Version **

  Purpose: This does dynamic searching, returning a table that's updated
	each time the user presses a key. 
	It also does the search for library barcode and matches that to a patron.
  Note that in both cases, it returns data (via AJAX), not as a new HTML page.

 ******************************************************************************/

session_start();

require_once('common.php');
$db = connectToDB();

if (!isset($_SESSION["authkey"]) || $_SESSION["authkey"] != AUTHKEY) {
	echo "LOGOUT";
	return;
}

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Listing Patrons");
	header("location:main.php");
	exit;
}
/********************************************************/

//If there is a barcode parameter, then search that.
$patronBC = "";
if (isset($_GET['bar'])) $patronBC= filter_var($_GET['bar'], FILTER_SANITIZE_NUMBER_INT);
if (strlen($patronBC) != 0) {

	$sql = "SELECT patronID FROM libraryCard WHERE barcode = ?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("i", $patronBC);
		$stmt->execute(); 
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();                 
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
	//$obj=new stdClass;
	//$obj->patronID=$result;
	//echo json_encode($obj); 
	echo json_encode(['patronID'=>$result]);
	return;
}

// Otherwise get the query parameter from the URL
$q = clean_input($_REQUEST["q"]);
$q = $q.'%';
$q2 = $q;
$q3 = $q;
#$sql = "SELECT id as patronID, firstname, lastname, phone, birthdate, postalCode FROM patron  WHERE firstname LIKE ? or lastname LIKE ? or phone LIKE ? ORDER BY lastname, firstname";
$sql = "SELECT id as patronID, firstname, lastname, phone, birthdate, postalCode, barcode, status FROM patron INNER JOIN libraryCard ON patron.id=libraryCard.patronID  WHERE firstname LIKE ? or lastname LIKE ? or phone LIKE ? ORDER BY lastname, firstname, status";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("sss", $q, $q2, $q3);
	$stmt->execute(); 
	$resultArray = $stmt->get_result();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}


//general HTML now being written
echo '<p class="text-primary">Please click on the desired row to select the patron.';
echo '<table class="table table-secondary table-striped table-hover table-bordered">';
echo '<thead>';
echo '<tr>';
echo '<th>Patron Name</th>';
echo '<th>Patron Phone</th>';
echo '<th>Birthdate</th>';
echo '<th>Postal Code</th>';
echo '<th>Library Card</th>';
echo '<th>Card Status</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// printing table rows: student name, student number
while ($row = mysqli_fetch_assoc($resultArray)){ 

# onclick() for <TR> is now supported in all modern browsers. I've just left it applied to each <TD> for now.
# should look like this: <tr onclick="window.document.location='commentPage.php?ID=339671216';">
#           echo "<tr onclick=\"window.document.location='commentPage.php?ID=". $row['studentID'] . "';\" >";
	if ($row['status'] != 'ACTIVE') echo '<tr class="table-danger">';
	else echo "<tr>";
	echo "<td onclick=\"window.document.location='patronViewCKO.php?ID=". $row['patronID'] . "';\" >".$row['lastname'], ", ", $row['firstname'] ."</td>";
	echo "<td onclick=\"window.document.location='patronViewCKO.php?ID=". $row['patronID'] . "';\" >".$row['phone']. "</td>";
	echo "<td onclick=\"window.document.location='patronViewCKO.php?ID=". $row['patronID'] . "';\" >".$row['birthdate']. "</td>";
	echo "<td onclick=\"window.document.location='patronViewCKO.php?ID=". $row['patronID'] . "';\" >".$row['postalCode']. "</td>";
	echo "<td onclick=\"window.document.location='patronViewCKO.php?ID=". $row['patronID'] . "';\" >".$row['barcode']. "</td>";
	echo "<td onclick=\"window.document.location='patronViewCKO.php?ID=". $row['patronID'] . "';\" >".$row['status']. "</td>";
#print_r($row);
	echo "</tr>";

} //this is the end of the while loop

echo '</tbody>';
echo '</table>';

mysqli_free_result($resultArray);
?>


