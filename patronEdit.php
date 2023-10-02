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

/* patron table
+------------+--------------+------+-----+-------------------+-------------------+
| Field      | Type         | Null | Key | Default           | Extra             |
+------------+--------------+------+-----+-------------------+-------------------+
| id         | int unsigned | NO   | PRI | NULL              | auto_increment    |
| firstname  | varchar(30)  | NO   |     | NULL              |                   |
| lastname   | varchar(30)  | NO   |     | NULL              |                   |
| address    | varchar(255) | NO   |     | NULL              |                   |
| city       | varchar(100) | NO   |     | NULL              |                   |
| prov       | varchar(2)   | NO   |     | NULL              |                   |
| postalCode | varchar(6)   | NO   |     | NULL              |                   |
| phone      | varchar(20)  | YES  |     | NULL              |                   |
| email      | varchar(50)  | YES  |     | NULL              |                   |
| birthdate  | date         | NO   |     | NULL              |                   |
| createDate | timestamp    | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+--------------+------+-----+-------------------+-------------------+
*/

$patronID = filter_var($_GET['ID'], FILTER_SANITIZE_NUMBER_INT);
$patronData = "";

$sql = "SELECT * FROM patron WHERE id = ?";
if ($stmt = $db->prepare($sql)) {
	$stmt->bind_param("i", $patronID);
	$stmt->execute(); 
	$patronData = $stmt->get_result()->fetch_assoc();
	$stmt->close();                 
} else {
	$message_  = 'Invalid query: ' . mysqli_error($db) . "\n<br>";
	$message_ .= 'SQL2: ' . $query;
	die($message_); 
}
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

</head>
<body>

<div class="container-md mt-2">
<div id="" class="alert alert-warning text-center rounded py-3">
	<a class="fa fa-sign-out btn btn-outline-dark float-start m-2" href="logout.php">  Logout</a>
	<span class="float-end"> <a class="d-block fa fa-cogs btn btn-outline-dark m-2" href="admin.php">  Administer</a> </span>
	<h2 class="fw-bold">The <?=$institution?> Public Libary</h2>
	<br clear="both">
	<a class="float-start btn btn-dark rounded" href="patronList.php"><i class="fa fa-arrow-left"></i>  Back</a>
    <hr>
	<br clear="both">
</div>

<a class="btn btn-info rounded" href="patronEdit.php?ID=<?php echo $patronID-1; ?>"><i class="fa fa-arrow-left"></i>  Prev. Patron</a>
<a class="btn btn-info rounded" href="patronEdit.php?ID=<?php echo $patronID+1; ?>"><i class="fa fa-arrow-right"></i>  Next Patron</a>
<a class="float-end btn btn-outline-dark rounded" href="patronDelete.php"><i class="fa fa-plus-circle"></i>  Delete Patron</a>
<br clear="both">

<form class="mt-4 border rounded border-success p-3"  action="patronUpdate.php" method="post">
<fieldset>
<div class="group">


<label for="dateContacted" class="" style="color:#518;">First name:</label>
<input id="dateContacted" name="dateContacted" type="date" size="15" style="background:#CAF;">

<label for="personContacted" class="" style="color:#620;">&nbsp;&nbsp;&nbsp;&nbsp;Person contacted:</label>
<input id="personContacted" name="personContacted" type="text" size="15" style="background:#FFA;" value="Student"> 
</div>
</fieldset>


PatronID: <?=$patronID?><br>
CreateDate: <?=$patronData['createDate']?><br>

<label for="firstname" class="">firstname</label><input class="" type="text" id="firstname" name="firstname" size="15"  value="<?=$patronData['firstname']?>"</input><br>
<label for="lastname" class="">lastname</label><input class="" type="text" id="lastname" name="lastname" size="15"  value="<?=$patronData['lastname']?>"</input><br>
<label for="address" class="">address</label><input class="" type="text" id="address" name="address" size="15"  value="<?=$patronData['address']?>"</input><br>
<label for="city" class="">city</label><input class="" type="text" id="city" name="city" size="15"  value="<?=$patronData['city']?>"</input><br>
<label for="prov" class="">prov</label><input class="" type="text" id="prov" name="prov" size="15"  value="<?=$patronData['prov']?>"</input><br>
<label for="postalCode" class="">postalCode</label><input class="" type="text" id="postalCode" name="postalCode" size="15"  value="<?=$patronData['postalCode']?>"</input><br>
<label for="phone" class="">phone</label><input class="" type="text" id="phone" name="phone" size="15"  value="<?=$patronData['phone']?>"</input><br>
<label for="email" class="">email</label><input class="" type="text" id="email" name="email" size="15"  value="<?=$patronData['email']?>"</input><br>
<input type="hidden" id="d" name="id" value="<?=$patronID?>">

<button type="submit" class="btn btn-warning btn-outline-dark">Submit</button
</form>
</div>

<p></p>

<div class="row">
		<div class="input-group mb-3 ">
			<label class="input-group-prepend btn btn-success" for="username">First name:</label>
			<input type="text" class="xform-control border border-success" id="username" name="username" value="" required autofocus>
			&nbsp;
			<input type="text" class="form-control border border-success" id="username" name="username" value="" required >
		</div>
	</div>



</body>

</html>

