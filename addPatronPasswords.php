<?php
/*************************************************
* This is a one-time fix program. 
* It is adding a password for each patron, 
* but ONLY if the patron has a library card.
* The password will be encrypted using PHP not MYSQL
* and the default will be lastname, first initial
* Josiah Smythe  -->> smythej
*
* This requires the password field to be added to the patron file.
* ALTER TABLE `patron` ADD `password` VARCHAR(255) NOT NULL AFTER `birthdate`; 
**************************************************/
require_once('common.php');
$db = connectToDB();
//This will get all of the patrons who DO have a library card. and then set their default password to their lastname+first initial. 
//In the SQL only the "login" field is actually needed. The others are there just to double check when debugging.
//This will get patron ids multiple times: once for each libraryCard that they have. I don't think that this matters as only a handful have more than one.
//We could do this: SELECT DISTINCT patron.id, LCASE(CONCAT(lastname, SUBSTRING(firstname,1,1))) AS login FROM patron LEFT JOIN libraryCard on patron.id=libraryCard.patronID WHERE libraryCard.barcode is NOT NULL GROUP BY (patron.id);
$sql = "SELECT patron.id, LCASE(CONCAT(lastname, SUBSTRING(firstname,1,1))) AS login, libraryCard.barcode FROM patron LEFT JOIN libraryCard on patron.id=libraryCard.patronID where libraryCard.barcode is NOT NULL;";
if ($stmt = $db->prepare($sql)) {
	$stmt->execute(); 
	$patronData = $stmt->get_result();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

while($row = mysqli_fetch_assoc($patronData)) {
	$pwd = password_hash("password", PASSWORD_DEFAULT);
	//use LIMIT 20  for debugging.  
	//echo $row['id'] . "  ". $row['login']." --> ".$pwd.PHP_EOL;
	$sql = "UPDATE patron SET password = ? WHERE id = ?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("si", $pwd, $row['id']);
		$stmt->execute(); 
		$stmt->close();                 
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}


}




?>
