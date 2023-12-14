<?php
/*******************************************************
* checkin.php
* Called from main.php
* Calls:
* 
* This checks in a book.
* NOTE: this is the one PHP file that has a "duration" added to the error message.
*       This necessitates a different anchor for JS Notification popup.
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Check in books");
	header("location:main.php");
	exit;
}
/********************************************************/

//if(isset($_POST['submit'])) {  //Won't work since we are not submitting via a submit button
if(isset($_GET['barcode'])) {
	
	$barcode = clean_input($_GET['barcode']);
	unset($_GET['barcode']);

	if ($barcode=="") {
		//This is checked in JS first.
		$notify = array("type"=>"error", "message"=>"No barcode!", "duration"=>"");
	} elseif (!is_numeric($barcode) ) {
		$notify = array("type"=>"error", "message"=>"Invalid barcode", "duration"=>"");
	} else {

		$sql = "SELECT * FROM holdings INNER JOIN bib ON holdings.bibID = bib.ID WHERE barcode = ?";
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("i",$barcode);
			$stmt->execute(); 
			$holdingsData = $stmt->get_result()->fetch_assoc();
			$stmt->close();                 
		} else {
			die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
		}
		if ($holdingsData == null) {
			$_SESSION['notify'] = array("type"=>"error", "message"=>"Barcode not found", "duration"=>"");	
			header("Location:checkin.php");
			exit;
		}
		$status = $holdingsData['status'];
		if ($status == "OUT") {

			$sql = "UPDATE holdings SET status = 'IN' WHERE barcode = ?";
			if ($stmt = $db->prepare($sql)) {
				$stmt->bind_param("i",$barcode);
				$stmt->execute(); 
				$stmt->close();                 
			} else {
				die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
			}
			$_SESSION['notify'] = array("type"=>"success", "message"=>"\\\"".$holdingsData['title']."\\\" has been checked in.", "duration"=>"");	
			header("Location:checkin.php");
			exit;
		} else {
			//have to escape the "" for JS as well.
			$_SESSION['notify'] = array("type"=>"error", "message"=>"This book (\\\"".$holdingsData['title']."\\\") has the status of $status!", "duration"=>"5000");	
			header("Location:checkin.php");
			exit;
		}	
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title><?=$institution?> Library Database</title>
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
	document.getElementById('barcode').addEventListener('keyup', (e) => {
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
	//FIXME: There is a strange situation where you have two browser tabs open to the same database and then log out of the other tab. 
	//This tab will then return the login page instead of the table of matching records. 
	//patronList calls patronFind (Ajax), which then calls common.php, which kills the program since the user is now logged out.
	//Somehow the parent program needs to be alerted to this and then return to login screen as well. We would just need to validateSession() at the end of AJAX.
	xhr.onload = () => {
		document.getElementById("dynTable").innerHTML = xhr.responseText;
	}
	xhr.open("GET", "bibFindCKI.php?q=" + str, true);
	xhr.send();
}

function processBarcode(e) {
	e.preventDefault();

    //validation of the input...
    const str = e.target.value;
    if (str.length == 0) return; //no error message
	if (isNaN(str)) {
		displayNotification("error", "Invalid barcode");
		return;
	}

	document.getElementById('form').submit();
}

</script>
</head>

<body>

<div class="container-md mt-2">

<!-- page header -->
<?php loadHeader("main.php"); ?>

<h3>Check In Book</h3>
<!-- NOTE this uses GET instead of POST so that the dynData table can also use GET to check in a barcode -->
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
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\", \"{$notify['duration']}\" )</script>"; ?>
<!-- ********************************************************************* -->

<!-- IMPORTANT - Do not remove next line. It's where the table appears (also for error from barcode input)-->
<div id="dynTable" class="mt-4"></div>

</div>
</body>
</html>
