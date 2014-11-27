<?php
//////////////////
// add publisher
//////////////////




	if ( (isset($_POST['PublisherEN'])) && (isset($_POST['PublisherRU']))  ) {
		$pubE  = $_POST['PublisherEN'];
		$pubR  = $_POST['PublisherRU'];
		$host = "localhost";
                $user = "livesite";
                $password = "wbsbooks";
                $database = "Validation";
                $connection = mysql_connect($host,$user,$password)  or die("Could not connect: ".mysql_error());
                mysql_select_db($database, $connection)  or die("Error in selecting the database:".mysql_error());
	        mysql_query("SET CHARACTER SET utf8");
        	mysql_query("SET NAMES utf8");



		$sq="SELECT * FROM PublisherList WHERE PublisherNameRU = '" . $_POST['PublisherRU'] . "' AND PublisherNameEN = '" . $_POST['PublisherEN'] . "'";
		$sql_result=mysql_query(  $sq  ,   $connection)   or exit("Sql Error ".mysql_error() . "<BR /><BR />" . $sql);
		$num_rows = mysql_num_rows(  $sql_result  );
		if (  $num_rows == 0 ) {
			$sq="INSERT IGNORE INTO PublisherList ( PublisherNameRU  ,  PublisherNameEN ) VALUES ( '" . $pubR  . "','" . $pubE . "')";

		        $sql_result=mysql_query(  $sq  ,   $connection)   or exit("Sql Error ".mysql_error() . "<BR /><BR />" . $sql);
			$rows_added =   mysql_affected_rows();
			echo 'New Publishers Added: ' . $rows_added;
			echo '<br /><br /><br />';
			echo '<a href = "index.php">HOME</a>';
		} else { echo 'Publisher Already in the Database!!';  echo '<br /><br /><br />'; echo '<a href = "index.php">HOME</a>';  }
	} else { echo 'ERROR!!'; }
?>

