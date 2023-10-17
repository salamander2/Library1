<?php
/*******************************************************
* bibUpdate.php 
* Called from bibEdit.php
* This updates the bib record.
* Validation has been done by JS, but more is done here.
* This returns to bibEdit.php with a message upon success. 
********************************************************/
session_start();
require_once('common.php');

# Check authorization (ie. that the user is logged in) or go back to login page
if ($_SESSION["authkey"] != AUTHKEY) { 
    header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
}

# Check user access level for the page (ie. Does the user have appropriate permissions to do this?)

$db = connectToDB();
$error_message = "";

/*  describe bib;
+------------+-----------------+------+-----+-------------------+-------------------+
| Field      | Type            | Null | Key | Default           | Extra             |
+------------+-----------------+------+-----+-------------------+-------------------+
| id         | int unsigned    | NO   | PRI | NULL              | auto_increment    |
| title      | varchar(255)    | NO   |     | NULL              |                   |
| author     | varchar(50)     | NO   |     | NULL              |                   |
| pubDate    | int             | NO   |     | NULL              |                   |
| ISBN       | bigint unsigned | YES  |     | NULL              |                   |
| callNumber | varchar(50)     | YES  |     | NULL              |                   |
| subjects   | varchar(200)    | YES  |     | NULL              |                   |
| createDate | timestamp       | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+-----------------+------+-----+-------------------+-------------------+
*/
	$id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

	$title=$author=$address=$callNumber=$pubDate=$ISBN="";
	//if (isset($_POST['firstname'])) $firstname = clean_input($_POST['firstname']);
	$title = clean_input($_POST['title']);
	$author= clean_input($_POST['author']);
	$callNumber = clean_input($_POST['callNumber']);

	$pubDate = filter_var($_POST['pubDate'], FILTER_SANITIZE_NUMBER_INT);
	$ISBN = filter_var($_POST['ISBN'], FILTER_SANITIZE_NUMBER_INT);

	$sql = "UPDATE bib SET title=?, author=?, pubDate=?, ISBN=?, callNumber=? WHERE id=?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("ssiisi", $title, $author, $pubDate, $ISBN, $callNumber, $id );
		$stmt->execute();
		$stmt->close();
	} else {
		$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
		$message_ .= 'SQL: ' . $sql;
		die($message_);
	}

	$_SESSION['success_message'] = "Title record has been updated.";

header("location:bibEdit.php?ID=$id");
