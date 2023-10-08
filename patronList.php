<?php
/*******************************************************
* patronList.php
* 
* This lists all patrons, searched by name, phone ...
* Called from main.php
* Calls patronEdit, patronAdd 
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

<style>
	#header {
	  background-color:#e6cf8b;
	  padding: 0 10px 5px 10px;
	  color:#22264B;
	}
	#header hr {
	  margin:0 -10px;
	}
</style>

<script>
function dynamicData(str) {
    if (str.length == 0) { 
        document.getElementById("dynTable").innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("dynTable").innerHTML = xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET", "patronFind.php?q=" + str, true);
        xmlhttp.send();
    }
}
</script>
</head>

<body>

<div class="container-md mt-2">

<!-- page header -->
<?php $backHref="main.php";
$text = file_get_contents("pageHeader.html");
$text = str_replace("BACK", $backHref,$text);
$text = str_replace("INSTITUTION", $institution,$text);
echo $text;


//FIXME:  the barcode needs JS for when Enter is pressed.
?>


<div class="row mt-4">
<div class="input-group">
	<div class="col me-2">
	<input class="form-control rounded" autofocus="" type="text" onkeyup="dynamicData(this.value)" placeholder="Enter First Name, Last Name, or Patron phone number ..." >&nbsp;&nbsp;
	</div>
	<div class="col-3 me-2">
	<input class="form-control rounded" type="text" placeholder="Enter Barcode" >&nbsp;&nbsp;
	</div>
	<div class="col-2">
    <a class="form-control btn btn-outline-dark rounded" href="patronAdd.php"><i class="fa fa-plus-circle"></i>  Add Patron</a>
	</div>
</div>
</div>

<!-- IMPORTANT - Do not remove next line. It's where the table appears -->
<div id="dynTable" class="mt-4"></div>


</div>
</body>

</html>
