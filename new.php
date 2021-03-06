<?php
require_once("./include/functions.php");
error_reporting(E_ALL ^ E_NOTICE);


if(!CheckLogin())
{
    RedirectToURL("login.php");
    exit;
}
recordActivity();
$userid   = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
//
// validation type set??
//
if (isset($_POST['validationType'])){
	$validationType = $_POST['validationType'];
        $id = $_POST['recordID'];
}



$user = GetUsername();
//
// validation type a book??
//
if ($validationType == "book")
{
	if (isset($_POST['actionType'])){
	    $actionType = $_POST['actionType'];
	}

	//
	// user selects the skip button -- so update the current record setting confirmed=TRUE, who confirmed it and when.
	//
	if ($actionType == "skip") {
		//$sql = "UPDATE new_validation_translation SET ConfirmedBy='".$user."' , dateConfirmed = CURRENT_TIMESTAMP";
		$sql = "UPDATE new_validation.new_validation SET Confirmed = -1, SessionID = '".$_SESSION['session_id']."' WHERE id = '".$id."'";
		UpdateDatabase($sql);
	} else {
            $t_ru = $_POST['TitleRU'];
            $t_en = $_POST['TitleEN'];
            $d_ru = $_POST['DescriptionRU'];
            $d_en = $_POST['DescriptionEN'];
            $publishers = $_POST['publishers'];
            $a_id = $_POST['Author'];
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
                  $sql = "SELECT * FROM new_validation.validation_authors WHERE authorID = ".$a_id;
                  $sql_result = GetDatabaseRecords ( $sql );
                  $sql_num=mysql_num_rows          ( $sql_result );

                  if ($sql_num > 0)
                  {
                    $sql = "UPDATE new_validation.validation_authors SET AuthorRU = '".$a_ru."', AuthorEN = '".$a_en."', Confirmed=1, ConfirmedBy='".$user."', DateConfirmed=CURRENT_TIMESTAMP WHERE AuthorID = ".$a_id;
                    UpdateDatabase($sql);
                  } else {
                      $sql = "INSERT INTO new_validation.validation_authors (AuthorRU, AuthorEN, Confirmed, ConfirmedBy, DateConfirmed) VALUES ('".$a_ru."','".$a_en."',1,'".$user."',CURRENT_TIMESTAMP)";
                      $a_id = InsertDatabaseRecord($sql);
                  }
            } else {
                //
                // author not set so create a new one.
                //
                if ($a_ru!="")
                {
                   $sql = "INSERT INTO new_validation.validation_authors (AuthorRU, AuthorEN, Confirmed, ConfirmedBy, DateConfirmed) VALUES ('".$a_ru."','".$a_en."',1,'".$user."',CURRENT_TIMESTAMP)";
                   $a_id = InsertDatabaseRecord($sql);
                }
            }
            // loop round all ISBN's
            foreach ($textAr as $line) {
                //
                // select books by book ID and ISBN.
                //
               $sql        = "SELECT * FROM new_validation.validation_books_ISBN WHERE bookID = ".$id." AND ISBN = '".trim($line)."'";
               $sql_result = GetDatabaseRecords($sql);
               $sql_num    = mysql_num_rows($sql_result);
		  // if found set confirmed flag for ISBN record.
                  if ($sql_num > 0)
                  {
                        $sql = "UPDATE new_validation.validation_books_ISBN SET Confirmed = 1 WHERE bookID = ".$id." AND ISBN = '".trim($line)."'";
                        UpdateDatabase($sql);
                  } else {
			// otherwise create a new one.
                        $sql = "INSERT INTO new_validation.validation_books_ISBN (bookID, ISBN, Source, Confirmed) VALUES (".$id.",'".trim($line)."','VALIDATION',1)";
                        $isbn_id = InsertDatabaseRecord($sql);
                  }

        }
        //
        // set Confirmed=1.
        //
        // create a new translation record -- only IF it has changed.
        // inserts the book ID, title, description, date confirmed, date Last effective, and who confirmed it.
	//
	$sql = "SELECT * FROM new_validation.new_validation_translation WHERE TitleEN = '" . $t_en . "' AND DescriptionEN = '" . $d_en . "'";
	$res = GetDatabaseRecords($sql);
	$num = mysql_num_rows($res);
	if ( $num == 0 ) {
	        $sql = "INSERT INTO new_validation.new_validation_translation ( bookID , TitleEN , DescriptionEN , dateConfirmed , dateLastEffective , ConfirmedBy ) values ('" . $id . "', '" . $t_en . "' , '" . $d_en . "', now() , null , $userid  );";
	        UpdateDatabase($sql);
	}
        $sql = "UPDATE new_validation.new_validation SET TitleRU = '".$t_ru."', DescriptionRU = '".$d_ru."', Author = '" . $a_id . "' ,Confirmed=1  WHERE id = ".$id;
        UpdateDatabase($sql);


        // update all titles and descriptions with the updated one - e.g. if there are 2 books a hard & soft cover copy both titles of these will be updated.
        // Update other books with the same titles
        /*$sql = "UPDATE validation_books AS CURBOOK
        INNER JOIN validation_books AS OTHER ON CURBOOK.TitleRU = OTHER.TitleRU
        SET OTHER.TitleEN = CURBOOK.TitleEN, OTHER.TitleConfirmed = 1, OTHER.TitleSourceID = CURBOOK.bookID
        WHERE CURBOOK.bookID = ".$id." AND OTHER.TitleConfirmed = 0";*/
        $sql = "UPDATE new_validation.new_validation_translation AS CURBOOK
        INNER JOIN new_validation_translation AS OTHER ON CURBOOK.TitleEN = OTHER.TitleEN
        SET OTHER.TitleEN = CURBOOK.TitleEN
        WHERE CURBOOK.bookID = ".$id;
        UpdateDatabase($sql);

        // as above but for descriptions.
        // Update other books with the same description
        /*$sql = "UPDATE validation_books AS CURBOOK
        INNER JOIN validation_books AS OTHER ON CURBOOK.DescriptionRU = OTHER.DescriptionRU
        SET OTHER.DescriptionEN = CURBOOK.DescriptionEN, OTHER.DescriptionConfirmed = 1, OTHER.DescriptionSourceID = CURBOOK.bookID
        WHERE CURBOOK.bookID = ".$id." AND OTHER.DescriptionConfirmed = 0";*/
        $sql = "UPDATE new_validation.new_validation_translation AS CURBOOK
        INNER JOIN new_validation_translation AS OTHER ON CURBOOK.DescriptionEN = OTHER.DescriptionEN
        SET OTHER.DescriptionEN = CURBOOK.DescriptionEN
        WHERE CURBOOK.bookID = ".$id;
        UpdateDatabase($sql);

        $sql = "DELETE FROM new_validation.validation_books_publishers WHERE bookID = '".$id."'";
        UpdateDatabase($sql);
            foreach ($publishers as $pub){
                $sql = "INSERT INTO new_validation.validation_books_publishers (bookID, PublisherID) VALUES ('".$id."','".$pub."')";
                UpdateDatabase($sql);
            }
        $sql = "UPDATE new_validation.UserViews SET Validated = 1 WHERE UserViewsID = ".$userviewsid;
        UpdateDatabase($sql);
        }
    }
    //
    //
    // ##############################################################################################
    //
    //




// release book lock.
$sql = "DELETE FROM new_validation.BookLock WHERE userID = '".$userid."'";
UpdateDatabase($sql);

// book id set?
if (isset($_GET['bookid'])){
  $bookid = $_GET['bookid'];

  $sql = "SELECT T1.*,PublisherNameRU ,
    CASE
                WHEN T2.idSuggestedTitlesListItems is not null THEN
                        0
                WHEN C1.priority is not null THEN
                        C1.priority
                ELSE
                        99
                END AS OrderList
        , CASE WHEN T2.idSuggestedTitlesListItems is null THEN 0 ELSE 1 END AS SuggList
	FROM new_validation.new_validation AS T1
        LEFT JOIN WBSBooks.SuggestedTitlesListItems AS T2 ON T1.id = T2.bookID
        LEFT JOIN ozon.ozon_book_categories AS O1 ON T1.ozon_id = O1.id
        LEFT JOIN ozon.ozon_categories AS C1 ON O1.category_id = C1.id
        LEFT JOIN new_validation.BookLock AS BL ON T1.id = BL.bookID
	LEFT JOIN new_validation.PublisherList PL ON T1.Publisher = PL.PublisherId
	WHERE T1.id = $bookid
        LIMIT 1";
} else {
  // book id not set?? so get next book where confirmed=0.
  $sql = "SELECT T1.*
	,
	CASE
        WHEN T1.CategoryID = 8108 THEN 20000000 - CASE WHEN T1.Year is null THEN 0 ELSE T1.Year END + RAND()
        WHEN T2.idSuggestedTitlesListItems is null AND DescriptionEN is not null AND DescriptionEN != '' THEN
		50000000 - CASE WHEN T1.Year is null THEN 0 ELSE T1.Year END + RAND()
	WHEN T2.idSuggestedTitlesListItems is null AND (DescriptionEN is null OR DescriptionEN = '') THEN
		70000000 - CASE WHEN T1.Year is null THEN 0 ELSE T1.Year END + RAND()
	ELSE RAND() END AS OrderList
	, CASE WHEN T2.idSuggestedTitlesListItems is null THEN 0 ELSE 1 END AS SuggList
	FROM new_validation.new_validation AS T1
	LEFT JOIN WBSBooks.SuggestedTitlesListItems AS T2 ON T1.id = T2.bookID
	WHERE T1.TitleRU is not null
	AND T1.confirmed=0 AND T1.TitleRU != '' AND T1.DescriptionRU is not null AND T1.DescriptionRU != ''
	ORDER BY OrderList
	LIMIT 1";


  $sql = "SELECT T1.*, PL.PublisherNameRU ,
	CASE
		WHEN T2.idSuggestedTitlesListItems is not null THEN
			0
		WHEN C1.priority is not null THEN
			C1.priority
		ELSE
			99
		END AS OrderList
        , CASE WHEN T2.idSuggestedTitlesListItems is null THEN 0 ELSE 1 END AS SuggList
        FROM new_validation.new_validation AS T1
        LEFT JOIN WBSBooks.SuggestedTitlesListItems AS T2 ON T1.id = T2.bookID
	LEFT JOIN ozon.ozon_book_categories AS O1 ON T1.ozon_id = O1.id
	LEFT JOIN ozon.ozon_categories AS C1 ON O1.category_id = C1.id
	LEFT JOIN new_validation.BookLock AS BL ON T1.id = BL.bookID
	LEFT JOIN new_validation.PublisherList PL ON T1.Publisher = PL.PublisherId
        WHERE BL.idBookLock is null AND T1.TitleRU is not null
        AND T1.confirmed=0 AND T1.TitleRU != '' AND T1.DescriptionRU is not null AND T1.DescriptionRU != ''
        ORDER BY OrderList, T1.Year DESC, RAND()
        LIMIT 1";

}
$sql_result = GetDatabaseRecords($sql);
$sql_num=mysql_num_rows($sql_result);


// get book data....
while($sql_row=mysql_fetch_array($sql_result))
{
        //$id=$sql_row["bookID"];
        $id   = $sql_row["id"];
        $t_en = $sql_row["titleRU"];
        $t_ru = $sql_row["titleRU"];
        $d_ru = $sql_row["descriptionRU"];
        $d_en = $sql_row["descriptionRU"];
        $barcode=$sql_row["barcode"];
        $publisherID=$sql_row["Publisher"];
        $publisherName=$sql_row["PublisherNameRU"];
        $author=$sql_row["Author"];
        $year=$sql_row["Year"];
        $source_id=$sql_row["source_id"];
        $ozon_id=$sql_row["ozon_id"];
}

//
// get Title , Description etc from the translations table
// for this book ID. based on the dateConfirmed date (latest) record.
//
$sql = "select * from new_validation.new_validation_translation where bookID = " . $id . " order by dateConfirmed DESC LIMIT 1";
// .. FILL IN BLANKS to get english translation text to display in 2nd box
$sql_result = GetDatabaseRecords($sql);
$sql_num=mysql_num_rows($sql_result);
// get book data....
if ( $sql_num != 0 ) {
	while($sql_row=mysql_fetch_array($sql_result))
	{
        	$t_en= $sql_row["TitleEN"];
		$d_en =$sql_row["DescriptionEN"];
	}
}
else
{
	$t_en = "";
	$d_en = "";
}



InsertDatabaseRecord("INSERT INTO new_validation.BookLock (bookID, userID) VALUES ('".$id."', '".$userid."')");
// update record of who isviewing the book....
$userviewsid = InsertDatabaseRecord("INSERT INTO new_validation.UserViews (userid, sessionID, bookID, DateViewed, Validated) VALUES ('".$userid."','".$_SESSION['session_id']."','".$id."',CURRENT_TIMESTAMP,0)");


if ($id=="")
{
   echo "<script>alert('No data to be validated')</script>";
   echo "<script>navigate('index.php')</script>";
   exit();
}

// get the publisher(s) for this book.
$sql="SELECT PublisherID FROM new_validation.validation_books_publishers WHERE bookID=".$id;
$result = GetDatabaseRecords($sql);
$selected_publishers=mysql_fetch_array($result);
// get list of publishers.
$sql="SELECT PublisherID, PublisherNameRU, PublisherNameEN FROM new_validation.PublisherList ORDER BY PublisherNameRU";
$result = GetDatabaseRecords($sql);


// loop round the publishers list -- make up a drop down list.
$options="";
while ($row=mysql_fetch_array($result)) {
    $pub_id=$row["PublisherID"];
    $pub_name=$row["PublisherNameRU"];
    $pub_name_EN=$row["PublisherNameEN"];

    $sql = "SELECT PublisherID FROM new_validation.validation_books_publishers WHERE bookID=".$id." AND PublisherID=".$pub_id;
    $sql_result = GetDatabaseRecords($sql);
    $sql_num=mysql_num_rows($sql_result);

	if ($sql_num > 0)
	{
		$s_publishers.="<option value=\"$pub_id\">$pub_name ($pub_name_EN)</option>";
	} else {
    		$combo.="<option value=\"$pub_id\">$pub_name ($pub_name_EN)</option>";
	}
}

// get ISBN's for book.
$sql="SELECT DISTINCT ISBN FROM new_validation.validation_books_ISBN WHERE bookID = ".$id." ORDER BY ISBN";
$result = GetDatabaseRecords($sql);
$isbn="";
while ($row=mysql_fetch_array($result)) {
    $isbn_text=$row["ISBN"];
    $isbn.=$isbn_text."\r";
}
$isbn.=$barcode;

// get author data for author id.
if ($author!="")
{
    $sql = "SELECT * FROM new_validation.validation_authors WHERE AuthorID=".$author;
    $result = GetDatabaseRecords($sql);

    while($sql_row=mysql_fetch_array($result))
    {
        $a_confirmed=$sql_row["Confirmed"];
	$a_ru=$sql_row["authorRU"];
	$a_en=$sql_row["authorEN"];
    }
} else {
	$a_confirmed=0;
	$author="";
	$a_ru="";
	$a_en="";
}

$image= "http://88.208.201.26/product_images/".$id.".jpg";

?>
<!DOCTYPE html>
<html>
<head>
<title>Russian Books - Data Validation</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link href="style/style.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script src="js/combo.js"></script>
<script src="js/tabs_old.js"></script>


<style>
	.custom-combobox {
		position: relative;
		display: inline-block;
	}
	.custom-combobox-toggle {
		position: absolute;
		top: 0;
		bottom: 0;
		margin-left: -1px;
		padding: 0;
		/* support: IE7 */
		*height: 1.7em;
		*top: 0.1em;
	}
	.custom-combobox-input {
		margin: 0;
		padding: 0.3em;
	}
</style>

<script>
	function validateForm()
	 {
	 var x=document.forms["book"]["authorConfirmed"];
	 if (x.checked == false && x.disabled == false)
	   {
	   alert("Author Not Confirmed");
	   return false;
	   }


	   var selObj = document.getElementById("publishers");
  	   for (var i=0; i<selObj.options.length; i++) {
    	      selObj.options[i].selected = true;
  	   }

	return true;

	}

	function revalidate_Author()
	{
		book.authorEN.readOnly = false;
		book.authorRU.readOnly = false;

		book.authorConfirmed.disabled = false;
		book.authorConfirmed.checked = false;

	}

	function submitBook()
	{
	    if (validateForm()) {
		document.forms["book"].submit();
	    }
	}

        function skipBook()
	{
	     if (confirm('Please confirm there is a problem with this book stopping you from validating it?')) {

	     $('#actionType').val('skip');
	     document.forms["book"].submit();

	     }
	}

</script>

</head>
<body>
<center>
<table border=0 cellspacing=30>

<tr>
<td valign=top>

<p><img src=<?= $image ?> width=200px></p>
<?

	if ($suggList == 1)
	{
		echo "<p><strong>SUGGESTED LIST</strong></p>";
	} else {
		echo "<p><strong>CATEGORY PRIORITY : ".$priority."</strong></p>";
	}
?>
<p><?= $id ?></p>
</td>

<td valign=top>


	<form name="book" action="<?php echo basename($_SERVER['PHP_SELF']) ?>" method=post >
	<input type=hidden name="validationType" value="book">
	<input type=hidden name="recordID" value="<?= $id ?>">
	<input type=hidden name="UserViewsID" value="<?= $userviewsid ?>">
	<input type=hidden id="actionType" name="actionType" value="">

		<center>
		<table border=1>
			<tr><td valign=top>Russian Title</td><td align=left><input type=text value ="<?= $t_ru ?>" name="TitleRU" size=50></td><td width=50px></td><td valign=top>Publication Year</td><td align=left valign=top><?= $year ?></td></tr>
			<tr><td valign=top>English Title</td><td align=left><input type=text value ="<?= $t_en ?>" name="TitleEN" size=50> Confirmed: <input type=checkbox <?= $t_confirmed==1 ? 'checked disabled' : '' ?>></td><td></td><td valign=top rowspan=3>ISBN</td><td rowspan=3><textarea name="ISBN" rows=5 cols=20><?= $isbn ?></textarea></td></tr>
<tr><td></td><td></td><td></td></tr>
			<tr><td valign=top>Publisher Name</td><td align=left><?= $publisherName ?></td><td></td></tr>
			<tr><td valign=top>Publisher Match


			</td><td colspan=5 align=left>

				<table border=0>
				    <tr><td width=550>
<div class="ui-widget">
	<label>Select Publisher: </label>
	<select id="combobox">
		<option value=-1>Select one...</option>
		<?=$combo?>
	</select>
</div>

</td><td rowspan=2 valign=top>
	<input type="button" id="addPub" value="Add Publisher"> <input type="button" id="refresh" value="Refresh Publishers"><br /><br />
	<input type="button" id="deletePub" value="Remove Publisher">
</td></tr>
<tr><td>

<select id="publishers" name="publishers[]" multiple=true size=10 width=500 style="width: 500px;">
<?=$s_publishers?>
</select>

</td></tr>
</table>
</div>

</td></tr>
			<tr><td colspan=8>
				<div id="tabContainer">
					<div id="tabs">
					  <ul>
						<li id="tabHeader_1">Description ***</li>
						<li id="tabHeader_3">Author<?= $a_confirmed==0 ? '**' : '' ?></li>
					  </ul>
					</div>
					<div id="tabscontent">
					  <div class="tabpage" id="tabpage_1">
						<h2>Description</h2>
						<p>
							<center>
							<table border=1>
								<tr><td>Russian Text</td><td><textarea rows="9" cols="80" name="DescriptionRU"><?= $d_ru ?></textarea></td></tr>
								<tr><td>English Text</td><td><textarea rows="9" cols="80" name="DescriptionEN"><?= $d_en ?></textarea></td></tr>
								<tr><td colspan=2 align=left> Confirmed: <input type=checkbox <?= $d_confirmed==1 ? 'checked disabled' : '' ?></td></tr>
							</table>
							</center>
						</p>
					  </div>
					  <div class="tabpage" id="tabpage_3">
						<h2>Author Details</h2>
						<p>
							<center>
							<table border=1>
								<tr><td>Russian Text</td><td><textarea rows="4" cols="55" name="authorRU" <?= $a_confirmed==1 ? 'readonly' : '' ?> ><?= $a_ru ?></textarea></td></tr>
								<tr><td>English Text</td><td><textarea rows="4" cols="55" name="authorEN" <?= $a_confirmed==1 ? 'readonly' : '' ?> ><?= $a_en ?></textarea></td></tr>
								<tr><td>Confirmed</td><td align=left><input type=hidden name="authorID" value="<?= $author ?>"><input type=checkbox name="authorConfirmed" <?= $a_confirmed==1 ? 'checked disabled' : '' ?> ></td></tr>
								<tr><td></td><td><input type=button value="Invalid Validation" onClick="revalidate_Author()"></td></tr>
							</table>
							</center>
						</p>
					  </div>
					</div>
				</div>
			</td></tr>
			<tr><td colspan=8>
			<table width=100%>
			<tr>
			    <td><center><input type=button value="Confirm Validation" style="height: 25px; width: 150px" onclick="submitBook()"></center></td>
			    <td></td>
			    <td><center><input type=button value="Problem Book" style="height: 25px; width: 150px" onclick="skipBook()"></center></td>
			</tr>
			</table>
			</td></tr>

		</table>
	</form>

</td>
<td valign=top>

<input type=button onClick="window.location.href='index.php'" style="height: 25px; width: 150px;" value="Home Page"><br>
<input type=button style="height: 25px; width: 150px" onClick="window.location.href='logoff.php'" value="Log Off"><br>
<?php



$sql = "SELECT count(*) as noConfirmed FROM new_validation WHERE Confirmed=1 ";
    $result = GetDatabaseRecords($sql);

    while($sql_row=mysql_fetch_array($result))
    {
        $books_confirmed=$sql_row["noConfirmed"];
    }


$sql = "SELECT count(*) as noConfirmed FROM new_validation WHERE Confirmed = 0 ";
    $result = GetDatabaseRecords($sql);

    while($sql_row=mysql_fetch_array($result))
    {
        $books_remaining=$sql_row["noConfirmed"];
    }





$sql = "SELECT userID,
SUM(NoBooks) AS TotalBooks,
SEC_TO_TIME(SUM(TimeWorked)) AS TotalTimeWorked,
SUM(NoBooks) / (SUM(TimeWorked)/3600) AS Daily_AveragePerHour
FROM
(
SELECT userID, COUNT(id) AS NoBooks,
        TIME_TO_SEC(CASE WHEN LogoffDate is null THEN LastActivity ELSE LogoffDate END) - TIME_TO_SEC(LoginDate) AS TimeWorked, 
		CASE WHEN LogoffDate is null THEN LastActivity ELSE LogoffDate END,
		LoginDate
        FROM new_validation.UserSessions
        INNER JOIN new_validation.new_validation ON UserSessions.UserHistoryID = new_validation.SessionID
        WHERE userID = ".$userid." AND LoginDate > CURDATE() AND new_validation.Confirmed = 1
        GROUP BY userHistoryID, userID
) AS TimeWorked
GROUP BY userID";
$result = GetDatabaseRecords($sql);

    while($sql_row=mysql_fetch_array($result))
    {
        $no_books=$sql_row["TotalBooks"];
        $hourly_avg=$sql_row["Daily_AveragePerHour"];
    }

?>

<table>
<tr><td>Total Validated</td><td><?= $books_confirmed ?></td></tr>
<tr><td>Remaining</td><td><?= $books_remaining ?></td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>No Books</td><td><?= $no_books ?></td></tr>
<tr><td>Hourly Avg</td><td><?= $hourly_avg ?></td></tr>
</table>
</td>
</tr>
</table>
</center>




<script>
$('#addPub').click(function() {
    var pub_id = $('#combobox option:selected').val();
    var pub_name = $('#combobox option:selected').text();

    if (pub_id == -1) {
      return;
    }


    //check if pub_id is already in the selected list

    $('#publishers').append($('<option>', {
          value: pub_id,
          text: pub_name
    }));

    $('#combobox option:selected').remove();

    $("#combobox option[value=-1]").attr('selected', 'selected'); 


        var selectOptions = $("#publishers option");

        selectOptions.sort(function(a, b) {
            if (a.text > b.text) {
                return 1;
            }
            else if (a.text < b.text) {
                return -1;
            }
            else {
                return 0
            }
        });

        $("#publishers").empty().append(selectOptions);

});

$('#deletePub').click(function() {
    var pub_id = $('#publishers option:selected').val();
    var pub_name = $('#publishers option:selected').text();

    if (confirm("You are about to remove publisher "+pub_name)) {
        $('#publishers option:selected').remove();

        $('#combobox').append($('<option>', {
          value: pub_id,
          text: pub_name
        }));

        var selectOptions = $("#combobox option");

        selectOptions.sort(function(a, b) {

            if (a.value == -1 || b.value == -1) {
                return 1;
            }
            else if (a.text > b.text) {
                return 1;
            }
            else if (a.text < b.text) {
                return -1;
            }
            else {
                return 0
            }
        });

        $("#combobox").empty().append(selectOptions);

    }

});

$('#refresh').click(function() {

   $("#combobox").load('pubList.php', function() {
     alert('Load was performed.');
   });

});

</script>

</body>
