<?php

require_once("./include/functions.php");

session_start();

    $sql = "DELETE FROM BookLock WHERE userID = ". $_SESSION['user_id'];
    UpdateDatabase($sql);



    $tbl_name="UserSessions"; // Table name

    $sql = "UPDATE ".$tbl_name." SET LogoffDate=CURRENT_TIMESTAMP WHERE UserHistoryID = '".$_SESSION['session_id']."'";

    session_unset(); 

    session_destroy();

    UpdateDatabase($sql);

RedirectToURL('index.php');


?>
