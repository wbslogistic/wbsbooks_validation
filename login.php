<html>
<head>
<title>Russian Books - Data Validation</title>

<link href="style/style.css" rel="stylesheet" type="text/css">

</head>
<body>
	<center>
	Welcome to the Russian Books Online Validation<br>
	Please enter your user details to access the system
	
	<form action="process_login.php" method="post">
	<table border=1>
		<tr><td>Username</td><td><input type=text name="myusername"></td></tr>
		<tr><td>Password</td><td><input type=password name="mypassword"></td></tr>
		<tr><td colspan=2><center><input type=submit value="Log In" style="height: 25px; width: 100px"></center></td></tr>
	</table>
	</form>
	<center>

<?php

    if (isset($_GET['error']))
    {
        $error = $_GET['error'];

        if (!empty($error))
        {
            echo "<script>alert('Wrong username or password');</script>";
        }
    }
?>


</body>
