<?php

/* Name of file (program)
   What it does
   Where it is called from
   Where it transfers to (what it calls
*/

/* Session variables:
	$username	- the user's login name
	$fullname	- the user's full name
	$isAdmin	- the user is admin  (vs normal user). How do we designate a read only user?	
*/
session_start();

# common.php has a set of common utility methods. 
# It also sets the Error reporting (to /var/log/apache2/error.log)
# It sets various session variables if they are not already set.
# and it links to config.php which has the variables needed to log in to MySQL.
require_once('common.php');

$db = connectToDB();

# Check authorization (ie. that the user is logged in) or go back to login page
if ($_SESSION["authkey"] != AUTHKEY) { 
    header("Location:index.php?ERROR=Failed%20Auth%20Key"); 
}

# Check user access level for the page (ie. Does the user have appropriate permissions to do this?)

$error_message = "";

# get POST and GET data
$user = $_POST["user"];
$patron = $_GET['ID'];

# do error checking on the data. Set error message if needed:
$dob = clean_input($_POST['dob']);
if (!validate_date($dob)) $error_message = "Invalid date or incorrect format";
//...
if ($error_message != "") $error_message = "<div class=\"error\">" . $error_message . "</div>";
	
//if corrent, then add to database
if (empty($error_message)) {

# Run SQL select statements (or others if needed), using prepared statements

/* If you're not using any variables in the SQL statements, you could use mysqli
	//get all of the users (students)
	#$sql = "SELECT username,fullname,lastLogin FROM users ORDER BY fullname";
	$sql = "SELECT username,fullname,DATE_FORMAT(lastLogin,'%a, %b %e %Y') FROM users ORDER BY fullname";
	$result=runSimpleQuery($db,$sql);
	$response = mysqli_fetch_all($result);
*/


# PHP methods

?>

*** Start HTML here. See HTML template. Add in <?php ... ?> as needed
*** Note. If all that you're doing is printing a variable then you can use the short form
	<?php echo $name ?>  <?=$name?>
	
<!DOCTYPE html>
<html lang="en">

<head>
//...

</head>
<body>
	
//Is this used??
<div id="error_message"></div>  

<?php if ($error_message != "") echo $error_message; ?>


</div>
</body>
</html>
