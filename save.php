<?php
//////////////////
// add publisher
//////////////////
error_reporting(E_ALL);
ini_set('display_errors', 'on');
 mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
require_once("./include/functions.php");
echo "start sterted";

	if ( (isset($_POST['PublisherEN'])) && (isset($_POST['PublisherRU']))  ) {
        
		$pubE  = $_POST['PublisherEN'];
		$pubR  = $_POST['PublisherRU'];
        
        echo "params present </br>";
		//$host = "localhost";
          //      $user = "livesite";
                //$password = "wbsbooks";
                //$database = "Validation";
                //$connection = mysql_connect($host,$user,$password)  or die("Could not connect: ".mysql_error());
                //mysql_select_db($database, $connection)  or die("Error in selecting the database:".mysql_error());
	        //mysql_query("SET CHARACTER SET utf8");
        	//mysql_query("SET NAMES utf8");

		$sq="SELECT * FROM publisherlist WHERE publishernameru = '" . $_POST['PublisherRU'] . "' AND publishernameen = '" . $_POST['PublisherEN'] . "'";
       echo "  </br> sql=".$sq;
       echo "RECORDS=". GetDatabaseRecords($sq);
         $result=GetDatabaseRecords($sq);
       echo "   record obtained";
      //$sql_row=pg_fetch_array($result);
     $count=pg_num_rows($result);
     echo " count=";
//		$sql_result=mysql_query(  $sq  ,   $connection)   or exit("Sql Error ".mysql_error() . "<BR /><BR />" . $sql);
		///$num_rows = mysql_num_rows(  $sql_result  );
		if (  $count == 0 ) {
			$sq="INSERT  INTO publisherlist ( publishernameru  ,  publishernameen ) VALUES ( '" . $pubR  . "','" . $pubE . "')";
            $result= InsertDatabaseRecord($sq);
            //$sql_result=mysql_query(  $sq  ,   $connection)   or exit("Sql Error ".mysql_error() . "<BR /><BR />" . $sql);
			$rows_added =   pg_affected_rows($result);
            
            //pg_affected_rows(
			echo '</br>New Publishers Added: ' . $rows_added;
			echo '<br /><br /><br />';
			echo '<a href = "index.php">HOME</a>';
		} else { echo '</br>Publisher Already in the Database!!';  echo '<br /><br /><br />'; echo '<a href = "index.php">HOME</a>';  }
	} else { echo 'ERROR!!'; }
?>

