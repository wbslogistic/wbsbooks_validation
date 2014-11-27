<?php
require_once("./include/functions.php");

// username and password sent from form 
   $myusername=$_POST['myusername']; 
   $mypassword=$_POST['mypassword']; 

    $sql="SELECT * FROM users WHERE username='$myusername' and password='$mypassword' and active = 1";
echo $sql;

    $result=GetDatabaseRecords($sql);
    $sql_row=pg_fetch_array($result);

// Mysql_num_row is counting table row
     $count=pg_num_rows($result);
    echo "COUNT=";
    echo $count;
    
// If result matched $myusername and $mypassword, table row must be 1 row
    if($count>0){
        session_start();
        $_SESSION['user_name'] = $myusername;
	$_SESSION['user_id'] = $sql_row["userid"];

    //echo "insert user session"."INSERT INTO UserSessions (userID, userName, LoginDate, SessionID) VALUES (".$_SESSION['user_id'].",'".$myusername."', CURRENT_TIMESTAMP, '".$_SESSION['session_id']."')"
    echo "START session insert = " ;
    $result  = intval(GetDatabaseRecords("select max(userhistoryid) as his_id from usersessions where userid=1123123")["his_id"]);
	 $_SESSION['session_id'] = $result+1;
     
    InsertDatabaseRecord("INSERT INTO usersessions (userid, username, logindate, sessionid) VALUES (".$_SESSION['user_id'].",'".$myusername."', CURRENT_TIMESTAMP, '".$_SESSION['session_id']."')");	
  echo "END session insert" ;
        header("location:index.php");
     }
     else {
        header("location:login.php?error=invalid");
     }
?>
