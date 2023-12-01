<?php
/*******************************************************
  NAME: userMaint.php
  CALLED FROM: admin.php
  PURPOSE: add, delete users. Change permissions.
 ********************************************************/
session_start();
require_once('common.php');

/*
$access = "ADMIN";
if (($userdata['authlevel']) != $access)  {
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information.");
	header("location:main.php");
}
*/

/********** Check permissions for page access ***********/
$allowed = array("ADMIN");
if (false === array_search($userdata['authlevel'],$allowed)) { 
	$_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - UserMaintenance");
	header("location:main.php");
	exit;
}
/********************************************************/


$sql = "SELECT * FROM users";
if ($stmt = $db->prepare($sql)) {
	$stmt->execute(); 
	$resultArray = $stmt->get_result();
	$stmt->close();                 
} else {
	die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
}

/*
 describe users;
+------------+-----------------------------------------+------+-----+-------------------+-------------------+
| Field      | Type                                    | Null | Key | Default           | Extra             |
+------------+-----------------------------------------+------+-----+-------------------+-------------------+
| username   | varchar(30)                             | NO   | PRI | NULL              |                   |
| fullname   | varchar(50)                             | NO   |     | ---               |                   |
| password   | varchar(255)                            | NO   |     | NULL              |                   |
| defaultPWD | tinyint(1)                              | NO   |     | 1                 |                   |
| authlevel  | enum('ADMIN','STAFF','PATRON','PUBLIC') | NO   |     | STAFF             |                   |
| lastLogin  | timestamp                               | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
| createDate | timestamp                               | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+-----------------------------------------+------+-----+-------------------+-------------------+
*/

$DP = base64_encode($defaultPWD);
$length = 5;
$randomletter = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
$DP = $randomletter . $DP;

// *************************  Handle form submission for update/delete ************************
// if(isset($_POST['submit'])) {
//}

?>

<!DOCTYPE HTML>
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
		//null
	}); 

	function showDP() {
	  var s = "<?php echo $DP?>";
	  s = s.substr(5);
	  alert(atob(s));
	}

/*
function updateRow(num, login) {

	//Create a formdata object
	var formData = new FormData();

	formData.append("login", login);

	//get the data from the row
	var name = "alpha_row" + num;
	var val = document.getElementById(name).value;
	formData.append("alpha_row",val);

	//Warning: You have to use encodeURIComponent() for all names and especially for the values so that possible & contained in the strings do not break the format.

	var xhr = new XMLHttpRequest();
	//Send the proper header information along with the request: DOES NOT WORK!
	//xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	//xmlhttp.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=1');
	//xmlhttp.setRequestHeader("Content-length", params.length);
	xhr.onreadystatechange = function() {
		if (xhr.readyState == 4 && xmlhttp.status == 200) {
			window.location.reload(true);
		}
	}

	xhr.open("POST", "updateUser.php");
	xhr.send(formData);
}
*/

</script>
</head>
<body>

<div class="container-md mt-2">

<!-- page header -->
<?php loadHeader("main.php"); ?>

<div class="card border-primary mt-3">
	<div class="card-head alert alert-primary mb-0"> 
	<h2>Database user administration</h2>
	</div>

<div class="card-body">

<!-- ****************************************** ADD NEW USER ****************************** -->


<div class="border border-secondary p-2 rounded" style="border-color:#C9F !important">
<form method="POST" action="userAdd.php">
<h4 class=""> Add a new user
<button type="submit" id="submit" name="submit" class="float-end btn btn-outline-primary">Create User</button>
</h4>
	<div class="row">
		<div class="col-sm-8 col-md-6 col-lg-4 mt-1">
			<label for="username" class="form-label fg2">Login</label>
			<input class="form-control bg2" type="text" id="username" name="username" required>
		</div>
		<div class="col-sm-8 col-md-6 col-lg-4 mt-1">
			<label for="fullname" class="form-label fg2">Full name</label>
			<input class="form-control bg2 rounded-end" type="text" id="fullname" name="fullname" required>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-2 mt-1">
			<label for="authlevel" class="form-label fg2">Access Level</label>
		<select class="form-control border border-secondary fg2" id="authlevel" name="authlevel">
			<option value="ADMIN">ADMIN</option>
			<option selected value="STAFF">STAFF</option>
			<option value="PATRON">PATRON</option>
			<option value="PUBLIC">PUBLIC</option>
		</select>
		</div>
	</div>
</form>
</div>


<!-- ******** Anchor for Javascript and PHP notification popups ********** -->
	<div id="notif_container"></div>
	<?php if ($notify["message"] != "") echo "<script> displayNotification(\"{$notify['type']}\", \"{$notify['message']}\")</script>"; ?>
<!-- ********************************************************************* -->
<hr>

<!--
CREATE USER 'rburen'@'localhost' IDENTIFIED BY 'default_password_here';
GRANT SELECT, INSERT, UPDATE ON sssDB.* TO 'rburen'@'localhost';
GRANT SELECT ON schoolDB.* TO 'rburen'@'localhost';

use schoolDB;
INSERT INTO `users` (`login_name`, `full_name`, `alpha`, `password`, `salt`, `defaultPWD`, `isAdmin`, `isWait`, `isTeam`) VALUES ('ddavis', 'Dawn Davis', 'SST', '', '', '1', '0', '1', '0');
-->

<!-- ****************************** TABLE OF DB USERS ******************************************************** -->

<p style='margin-bottom:0;'>&nbsp;</p>

<table class="table table-secondary table-striped table-hover table-bordered">
<thead>
<tr>
<th class="">Login name</th>
<th class="">Full Name</th>
<th class="">Access Level</th>
<th><!-- <i>These buttons don't work yet!</i>&nbsp; --></th>
<th class="">Default PWD changed?</th>
</tr>
</thead>
<tbody>

<?php
$num = 1;
while ($row = mysqli_fetch_assoc($resultArray)){

	echo '<tr>';
	echo '<td>'.$row['username'].'</td>';
	echo '<td>'.$row['fullname'].'</td>';

	//echo '<td><input type="text" class="" id="alpha_row'.$num.'" size=15 value="' .$row['authlevel']. '">';
	echo "<td class='text-primary'>${row['authlevel']} &nbsp;";
	echo '<select class="fg2 float-end">';
	$sql = "SHOW COLUMNS FROM users WHERE FIELD='authlevel'";
	if ($stmt = $db->prepare($sql)) {
		$stmt->execute(); 
		$result= $stmt->get_result();
		$stmt->close();                 
	} else {
		die("Invalid query: " . mysqli_error($db) . "\n<br>SQL: $sql");
	}

	while ($row2 = mysqli_fetch_row($result)) {
		//row1 = "enum('ADMIN','STAFF','PATRON','PUBLIC')"
		//substring produces this: ADMIN','STAFF','PATRON','PUBLIC
		foreach(explode("','",substr($row2[1],6,-2)) as $option) {
			$str = "<option value='$option'>$option</option>";
			//add "selected" to the current value
			if ($option == $row['authlevel']) $str = "<option selected value='$option'>$option</option>";
			echo $str;
		}
	}
	echo '</select></td>';
#     echo '<td style="color:black;" id="login" name="login">' .$row['login_name']. '</td>';
#     echo '<td style="color:black;" id="login" name="login">' .$row['full_name']. '</td>';
#     echo '<td style="color:black;" id="login" name="login">' .$row['alpha']. '</td>';

#     echo '<td style="color:black;" id="login" name="login">' .$row['isWait']. '</td>';
	/* The delete button is a straight call to a separate php page.
	   So is the reset password button

	   The update button must be the submit button on a form. The form is comprized of everything in the row up to there.
	   Each field must have a name, then the form can be done using POST method.
	 */
	echo '<td>';
	echo '<button type="submit" onclick="updateRow('.$num.',\''.$row['username'].'\')"><s>Update</s></button>&nbsp;';
	echo '<a href="userDelete.php?ID='.$row['username'].'"><button type="submit" name="delete" style="color:red;" onclick="return confirm(\'Are you sure?\');" >Delete</button></a>&nbsp;';

	echo '<a href="userResetPWD.php?ID='.$row['username'].'"><button type="submit" name="resetPWD" onclick="return confirm(\'Are you sure?\');" >Reset Password</button></a></td>';
	echo "<td>";
	if ($row['defaultPWD'] == 1) echo " <center><b>*</b></center> ";
	else echo " <center><b>&check;</b></center> ";
	echo '</td>';

	echo '</tr>';
	echo  PHP_EOL; //for viewing source code.
	$num ++;
}

echo '</tbody></table>';
?>

<hr>
<button type="button" onclick="showDP();">Show Default Password</button>


<br>
</div>
<div class="card-footer alert alert-primary mb-0"></div>
</div> <!-- end of card-body and card -->

</div>
</body>
</html>

