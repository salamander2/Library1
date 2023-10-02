<?php
/*******************************************************
* This is the main landing page after one has logged on
* Other possibilities are: pac page and patron page
* Visible options vary depending on the access level of the user (admin or staff)
*
* This is called from index.php
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

<!-- this page has a special header, not the normal one that other pages use -->
<div class="container-md mt-2">
<div id="" class="bg-warning text-center rounded py-3">


<a class="d-block fa fa-sign-out btn btn-outline-dark float-start m-2" href="logout.php">  Logout</a>
<span class="float-end">
<a class="d-block fa fa-cogs btn btn-outline-dark m-2" href="admin.php">  Administer</a>
</span>
	<h2 class="fw-bold">The <?=$institution?> Public Libary</h2>

<br clear="both">
    <hr>
</div>

<form class="mt-4">
<!-- <span class="white">Enter First Name, Last Name, or Patron Phone...</span> -->
<fieldset>
<div class="input-group">
	<input class="form-control col-8 rounded" autofocus="" type="text" onkeyup="dynamicData(this.value)" placeholder="Enter First Name, Last Name, or Patron phone number ..." >&nbsp;&nbsp;
    <a class="fa fa-plus-circle btn btn-outline-dark rounded" href="patronAdd.php">  Add Patron</a>
</div>
</fieldset>
</form>

<!-- IMPORTANT - Do not remove next line. It's where the table appears -->
<div id="dynTable" class="mt-4"></div>


</div>
</body>

</html>
