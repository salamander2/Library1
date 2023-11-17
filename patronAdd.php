<?php
/*******************************************************
* patronAdd.php
* Called from patronList.php
* Calls: patronEdit.php upon success
*
* Purpose: allow user to enter patron information for a new record.
* 		validates input and adds record
********************************************************/
session_start();
require_once('common.php');

/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - Add Patron");
	header("location:main.php");
}
/********************************************************/

if(isset($_POST['submit'])) {

	$firstname=$lastname=$address=$city=$prov=$postalCode=$phone=$email=$birthdate="";

	//All validation still needs to be done here (and on patronAdd.php).
	if (isset($_POST['firstname']))	$firstname = clean_input($_POST['firstname']);
	if (isset($_POST['lastname'])) 	$lastname = clean_input($_POST['lastname']);
	if (isset($_POST['address'])) 	$address = clean_input($_POST['address']);
	if (isset($_POST['city'])) 		$city = clean_input($_POST['city']);
	if (isset($_POST['prov'])) 		$prov = clean_input($_POST['prov']);
	if (isset($_POST['postalCode'])) $postalCode = clean_input($_POST['postalCode']);
	if (isset($_POST['phone'])) 	$phone = clean_input($_POST['phone']);
	if (isset($_POST['email'])) 	$email = clean_input($_POST['email']);
	if (isset($_POST['birthdate'])) $birthdate = clean_input($_POST['birthdate']);
	//Check for required values
	if ($firstname == "" || $lastname == "" || $address == "" || $city == "" || $prov == "" || $postalCode == "" || $birthdate == "") {
		$_SESSION['notify'] = array("type"=>"error", "message"=>"Missing required fields.");
		header("location:patronAdd.php");
		return;
	}
	$password = $lastname.substr($firstname,0,1);
	$password = password_hash($password, PASSWORD_DEFAULT);

	$sql = "INSERT INTO patron (firstname, lastname, address, city, prov, postalCode, phone, email, birthdate, password ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("ssssssssss", $firstname, $lastname, $address, $city, $prov, $postalCode, $phone, $email, $birthdate, $password );
		$stmt->execute();
		$patronID = $stmt->insert_id;
		$stmt->close();
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}
	$_SESSION['notify'] = array("type"=>"success", "message"=>"Patron record has been updated.");

	header("location:patronEdit.php?ID=$patronID");

}

$patronData = "";

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

</head>
<body>

<div class="container-md mt-2">

<!-- page header -->
<?php $backHref="patronList.php";
$text = file_get_contents("pageHeader.html");
$text = str_replace("BACK", $backHref,$text);
$text = str_replace("INSTITUTION", $institution,$text);
echo $text;
?>

<div class="card border-primary mt-3">
	<div class="card-head alert alert-primary mb-0"> <h2>Add New Patron </div>

<div class="card-body">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return validatePatronForm()" method="post">
		
		<div class="row">
			<div class="col-sm-8 col-md-6 col-lg-4 mt-1">
				<div class="input-group rounded">
				<label for="lastname" class="input-group-prepend btn btn-info">Last name</label>
				<input class="form-control bgP rounded-end" type="text" id="lastname" name="lastname" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-4 mt-1">
				<div class="input-group rounded">
				<label for="firstname" class="input-group-prepend btn btn-info">First name</label>
				<input class="form-control bgP rounded-end" type="text" id="firstname" name="firstname" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>
		<div class="row mt-2">
		<div class="col-sm-8 col-md-6 col-lg-4">
			<div class="input-group rounded">
				<label for="birthdate" class="input-group-prepend btn btn-info">Birth date</label>
				<input class="form-control bgP rounded-end" type="date" id="birthdate" name="birthdate" required><span class="text-danger">&nbsp;*</span>
			</div>
		</div></div>

		<h5 class="mt-3"><u>Address:</u></h5>
		<div class="row my-2">
			<div class="col-md-6">
				<div class="input-group rounded">
				<label for="address" class="input-group-prepend btn btn-secondary">Street</label>
				<input class="form-control bgS rounded-end" type="text" id="address" name="address" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<div class="row my-2">
			<div class="col-sm-6 col-md-4">
				<div class="input-group rounded">
				<label for="city" class="input-group-prepend btn btn-secondary">City</label>
				<input class="form-control bgS rounded-end" type="text" id="city" name="city" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-4 col-lg-3 col-xxl-2">
				<div class="input-group rounded">
				<label for="prov" class="input-group-prepend btn btn-secondary">Prov./State</label>
				<input class="form-control bgS rounded-end" type="text" id="prov" name="prov" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
			<div class="col-sm-6 col-lg-4 col-xl-3">
				<div class="input-group rounded">
				<label for="postalCode" class="input-group-prepend btn btn-secondary">Postal Code</label>
				<input class="form-control bgS rounded-end" type="text" id="postalCode" name="postalCode" required><span class="text-danger">&nbsp;*</span>
				</div>
			</div>
		</div>

		<h5 class="mt-4 fg1"><u>Contact:</u></h5>
		<div class="row">
			<div class="col-sm-8 col-md-4">
				<div class="input-group rounded">
				<label for="phone" class="input-group-prepend btn btn-outline-warning fg1"><b>Phone</b></label>
				<input class="form-control bg1" type="text" id="phone" name="phone" >
				</div>
			</div>
			<div class="col-sm-8 col-md-6 col-lg-5">
				<div class="input-group rounded">
				<label for="email" class="input-group-prepend btn btn-outline-warning fg1"><b>Email</b></label>
				<input class="form-control bg1" type="text" id="email" name="email" >
				</div>
			</div>
		</div>

		<br clear="both">
		<div class="row">
		<div class="col">
			<button type="submit" id="submit" name="submit" class="btn btn-success">Create Patron</button> &nbsp;
		</div>
		<div class="col form-check">
		  <input class="form-check-input" type="checkbox" value="" id="addCard" checked>
		  <label class="form-check-label" for="addCard">Create Library Card?</label>
		</div>
		</row>
	</form>

</div></div> <!-- end of card-body and card -->
<!-- ******** Anchor for Javascript and PHP notification popups ********** -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
<!-- ********************************************************************* -->

</div>

<br><br><br>
</body>
</html>
