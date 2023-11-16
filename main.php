<?php
/*******************************************************
* main.php
*
* Called from index.php
* Calls: many pages, depending on which button is pressed
*
* This is the main landing page after one has logged on
* Other possibilities are: PAC page and patron page
* Options visible vary depending on the access level of the user (admin or staff)
*
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Staff information");
	header("location:logout.php");
}
/********************************************************/
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
    <link href="resources/library.css" rel="stylesheet">
	<script src="resources/library.js"></script>

	<script>
	document.addEventListener("DOMContentLoaded", () => {
		let text = (new Date()).toDateString();
		document.getElementById("date").textContent = text;
	}); 
	</script>

</head>

<body>

<!-- this page has a special header, not the normal one that other pages use -->
<div class="container-md mt-2">
	<span class="float-end px-2 pt-1" style="background-color: rgba(255,255,255,0.35);"><img width=200 height=170 src="images/logoBG.png"></span>
	<h2 class="bg-warning text-center rounded py-3">The <?=$institution?> Public Libary</h2>
&nbsp;

	<div class="row">
	<div class="col-md-8 p-0">
		<div class="card border border-primary p-2">
			<div class="alert alert-warning mb-0">Welcome "<b><?=$userdata['fullname']?></b>"</div>
		</div>
	</div>
	<div class="col">
		<?php 
		if ($userdata['authlevel'] === "ADMIN") {
			echo '<span class="float-end"> <a class="d-block btn btn-danger" href="admin.php"><i class="fa fa-cogs"></i>   Administer</a> </span>';
		}
		?>
	</div>
	</div>
&nbsp;

	<div class="card border border-secondary alert alert-warning">
		<div class="card-body">
		<div class="ml-3">
		<a href="patronList.php"><button type="button" class="btn btn-success">Search Patrons</button></a>
		<a href="bibSearch.php" class="px-2"><button type="button" class="btn btn-primary">Books</button></a>
		<a href="" class="px-2"><button type="button" class="btn btn-outline-primary">Circulation</button></a>
		<a href="" class="px-2"><button type="button" class="btn btn-outline-primary">Fines</button></a>
		<a href="" class="px-2"><button type="button" class="btn btn-outline-primary">Reports</button></a>
		<span class="float-end"><a href="changePWD.php"><button type="button" class="btn btn-outline-secondary">Change Password</button></a>&nbsp;
		<a href="logout.php"><button type="button" class="btn btn-secondary">Logout</button></a></span>
		</div>

		</div><!-- /card-body -->
	</div><!-- /card -->

<!-- ******** Anchor for Javascript and PHP notification popups ********** -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
<!-- ********************************************************************* -->

	<div class="card border border-secondary alert alert-warning mt-4">
		<div class="card-body">

		<p class="float-end"><span class="border-bottom border-end border-warning p-1">Today is <span id="date"></span></span></p>
		<h3>Staff Announcements</h3>

		<ul>
			<li><i class="fa fa-crown"></i> Reminder: <b>the royal family</b> is coming for a visit in two weeks 
			<li><i class="fa fa-book"></i> We just received <b>$10,000</b> to buy new aooks 
			<li><i class="fa fa-mug-saucer"></i> Jane bought a <b>coffee maker</b> for us, it's in the staff lounge.
			<li><i class="fa fa-jet-fighter"></i> This weekend is our <b>jet fighter ride</b> bonding experience!
		</ul>

		</div><!-- /card-body -->
	</div><!-- /card -->
</div>

</body>

</html>
