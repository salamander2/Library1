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
7. Each page begins with     
   `if ($_SESSION["authkey"] != AUTHKEY) {`                                                                                                                                                                   
           `header("Location:index.php?ERROR=Failed%20Auth%20Key");`                                                                                                                                            
   `}`    
   This ensures that someone cannot just type in the URL of a page and see any information from that page, e.g. if you're logged in as a patron and then go to the `deleteHoldings.php` page.
8. There are 4 user levels which are used to determine and control which pages and data can be accessed and modified by which users.

* All SQL queries that use any variables or user input are written as prepared statements. This completely prevents SQL injection attacks.
  
