<?php
/*******************************************************
* checkout2.php
* Called from patronFindCKO.php
* 
* (1) Find patron info
* (2) See if patron has a valid barcode
* (3) Display patronInfo in a shortform at the top.

* FIXME: this is using the JS from "checkout.php -- which searches for patrons. 
* switch to using checkin.php JS
* Also: patronViewCKO needs to pass the patron ID to this file.
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Checkout 2");
	header("location:main.php");
	exit;
}
/********************************************************/

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
	<script src="resources/library.js"></script>

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

    document.getElementById("barcode").value = "";
	let xhr = new XMLHttpRequest();
	xhr.onload = () => {
		if (xhr.responseText == "LOGOUT") {
			window.document.location="index.php?ERROR=Failed%20Auth%20Key"; 
			return;
		}
		document.getElementById("dynTable").innerHTML = xhr.responseText;
	}
	xhr.open("GET", "patronFindCKO.php?q=" + str, true);
	xhr.send();
}

function processBarcode(e) {
    const str = e.target.value;
    if (str.length == 0) { 
        document.getElementById("dynTable").innerHTML = "";
        return;
    } 

    //validation of the input...
	if (isNaN(str)) {
		displayNotification("error", "Invalid barcode");
		return;
	}
	let xhr = new XMLHttpRequest();
	xhr.onload = () => {
		if (xhr.responseText == "LOGOUT") {
			window.document.location="index.php?ERROR=Failed%20Auth%20Key"; 
			return;
		}
		const data = JSON.parse(xhr.responseText);
		if (data.patronID != null) {
			window.location.href='patronEdit.php?ID='+data.patronID;
		} else {
		   displayNotification("error", "Barcode not found");
		}
	}
	xhr.onerror = () => {
		displayNotification("error", "Barcode not found");
	}
	xhr.open("GET", "patronFindCKO.php?bar=" + str, true);
	xhr.send();
}
</script>
</head>

<body>

<div class="container-md mt-2">

<!-- page header -->
<?php loadHeader("main.php"); ?>
<h3>Patron: <span class="text-secondary">Smith, John <span class="small">(111 King St, London)</span></span></h3>
<h2>CHECKOUT: <span class="small text-secondary">Enter barcode</span></h2>

<form id="form" action="<?php echo $_SERVER["PHP_SELF"];?>" method="GET">
	<div class="row mt-4">
		<div class="col-12 col-sm-9 col-md-6 col-lg-3 me-2">
		<input class="form-control rounded" style="border-color:#CCC;" type="text" name="barcode" id="barcode" placeholder="Scan/Type Barcode, press ENTER" autofocus>
		<span class="smaller text-secondary">&nbsp;&nbsp;&nbsp;Starts with 30748...</span> 
		</div>
	</div>
</form>
	<div class="row mt-4">
		<div class="col-12 col-md-7 me-2">
			<div class="input-group">
				<span style="display: block; padding: .375rem .75rem;">OR </span> 
				<input class="form-control rounded" style="border-color:#CCC;" autofocus="" type="text" onkeyup="dynamicData(this.value)" placeholder="Search by Title/Author" >&nbsp;&nbsp;
			</div>
		</div>
	</div>

<!-- ******** Anchor for Javascript and PHP notification popups ********** -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
<!-- ********************************************************************* -->

<!-- IMPORTANT - Do not remove next line. It's where the table appears (also for error from barcode input)-->
<div id="dynTable" class="mt-4"></div>

</div>
</body>
</html>
