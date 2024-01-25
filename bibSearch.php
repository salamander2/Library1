<?php
/*******************************************************
 * bibList.php
 * 
 * This sets up a search to find books by various fields

 * Called from: main.php
 * Calls: bibFind.php, which sends back a table that calls bibEdit.php
 ********************************************************/
session_start();
require_once('common.php');

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
	<title><?=$institution?> Library Database</title>
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

	// Get the form element
	const form = document.getElementById("myForm");
	// Add 'submit' event handler
	form.addEventListener("submit", (event) => {
			event.preventDefault();
			postForm(form);
			new bootstrap.Collapse(document.getElementById("collapse1"), {toggle:false} ).hide();
			document.querySelector(".collapseBtn").textContent = " + ";
		});
});

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
			document.getElementById("searchTips").style = "display:block";
			document.getElementById("barcode").value="";
			document.getElementById("barcode").focus();
			return;
		}
		if (xhr.responseText.startsWith("LOGOUT")) {
			window.document.location="ndex.html?ERROR=Failed%20Auth%20Key"; 
			return;
		}
		//The responseText can begin with "LOCATION". This is from an exact barcode search.
		if (xhr.responseText.startsWith("LOCATION ")) {
			window.location.href = xhr.responseText.replace("LOCATION ","");
			return;
		}

		document.getElementById("searchTips").style = "display:none";
		document.getElementById("dynTable").innerHTML = xhr.responseText;
	}
	// Define what happens in case of error
	//xhr.addEventListener("error", (event) => {
	//  alert("Oops! Something went wrong.");
	//});

	// Set up our request
	xhr.open("POST", "bibFind.php");

	// The data sent is what the user provided in the form
	xhr.send(myForm);
}

function removeTHE() {
	let title = document.getElementById("title").value;
	if (title.trim().toUpperCase().startsWith("THE ")) {
		title = title.substring(4);
		document.getElementById("title").value = title;
		window.alert(title);
	}
	if (title.trim().toUpperCase() == "THE") {
		document.getElementById("title").value = "";
		window.alert(title);
	}
	return true;
}

</script>

</head>

<body>

	<div class="container-md mt-2">

		<!-- page header -->
		<?php loadHeader("main.php"); ?>

		<h3>Search Books <span class="text-secondary smaller float-end">(<?=$result?> books in collection)
		<button class="collapseBtn btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1"> – </button>
		</span></h3>
		<hr>
		<div class="card-body collapse show" id="collapse1">
		<form id="myForm" Xaction="bibFind.php" method="POST" onsubmit="return removeTHE()">
			<div class="row bgS pb-2">
				<div class="col-md-6">
					<label for="title" class="form-label">Title</label>
					<input type="text" class="form-control" name="title" id="title" autofocus="">
				</div>
				<div class="col-md-6">
					<label for="inputPassword4" class="form-label">Author</label>
					<input type="text" class="form-control" name="author" id="author">
				</div>
			</div>
			<div class="row bgS">
				<div class="col-md-6">
					<label for="inputCity" class="form-label">Subject</label>
					<input type="text" class="form-control" name="subjects" name="subjects" disabled placeholder="Subject field not available" readonly>
					<span class="smaller text-secondary">&nbsp;&nbsp;&nbsp;Sorry, the database does not contain "subjects" for the books.</span> 
				</div>
			</div>
			<div class="row bgS pb-2">
				<div class="col-md-4">
					<label for="inputZip" class="form-label">Call Number</label>
					<input type="text" class="form-control" name="callNumber" id="callNumber" >
					<span class="smaller text-secondary">&nbsp;&nbsp;&nbsp;e.g. FIC J  or 796</span> 
				</div>
				<div class="col-md-4 border ">
					<label for="inputZip" class="form-label">Barcode</label>
					<input type="text" class="form-control" name="barcode" id="barcode" >
				</div>
					<div class="col-md-4 border">
						<label for="inputCity" class="form-label">ISBN</label>
						<input type="text" class="form-control" name="ISBN" id="ISBN" >
					</div>
			</div>
			<div class="row bgS pb-2">
				<div class="col-12">
					<button type="submit" class="btn btn-primary">Search</button>
					<span class="smaller text-secondary">&nbsp;&nbsp;&nbsp;Searching with no criteria returns all the books.</span>
				</div>
			</div>
		</form>
		</div>
<!-- ******** Anchor for Javascript and PHP notification popups ********** -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
<!-- ********************************************************************* -->

		<div id="searchTips">
			&nbsp;
			<div class="row alert alert-success">The searches are done on partial text and combined using AND. So the more information added, the more restrictive the search.<br>
				Call number="FIC" and Title = "Girl" will find all books that are fiction and start with "Girl" or "The Girl"</div>
			<div class="row alert alert-danger">Barcode and ISBN are searched as exact matches. 
				If anything is entered in these fields, then the other ones are ignored. Barcode trumps ISBN if both are entered. </div>
		</div>

		<!-- IMPORTANT - Do not remove next line. It's where the table appears (also for error from barcode input)-->
		<div id="dynTable" class="mt-4"></div>

	</div>
</body>

</html>
