<?php
/*******************************************************
 * PAC.php
 * Called from: index.php
 * Calls: bibFindPAC.php, which sends back a table that calls bibView.php
 * 
 * This lists/searches all books, by various fields
 * It is the public access catalog and the HTML is based on bibSearch.php
 ********************************************************/
session_start();
require_once('common.php');

//PAC special startup:
$username = "PAC";
$password = "CairParavel";

$db = connectToDB();

/********** The following is from INDEX.PHP and is the Login code. However, PAC does not login manually *************/
//Retrieve all data for that user and verify the password for that user. It is stored into an array "userdata".
$sql = "SELECT username, fullname, password as pwdHash, authlevel, createDate, lastLogin FROM users WHERE username = BINARY ?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$userdata = $result->fetch_array(MYSQLI_ASSOC);
	$stmt->close();
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

	//Check if user exists, then verify password
	$row_cnt = mysqli_num_rows($result);
	if (0 === $row_cnt) {		
		$notify["message"] = "That user does not exist. <br><span class='small'>(Check case of username or talk to admin.)</span>";
	} elseif (!password_verify ($password, $userdata['pwdHash'])) {
		$notify["message"] = "Invalid password";
	}
	//Password has been checked, now clear the variable for security reasons.
	$password = "---";
	$userdata['pwdHash'] = "";
	// error message ...
	if (empty($notify["message"])) {
		$_SESSION["userdata"] = $userdata;
		//This is set here upon login (AND ALSO IN register.php)  and then session-authkey is never set again.
		$_SESSION["authkey"] = AUTHKEY;
	}
/*************************************************************************************************/


$sql = "SELECT COUNT(*) FROM bib";
if ($stmt = $db->prepare($sql)) {
	$stmt->execute(); 
	$stmt->bind_result($result);
	$stmt->fetch();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title><?=$institution?> Library Database : PAC</title>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="resources/bootstrap5.min.css" >
	<script src="resources/bootstrap5.min.js"></script>
	<!-- our project just needs Font Awesome Solid + Brands -->
	<!-- <link href="resources/fontawesome-6.4.2/css/fontawesome.min.css" rel="stylesheet"> -->
	<link href="resources/fontawesome6.min.css" rel="stylesheet">
	<link href="resources/fontawesome-6.4.2/css/brands.min.css" rel="stylesheet">
	<link href="resources/fontawesome-6.4.2/css/solid.min.css" rel="stylesheet">
	<link rel="stylesheet" href="resources/library.css" >
	<script src="resources/library.js"></script>

	<style>
	.form-label { margin-top: .5rem; margin-bottom:0; }
	</style>

<script type="module">
  import QuantityInput from './resources/quantity.js';
  (function(){
	   new QuantityInput(document.querySelector('[data-quantity]'), '–', '+');
   })();
</script>

<script>
 document.addEventListener("DOMContentLoaded", () => {

	//for Bootstrap collapse. I need to change the symbol when the item has collapsed / expanded.
	const collapseDivs = document.querySelectorAll(".collapseBtn");
    for (const item of collapseDivs) {
        item.addEventListener("click", 
        () => {
            if   (item.textContent == " + ") item.textContent = '\u2013'; //en-dash
            else                             item.textContent = " + ";
        });
    }
 
	//searching is done by POST upon either button click
	const btnBrowse = document.getElementById("btnBrowse");
	btnBrowse.addEventListener("click", () => {
		const form2 = document.getElementById("browseForm");
		postForm(form2);
		new bootstrap.Collapse(document.getElementById("collapse1"), {toggle:false} ).hide();
		document.querySelector(".collapseBtn").textContent = " + ";
	});

	//handles submit button on search form
	const form = document.getElementById("myForm");
	form.addEventListener("submit", (event) => {
		event.preventDefault();
		if (!validateForm()) return;
		postForm(form);
		new bootstrap.Collapse(document.getElementById("collapse1"), {toggle:false} ).hide();
		document.querySelector(".collapseBtn").textContent = " + ";
		updateButton();
	});
	form.addEventListener("reset", () => {
		document.getElementById("dynTable").innerHTML = "";
		updateButton();
	});
});

//This form requires title OR author OR ISBN to be filled in.
function validateForm() {
	
	if (document.getElementById("title").value == "" &&
		document.getElementById("author").value == "" &&
		document.getElementById("ISBN").value == "") 
	{
			document.getElementById("dynTable").innerHTML = "";
			displayNotification("warning", "You need some search criteria.");
			return false;
	}
	return true;
}

function postForm(form) {

	const xhr = new XMLHttpRequest();
	const myForm = new FormData(form);

	//document.getElementById("error_message").innerHTML = "";
	xhr.onload = () => {
		//The responseText can begin with "ERROR". If so, it is handled differently
		if (xhr.responseText.startsWith("ERROR ")) {
			errorMsg = xhr.responseText.replace("ERROR ","");
			//document.getElementById("error_message").innerHTML = '<div class="btn btn-danger w-50 mt-2">'+xhr.responseText+'</div>';
			displayNotification("error", errorMsg);
			return;
		}

		document.getElementById("dynTable").innerHTML = xhr.responseText;
	}
	// Set up our request
	xhr.open("POST", "bibFindPAC.php");
	// The data sent is what the user provided in the form
	xhr.send(myForm);
}

function removeTHE() {
	let title = document.getElementById("title").value;
	if (title.trim().toUpperCase().startsWith("THE ")) {
		title = title.substring(4);
		document.getElementById("title").value = title;
	}
	if (title.trim().toUpperCase() == "THE") {
		document.getElementById("title").value = "";
	}
	return true;
}

function updateButton() {
	let btn = document.getElementById("btnSubmit");
	if (btn.type == "submit") {
		btn.type="reset";
    	btn.textContent = "Reset";
	} else {
		btn.type="submit";
    	btn.textContent = "Submit";
	}
}
</script>

</head>
<body>

<div class="container-md mt-2">

<!-- page header -->
<div id="pageheader" class="alert alert-warning text-center rounded py-3">
	<!-- The spacing of the H2 and H1 can be aligned by adding a similar sized button at the beginning or by floating a button over the text (on the left) -->
	<!-- <div style="z-index:20; position:absolute;"><a class="btn btn-secondary" href="logout.php"><i class="fa fa-sign-out"></i>   Logout</a> </div> -->
	<a class="float-start btn btn-outline-secondary invisible" href=""><i class="fa fa-stop"></i>   SPACER</a>
	<a class="float-end btn btn-secondary" href="logout.php"><i class="fa fa-sign-out"></i>   Logout</a>
	<h2 class="fw-bold">The <?=$institution?> Public Libary</h2>
	<h1 class=""><i class="fa fa-xs fa-star-of-life"></i>&nbsp;Public Access Catalog&nbsp;<i class="fa fa-star-of-life fa-xs"></i></h1>
	<br clear="both">
    <hr class="py-0 mb-0">
</div>
<!-- end page header.-->



	<div class="row py-2">
		<div class="col-md-8">
			<form id="browseForm">
				<div class="input-group rounded">
				<button type="button" id="btnBrowse" class="btn btn-primary">Browse Books</button> &nbsp;
				<div data-quantity class="border border-primary rounded py-1"></div>
				<!-- <input type="text" xclass="form-control" name="title2" id="title2" size="1" value="M" style="text-align:center;"> -->
				</div>
				<label for="title2" class="px-3 smaller xform-label">Titles beginning with letter:</label>
			</form>
		</div>
	</div>
	<p></p>
	<hr>

	<h3>Search Books <span class="text-secondary smaller float-end">(<?=$result?> books in collection)
	<button class="collapseBtn btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1"> – </button>
	</span></h3>
	<hr>
	<div class="card-body collapse show" id="collapse1">

	<form id="myForm" onsubmit="return removeTHE()">
		<div class="row bgS pb-2">
			<div class="col-md-6">
				<label for="title" class="form-label">Title</label>
				<input type="text" class="form-control" name="title" id="title" autofocus="">
			</div>
			<div class="col-md-6">
				<label for="author" class="form-label">Author</label>
				<input type="text" class="form-control" name="author" id="author">
			</div>
		</div>
		<div class="row bgS pb-2">
			<div class="col-8 pt-4">
				<button id="btnSubmit" type="submit" class="btn btn-primary">Search</button>
			</div>
			<div class="col-md-4">
				<label for="ISBN" class="form-label">ISBN</label>
				<input type="text" class="form-control" name="ISBN" id="ISBN" >
			</div>
		</div>
	</form>
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
