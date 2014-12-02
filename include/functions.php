
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
     $connection = "dbname=postgres user=postgres password=s host=localhost port=5432";
        $dbconn = pg_connect($connection)
    or die('Could not connect: ' . pg_last_error());
    return  $dbconn ;
}

function recordActivity()
    {
        $now =date("d m Y H i s");
        echo  "session_id";
         echo "SESSION_ID".$_SESSION['session_id'];
        $sql = "UPDATE usersessions SET lastactivity = to_timestamp('". $now."','DD MM YYYY') WHERE userhistoryid = ".$_SESSION['session_id'];
        UpdateDatabase($sql);
    }

function UpdateDatabase($sql)
    {
          $connection = GetConnection();
          echo "sql=".$sql;
          echo " </br> Update database sql =".$sql;
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
   echo "</br> SQL=".$sql." </br>";
   $sql_result=pg_query($connection,$sql)
        	or exit("Sql Error"+ pg_last_error($connection));
    pg_close($connection);
	return $sql_result;
}

function InsertDatabaseRecord($sql)
    {
		  $connection = GetConnection();
          echo "insert query = ".$sql;
           $sql_result=pg_query($connection,$sql)
          	or exit("Sql Error"+ pg_last_error($connection));
          pg_close($connection);       
          return $sql_result;
}



 ?>
 


