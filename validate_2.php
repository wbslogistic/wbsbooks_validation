<!DOCTYPE html>

<?php

require_once("./include/functions.php");
 error_reporting(E_ALL);
 if(!CheckLogin())
{
    RedirectToURL("login.php");
    exit;
}
recordActivity();
session_start();
$userid   = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

 $validationType = "";
 if (isset($_POST['validationType'])){
	$validationType = $_POST['validationType'];
        $id = $_POST['recordID'];
}
 $user = GetUsername();
 
 if ($validationType == "book")
{
	if (isset($_POST['actionType'])){
	    $actionType = $_POST['actionType'];
}

if ($actionType == "skip") {
		$sql = "UPDATE products SET Confirmed = -99, SessionID = '".$_SESSION['session_id']."' WHERE id = '".$id."'";
		UpdateDatabase($sql);
	}else {
            $t_ru = $_POST['TitleRU'];
            $t_en = $_POST['TitleEN'];
            $d_ru = $_POST['DescriptionRU'];
            $d_en = $_POST['DescriptionEN'];
            $publishers = $_POST['publishers'];
            $a_id = $_POST['authorID'];
            $a_ru = $_POST['authorRU'];
            $a_en = $_POST['authorEN'];
            $a_con = $_POST['authorConfirmed'];
            $userviewsid = $_POST['UserViewsID'];

            $t_ru = addslashes($t_ru);
            $t_en = addslashes($t_en);
            $d_ru = addslashes($d_ru);
            $d_en = addslashes($d_en);
            $a_id = addslashes($a_id);
            $a_ru = addslashes($a_ru);
            $a_en = addslashes($a_en);
            $a_con = addslashes($a_con);
            $text = trim($_POST['ISBN']);
            $textAr = explode("\r", $text);
            //
            // author ID set?
            //
            if ($a_id!="")
            {
                  $sql = "SELECT * FROM authors WHERE authorid = ".$a_id;
                  $sql_result = GetDatabaseRecords ( $sql );
                  $sql_num=mysql_num_rows          ( $sql_result );

                  if ($sql_num > 0)
                  {
                    $sql = "UPDATE Authors SET AuthorRU = '".$a_ru."', AuthorEN = '".$a_en."', Confirmed=1, ConfirmedBy='".$userid."', DateConfirmed=CURRENT_TIMESTAMP WHERE AuthorID = ".$a_id;
                    UpdateDatabase($sql);
                  } else {
                      $sql = "INSERT INTO Authors (AuthorRU, AuthorEN, Confirmed, ConfirmedBy, DateConfirmed) VALUES ('".$a_ru."','".$a_en."',1,'".$userid."',CURRENT_TIMESTAMP)";
                      $a_id = InsertDatabaseRecord($sql);
                  }
             } // if $a_id = '' 
        else {
                //
                // author not set so create a new one.
                //
                if ($a_ru!="")
                {
                   $sql = "INSERT INTO Authors (AuthorRU, AuthorEN, Confirmed, ConfirmedBy, DateConfirmed) VALUES ('".$a_ru."','".$a_en."',1,'".$userid."',CURRENT_TIMESTAMP)";
                   $a_id = InsertDatabaseRecord($sql); //require_all 'helpers';
            }
            
    }
    
           // loop round all ISBN's
            foreach ($textAr as $line) 
            {
                //
                // select books by book ID and ISBN.
                //
               $sql        = "SELECT * FROM BookISBNs WHERE bookID = ".$id." AND ISBN = '".trim($line)."'";
               $sql_result = GetDatabaseRecords($sql);
               $sql_num    = mysql_num_rows($sql_result);
		  // if found set confirmed flag for ISBN record.
                  if ($sql_num > 0)
                  {
                        $sql = "UPDATE BookISBNs SET Confirmed = 1 WHERE bookID = ".$id." AND ISBN = '".trim($line)."'";
                        UpdateDatabase($sql);
                  } else {
			                      // otherwise create a new one.
                                            $sql = "INSERT INTO BookISBNs (bookID, ISBN, Source, Confirmed) VALUES (".$id.",'".trim($line)."','VALIDATION',1)";
                                            $isbn_id = InsertDatabaseRecord($sql);
                              }
                   }
    

}


} //VALIDATION TYPE == book


?>

<html>
<head>
<link href="style/style.css" rel="stylesheet" type="text/css">

</head>
<body>
body started 

<?php 
echo  "user = ". $user

?>
<center>
<table border=0 cellspacing=30 width=850>
<tr><td width=10%></td>
<td width=80%>
<h1>Add Publisher</h1>
<form name="book" action="save.php" onsubmit="return validateForm()" method=post >
<input type=hidden name="validationType" value="new_publisher">
<table border=0>
<tr><td>Publisher Name - Russian</td><td><input type=text name="PublisherRU" size=50></td></tr>
<tr><td>Publisher Name - English</td><td><input type=text name="PublisherEN" size=50></td></tr>
<tr><td colspan=2><center><input type=submit value="Save Publisher" style="height: 25px; width: 150px"></center></td>
</table>
</form>
</td>
<td valign=top width=10%>

<input type=button onClick="window.location.href='index.php'" style="height: 25px; width: 150px;" value="Home Page">

</td>
</center>
</body>
</html>
