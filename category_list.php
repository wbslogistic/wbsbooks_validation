<?PHP
require_once("./include/functions.php");
 error_reporting(E_ALL);
ini_set('display_errors', 'on');
if(!CheckLogin())
{
    RedirectToURL("login.php");
    exit;
}

recordActivity();
$username = $_SESSION['user_name'];
$userid = $_SESSION['user_id'];


$sql = "DELETE FROM BookLock WHERE userID = ". $_SESSION['user_id'];
UpdateDatabase($sql);


//Get Permissions
$sql = "SELECT * FROM Users WHERE userID = " . $userid;
$sql_result = GetDatabaseRecords($sql);

while($sql_row=pg_fetch_array($sql_result))
{
	$runStats = $sql_row['runstats'];
    
}


$sql = "SELECT *
  FROM categories 
  WHERE   name_en = '' OR name_en is null 
  LIMIT 10
  ";
  
$sql_result = GetDatabaseRecords($sql);
$array = [] ;
while($sql_row=pg_fetch_array($sql_result))
{
    $array[] = $sql_row;
    
}
//echo (string) $array[0]; 
$transations = $_POST['translations'];
$ids = $_POST['ids'];
//echo    "TRANS=".$transations[0];
if (count($transations) > 0)
{
    $index= 0;
foreach ($transations as $trans) 
{
$sql= "UPDATE categories
   SET  name_en='$trans' ,
    taxon_en = get_taxon_en(".$ids[$index].")   WHERE self_id =  ".$ids[$index];

UpdateDatabase($sql);

$sql= "UPDATE categories
   SET  taxon_en = get_taxon_en(".$ids[$index].")   WHERE self_id =  ".$ids[$index];
UpdateDatabase($sql);
    $index++;

}
}


?>

<html>
<head>
<link href="style/style.css" rel="stylesheet" type="text/css">
<style> 
.centered 
{
    vertical-align: center;
    text-align: center;
    width :200px;
    margin-left:50px;
    margin-right:50px;
    margin-top:50px;
    border:solid;
    border-width:2px;
    
}

.text_centered
{
    vertical-alignment:center; margin-left:330px;  margin-top:20px;
    }
table, th, td {
   border: 1px solid ;
}
</style> 
<title>Russian Books - Data Validation</title>

<link href="style/style.css" rel="stylesheet" type="text/css">

</head>
<body>
<h2 class="text_centered" >  Category translation  <h2> 
<?php
if (count($array) < 1)
{
   echo  "  <h2 class='text_centered'> All the categories are translated ! </h2>";
  }
?>

<form name="book" action="category_list.php" method='post' >
<table class="centered" style="width:100%">
<tr>
<td> <span> Category  Russian name </span>  </td> 
<td> <span> Category  English translation </span>  </td> 
</tr> 
  <tr>
    <?php
if (count($array) > 0)
{
foreach ($array as $value) {
    echo "<tr> <td>";
     echo $value['name'];
    echo "</td> <td>";
    echo "<textarea name='translations[]' id='trans' rows=5 cols=60></textarea><br />";
    echo "<input type='hidden' name='ids[]' value=".$value['self_id']." />";
    echo "</td> </tr>";
}
}
else 
{
  

}
?>


  
  <tr> 

  </table> 
<input type="submit" style=" margin-left:100px; width:170px; margin-top:4px; height:31px;" value ="Submit translations" /> 

</form>

</table> 
</form> 

</body> 
</html> 