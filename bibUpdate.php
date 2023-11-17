<?php
/*******************************************************
* bibUpdate.php 
*
*  ** AJAX Version **
*
* Called from: bibEdit.php
* This updates the bib record.
* Validation has been done by JS, but more is done here.
* This returns to bibEdit.php with a message upon success. 
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - BIB update");
	header("location:main.php");
}
/********************************************************/

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
	if (isset($_POST['title'])) 	 $title = clean_input($_POST['title']);
	if (isset($_POST['author'])) 	 $author= clean_input($_POST['author']);
	if (isset($_POST['callNumber'])) $callNumber = clean_input($_POST['callNumber']);

	$pubDate = filter_var($_POST['pubDate'], FILTER_SANITIZE_NUMBER_INT);
	$ISBN = filter_var($_POST['ISBN'], FILTER_SANITIZE_NUMBER_INT);

	$sql = "UPDATE bib SET title=?, author=?, pubDate=?, ISBN=?, callNumber=? WHERE id=?";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("ssiisi", $title, $author, $pubDate, $ISBN, $callNumber, $id );
		$stmt->execute();
		$stmt->close();
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}

	$_SESSION['notify'] = array("type"=>"success", "message"=>"Title record has been updated.");

header("location:bibEdit.php?ID=$id");
