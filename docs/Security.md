# 🔐 📎 Security and Authentication 
_Programmer documentation_

1. The starting page is **index.php**
2. The application logs on using a master username and password that has SELECT, INSERT, UPDATE, DELETE, and TRIGGER permissons on the database.
3. This information is stored in **library1.config.php** , which is one directory level up from the application (ie. inaccessible to the internet).
4. The user enters a username and password. This is checked against the username and hashed password stored in our "users" table.
5. `password_verify ($password, $pwdHash)` is used to verify the password.     
$password is immediately cleared after the result of this test.
6. Session varibles are set (including the logged in user access level)    
   `$_SESSION["authkey"] = AUTHKEY;`  //a special session variable is set if the login is successful.    
    and control is passed to **main.php**
7. **Ensuring that a user is logged in** before displaying something like /Library1/patronList.php This ensures that someone cannot just type in the URL of a page and see any information from that page.
   In **common.php** (the file that is included at the top of each page) we have

```php
if (basename($_SERVER['PHP_SELF']) !== "index.php") {
    # Check authorization (ie. that the user is logged in) or go back to login page
    if ($_SESSION["authkey"] != AUTHKEY)  header("Location:$home?ERROR=Failed%20Auth%20Key"); 
    $db = connectToDB();
}   
```

This check also needs to be done in any AJAX page that sends back data. 
For example, if you leave the checkin page open all day, you become logged out, but you could still search for information via AJAX in the search boxes. 
The files involved are `bibFind*.php` and `cardStatus.php`. They all call "common.php", but it doesn't work properly, as it becomes an inner subpage that is logged out. Looks messy.
These are now returning a value "LOGOUT" which the calling programs must trap and process (ie. logout the user).

8. There are 4 user levels (ADMIN, STAFF, PATRON, PUBLIC) which are used to determine and control which pages and data can be accessed and modified by which users. This happens _after_ the authentication above. Each page has this code at the top. It restricts access based on authentication levels. So for example, a staff member cannot create users, a patron cannot edit a Bib record. Most pages revert to main.php when an unauthorized access happens. Each page has a custom error message (see below).

```php
/********** Check permissions for page access ***********/
$allowed = array("ADMIN","STAFF");
if (false === array_search($userdata['authlevel'],$allowed)) {
    $_SESSION['notify'] = array("type"=>"info", "message"=>"You do not have permission to access this information - BIB Edit");
    header("location:main.php");
}   
/********************************************************/
```  

9. All SQL queries are written as **prepared statements.** This completely prevents SQL injection attacks.
  
