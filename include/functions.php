<?PHP

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

function recordActivity()
    {

        $sql = "UPDATE UserSessions SET LastActivity = CURRENT_TIMESTAMP WHERE UserHistoryID = '".$_SESSION['session_id']."'";
        UpdateDatabase($sql);

    }

function UpdateDatabase($sql)
    {
	$host = "localhost";
	$user = "livesite";
	$password = "MX980OOLL9237#";
	$database = "Validation";
	$connection = mysql_connect($host,$user,$password) 
		or die("Could not connect: ".mysql_error());

	mysql_select_db($database, $connection)
		or die("Error in selecting the database:".mysql_error());

	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET NAMES utf8");

	$sql_result=mysql_query($sql, $connection)
		or exit("Sql Error ".mysql_error() . "<BR /><BR />" . $sql);

    }

function InsertDatabaseRecord($sql)
    {

	$host = "localhost";
	$user = "livesite";
	$password = "MX980OOLL9237#";
	$database = "Validation";
	$connection = mysql_connect($host,$user,$password) 
		or die("Could not connect: ".mysql_error());

	mysql_select_db($database, $connection)
		or die("Error in selecting the database:".mysql_error());

	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET NAMES utf8");

	$sql_result=mysql_query($sql, $connection)
		or exit("Sql Error".mysql_error());

	return mysql_insert_id();

    }

function GetDatabaseRecords($sql)
    {

	$host = "localhost";
	$user = "livesite";
	$password = "MX980OOLL9237#";
	$database = "Validation";
	
	
	
	//$connection = mysql_connect($host,$user,$password)
	$connection = pg_connect("host=localhost dbname=import user=postgresql password=s")
        	or die("Could not connect to postgresql "));
	//mysql_select_db($database,$connection)
      //  	or die("Error in selecting the database:".mysql_error());

	//pg_query("SET CHARACTER SET utf8");
	//pg_query("SET NAMES utf8");

	$sql_result=pg_query($connection,$sql)
        	or exit("Sql Error"+ pg_last_error($connection));
	return $sql_result;
    }


function formatData($data)
    {
        return str_replace("'", "\'", $data); 
    }


?>
