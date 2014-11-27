
<?php  Print "Hello, World!</br>"; ?>
 <?php  Echo "Hello, World!</br> ";
 error_reporting(E_ALL);
ini_set('display_errors', 'on');
 mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
 ?>
 
 
 
 <?php
  error_reporting(E_ALL);
  
  
  function CheckLogin()
    {
	if(!isset($_SESSION)){ session_start(); }

         if(empty($_SESSION['user_name']))
         {
            return false;
         }
         return true;
   }


 function GetUsername()
    {
        if(!isset($_SESSION)){ session_start(); }

         if(empty($_SESSION['user_name']))
         {
            return "";
         }
         return $_SESSION['user_name'];

    }


  
  function RedirectToURL($url)
    {

echo "<script type='text/javascript'>";
echo "window.location = '".$url."';";
echo "</script>";

        exit;
    }
 function GetConnection()
 {
     $connection = "dbname=books user=postgres password=s host=localhost port=5432";
        $dbconn = pg_connect($connection)
    or die('Could not connect: ' . pg_last_error());
    return  $dbconn ;
}

function recordActivity()
    {

        $sql = "UPDATE UserSessions SET LastActivity = current_timestamp WHERE UserHistoryID = '".$_SESSION['session_id']."'";
        UpdateDatabase($sql);

    }

function UpdateDatabase($sql)
    {
          $connection = GetConnection();
           $sql_result=pg_query($connection,$sql)
        	or exit("Sql Error"+ pg_last_error($connection));
          pg_close($connection);        
          }
        
        function formatData($data)
    {
        return str_replace("'", "\'", $data); 
    }


 function GetDatabaseRecords($sql)   {
// Performing SQL query
   $connection = GetConnection();
   $sql_result=pg_query($connection,$sql)
        	or exit("Sql Error"+ pg_last_error($connection));
    pg_close($connection);
	return $sql_result;
}

function InsertDatabaseRecord($sql)
    {
		  $connection = GetConnection();
           $sql_result=pg_query($connection,$sql)
        	or exit("Sql Error"+ pg_last_error($connection));
          pg_close($connection);            
}



 ?>
 
<?php

// Performing SQL query
$query = 'SELECT * FROM authors limit 10';
$result =GetDatabaseRecords($query);
// Printing results in HTML
echo "<table>\n";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr>\n";
    foreach ($line as $col_value) 
    {
        echo "\t\t<td>$col_value</td>\n";
    }
    echo "\t</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($result);

echo "ok";
   $int_1  = intval(GetDatabaseRecords("select max(userhistoryid) as his_id from usersessions where userid=1123123")["his_id"]);
echo $int_1+1
?>


