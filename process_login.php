<?php

require_once("./include/functions.php");

    $tbl_name="Users"; // Table name 

// username and password sent from form 
   $myusername=$_POST['myusername']; 
   $mypassword=$_POST['mypassword']; 

    $sql="SELECT * FROM $tbl_name WHERE username='$myusername' and password='$mypassword' and active = 1";
echo $sql;

    $result=GetDatabaseRecords($sql);
    $sql_row=mysql_fetch_array($result);

// Mysql_num_row is counting table row
     $count=mysql_num_rows($result);

// If result matched $myusername and $mypassword, table row must be 1 row
    if($count>0){
        session_start();
        $_SESSION['user_name'] = $myusername;
	$_SESSION['user_id'] = $sql_row["userID"];

	$_SESSION['session_id'] = InsertDatabaseRecord("INSERT INTO UserSessions (userID, userName, LoginDate, SessionID) VALUES (".$_SESSION['user_id'].",'".$myusername."', CURRENT_TIMESTAMP, '".$_SESSION['session_id']."')");	

        header("location:index.php");
     }
     else {
        header("location:login.php?error=invalid");
     }
?>
