<?PHP
require_once("./include/functions.php");

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

while($sql_row=mysql_fetch_array($sql_result))
{
	$addPublisher = $sql_row['AddPublisher'];
	$runStats = $sql_row['RunStats'];

}

?>

<html>
<head>
<title>Russian Books - Data Validation</title>

<link href="style/style.css" rel="stylesheet" type="text/css">

</head>
<body>

	Welcome <?= $username ?> to the Russian Books Online Validation<br>
	Please select your version of validation

	<center>
	<table border=0>
<!--		<tr><td>Validation of Authors</td><td><input type=button style="height: 25px; width: 100px" onClick="window.location.href='standard_validation.php?validationType=Author'" value="Click Here"></td></tr>
		<tr><td>Validation of Genres</td><td><input type=button style="height: 25px; width: 100px" onClick="window.location.href='standard_validation.php?validationType=Genre'" value="Click Here"></td></tr>
		<tr><td>Validation of Categories</td><td><input type=button style="height: 25px; width: 100px" onClick="window.location.href='standard_validation.php?validationType=Category'" value="Click Here"></td></tr>
-->
<?php
	if ($addPublisher == 1) {
?>

		<tr><td colspan=2><hr></td></tr>
		<tr><td>Add New Publisher</td><td><input type=button style="height: 25px; width: 100px" onClick="window.location.href='add_publisher.php'" value="Click Here"></td></tr>

<?php
}

	if ($runStats == 1) {
?>

		<tr><td colspan=2><hr></td></tr>
		<tr><td>Run User Stats</td><td><input type=button style="height: 25px; width: 80px" onClick="window.location.href='user_stats.php?days=7'" value="7 Days"><input type=button style="height: 25px; width: 80px" onClick="window.location.href='user_stats.php?days=30'" value="30 Days"></td></tr>

<?php
}
?>
		<tr><td colspan=2><hr></td></tr>
		<tr><td>Validation of Books</td><td><input type=button style="height: 25px; width: 100px" onClick="window.location.href='validate_books.php'" value="Click Here"></td></tr>
		<tr><td colspan=2><hr></td></tr>
		<tr><td colspan=2><input type=button style="height: 25px; width: 100px" onClick="window.location.href='logoff.php'" value="Log Off"></td></tr>
	</table>
	<center>
</body>
