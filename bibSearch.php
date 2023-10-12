<?php
/*******************************************************
* bibList.php
* 
* This lists/searches all books, by various fields
* Called from main.php
* Calls   
********************************************************/
session_start();
require_once('common.php');

# Check authorization (ie. that the user is logged in) or go back to login page
if ($_SESSION["authkey"] != AUTHKEY) { 
    header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
}

# TODO Check user access level for the page (ie. Does the user have appropriate permissions to do this?)

$db = connectToDB();

$error_message = "";

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title><?=$institution?> Library Database</title>
	<!-- Required meta tags -->
	<title>Library Database — 2023</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="resources/bootstrap5.min.css" >
    <!-- our project just needs Font Awesome Solid + Brands -->
    <!-- <link href="resources/fontawesome-6.4.2/css/fontawesome.min.css" rel="stylesheet"> -->
    <link href="resources/fontawesome6.min.css" rel="stylesheet">
    <link href="resources/fontawesome-6.4.2/css/brands.min.css" rel="stylesheet">
    <link href="resources/fontawesome-6.4.2/css/solid.min.css" rel="stylesheet">

    <link rel="stylesheet" href="resources/library.css" >

<script>
document.addEventListener("DOMContentLoaded", () => {
	const bar = document.getElementById('barcode');
	bar.addEventListener('keyup', (e) => {
		if (e.key === 'Enter') processBarcode(e);
	});
});

function dynamicData(str) {
    if (str.length == 0) { 
        document.getElementById("dynTable").innerHTML = "";
        return;
    } 

	var xhr = new XMLHttpRequest();
	xhr.onload = () => {
		document.getElementById("dynTable").innerHTML = xhr.responseText;
	}
	xhr.open("GET", "patronFind.php?q=" + str, true);
	xhr.send();
}

</script>

<style>
.form-label { margin-top: .5rem; margin-bottom:0; }
</style>
</head>

<body>

<div class="container-md mt-2">

<!-- page header -->
<?php $backHref="main.php";
$text = file_get_contents("pageHeader.html");
$text = str_replace("BACK", $backHref,$text);
$text = str_replace("INSTITUTION", $institution,$text);
echo $text;


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
?>
<h3>Search Books</h3>
<form action="bibFind.php" method="POST">
<div class="row bg4 pb-2">
  <div class="col-md-6">
    <label for="title" class="form-label">Title</label>
    <input type="text" class="form-control" name="title" id="title" autofocus="">
  </div>
  <div class="col-md-6">
    <label for="inputPassword4" class="form-label">Author</label>
    <input type="text" class="form-control" name="author" id="author">
  </div>
</div>
<div class="row bg4">
  <div class="col-md-6">
    <label for="inputCity" class="form-label">Subject</label>
    <input type="text" class="form-control" name="subject" id="subject" >
  </div>
</div>
<div class="row bg4 pb-2">
  <div class="col-md-4">
    <label for="inputCity" class="form-label">ISBN</label>
    <input type="text" class="form-control" name="ISBN" id="ISBN" >
  </div>
  <div class="col-md-4">
    <label for="inputZip" class="form-label">Call Number</label>
    <input type="text" class="form-control" name="callNumber" id="callNumber" >
  </div>
  <div class="col-md-4">
    <label for="inputZip" class="form-label">Barcode</label>
    <input type="text" class="form-control" name="barcode" id="barcode" >
  </div>
</div>
<div class="row bg4 pb-2">
  <div class="col-12">
    <button type="submit" class="btn btn-primary">Search</button>
  </div>
</div>
</form>


<!-- IMPORTANT - Do not remove next line. It's where the table appears (also for error from barcode input)-->
<div id="dynTable" class="mt-4"></div>

<p>I think that this should be done using AJAX, but then the form would have to be submitted that way. Let's start with just using PHP.</p>

</div>
</body>

</html>
