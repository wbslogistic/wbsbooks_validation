<!DOCTYPE html>
<html>
<head>
<title>Russian Books - Data Validation</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link href="style/style.css" rel="stylesheet" type="text/css">

<script>
	function validateForm()
	 {
	  var err = 0;
	  var x=document.forms["book"]["PublisherRU"];
	  if (x.value.length == 0)
	  {
		alert("Please enter Publisher Name - Russian");
		err = 1;
	  }


	  var x=document.forms["book"]["PublisherEN"];
	  if (x.value.length == 0)
	  {
		alert("Please enter Publisher Name - English");
		err = 1;
	  }

	  if (err == 1)
	  {
	   return false;
	  }
	   }
</script>

</head>
<body>
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
