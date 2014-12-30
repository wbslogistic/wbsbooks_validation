<?php

require_once("./include/functions.php");
 error_reporting(E_ALL);

if(!CheckLogin())
{
    RedirectToURL("login.php");
    exit;
}
recordActivity();
//session_start(); fix: session should eb
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
    $book_validated = 'true';
	if (isset($_POST['actionType'])){
	    $actionType = $_POST['actionType'];
	}

	//
	// user selects the skip button -- so update the current record setting confirmed=TRUE, who confirmed it and when.
	//
	if ($actionType == "skip") {
		$sql = "UPDATE products SET Confirmed = -99, SessionID = '".$_SESSION['session_id']."' WHERE id = '".$id."'";
		UpdateDatabase($sql);
	} else {
            $t_ru = $_POST['TitleRU'];
            $t_en = $_POST['TitleEN'];
            $d_ru = $_POST['DescriptionRU'];
            $d_en = $_POST['DescriptionEN'];
            $publishers = $_POST['publishers'];
             
            $a_id = $_POST['author_ID'];
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
                  $sql_num=pg_num_rows( $sql_result );

                  if ($sql_num > 0)
                  {
                    $sql = "UPDATE Authors SET AuthorRU = '".$a_ru."', AuthorEN = '".$a_en."', Confirmed=1, ConfirmedBy='".$userid."', DateConfirmed=now()::timestamp WHERE AuthorID = ".$a_id;
                    UpdateDatabase($sql);
                  } else {
                      $sql = "INSERT INTO Authors (AuthorRU, AuthorEN, Confirmed, ConfirmedBy, DateConfirmed) VALUES ('".$a_ru."','".$a_en."',1,'".$userid."',now()::timestamp)";
                       $a_id = InsertDatabaseRecord($sql);
                      $sql =  "SELECT max(authorid) as id from Authors";
                      $result_1 =GetDatabaseRecords($sql);
                      $author_new_id =  pg_fetch_array($result_1);
                      $a_id = $author_new_id["id"]; 
                  }
            } else {
                //
                // author not set so create a new one.
                //
                if ($a_ru!="")
                {
                      $sql = "INSERT INTO Authors (AuthorRU, AuthorEN, Confirmed, ConfirmedBy, DateConfirmed) VALUES ('".$a_ru."','".$a_en."',1,'".$userid."',now()::timestamp)";
                      $a_id = InsertDatabaseRecord($sql); 
                      $sql =  "SELECT max(authorid) as id from Authors";
                      $result_1 =GetDatabaseRecords($sql);
                      $author_new_id =  pg_fetch_array($result_1);
                      $a_id = $author_new_id["id"]; 
                }
            }
            // loop round all ISBN's
            foreach ($textAr as $line) {
                //
                // select books by book ID and ISBN.
                //
               $sql     = "SELECT * FROM BookISBNs WHERE bookID = ".$id." AND ISBN = '".trim($line)."'";
               $sql_result = GetDatabaseRecords($sql);
               $sql_num    = pg_num_rows($sql_result);
		  // if found set confirmed flag for ISBN record.
                  if ($sql_num > 0)
                  {
                        $sql = "UPDATE BookISBNs SET Confirmed = 't' WHERE bookID = ".$id." AND ISBN = '".trim($line)."'";
                        UpdateDatabase($sql);
                  } else {
			// otherwise create a new one.
                        $sql = "INSERT INTO BookISBNs (bookID, ISBN, Source, Confirmed) VALUES (".$id.",'".trim($line)."','VALIDATION','t')";
                        $isbn_id = InsertDatabaseRecord($sql);
                  }

        }


	$sql = "UPDATE BookTranslations SET dateLastEffective = now()::timestamp WHERE bookID = " . $id . " AND dateLastEffective is null";
	UpdateDatabase($sql);


        //
        // set Confirmed=1.
        //
        // create a new translation record -- only IF it has changed.
        // inserts the book ID, title, description, date confirmed, date Last effective, and who confirmed it.
	//

	$sql = "INSERT INTO BookTranslations ( bookID , TitleEN , DescriptionEN , dateConfirmed , dateLastEffective , ConfirmedBy ) values ('" . $id . "', '" . $t_en . "' , '" . $d_en . "', now() , null , $userid  );";
	UpdateDatabase($sql);

        // update all titles and descriptions with the updated one - e.g. if there are 2 books a hard & soft cover copy both titles of these will be updated.
        // Update other books with the same titles

	// Update matching Titles carry forward Descriptions
	$sql = "UPDATE BookTranslations tr
SET dateLastEffective = now()
WHERE tr.dateLastEffective is null and tr.bookID in  (SELECT Books.id FROM products as Books INNER JOIN 
    (SELECT * FROM products WHERE id = $id) AS ORIG ON Books.TitleRU
     like ORIG.TitleRU AND Books.id != ORIG.id) or tr.bookID =$id";
	UpdateDatabase($sql);

	$sql = "INSERT INTO BookTranslations (bookID, TitleEN, DescriptionEN, dateConfirmed, dateLastEffective, ConfirmedBy, SyncCode)
                SELECT t1.id, '$t_en', DescriptionEN, now(), null, $userid AS userID, 1 FROM products AS t1
                                INNER JOIN (
                        SELECT Books.id FROM products  as Books
                        INNER JOIN (SELECT * FROM products WHERE id = $id)
                        AS ORIG ON Books.TitleRU like ORIG.TitleRU AND Books.id != ORIG.id)
                AS FindDuplicates ON t1.id = FindDuplicates.id
                                LEFT JOIN
                                (
                                        SELECT t2.* FROM
                                        BookTranslations t2
                                        INNER JOIN (SELECT bookID, MAX(BookTranslationID) BookTranslationID FROM BookTranslations GROUP BY bookID) t3
                                                ON t2.bookID = t3.bookID AND t2.BookTranslationID = t3.BookTranslationID
                                        INNER JOIN (
                        SELECT Books.id FROM products as Books
                        INNER JOIN (SELECT * FROM products WHERE id = $id)
                        AS ORIG ON Books.TitleRU like ORIG.TitleRU AND Books.id != ORIG.id)
                                        AS FindDuplicates ON t2.bookID = FindDuplicates.id
                                ) as t4 ON t1.id = t4.bookID";
	UpdateDatabase($sql);


	// Update matching Descriptions carry forward Titles
	$sql = "UPDATE BookTranslations tr
SET dateLastEffective = now()
WHERE tr.dateLastEffective is null and tr.bookID in  (SELECT Books.id FROM products as Books INNER JOIN 
    (SELECT * FROM products WHERE id = $id) AS ORIG ON Books.TitleRU
     like ORIG.TitleRU AND Books.id != ORIG.id) or tr.bookID =$id;";
	UpdateDatabase($sql);
	$sql = "INSERT INTO BookTranslations (bookID, TitleEN, DescriptionEN, dateConfirmed, dateLastEffective, ConfirmedBy, SyncCode)
		SELECT t1.id, TitleEN, '$d_en' AS DescriptionEN, now(), null, $userid AS userID, 2 FROM products AS t1
				INNER JOIN (  SELECT Books.id FROM products as Books
                        INNER JOIN (SELECT * FROM products WHERE id = $id)
                        AS ORIG ON Books.DescriptionRU like ORIG.DescriptionRU AND Books.id != ORIG.id)
                AS FindDuplicates ON t1.id = FindDuplicates.id
				LEFT JOIN 
				(	SELECT t2.* FROM
					BookTranslations t2
					INNER JOIN (SELECT bookID, MAX(BookTranslationID) BookTranslationID FROM BookTranslations GROUP BY bookID) t3
						ON t2.bookID = t3.bookID AND t2.BookTranslationID = t3.BookTranslationID
					INNER JOIN (
                        SELECT Books.id FROM products as Books
                        INNER JOIN (SELECT * FROM products WHERE id = $id)
                        AS ORIG ON Books.DescriptionRU like ORIG.DescriptionRU AND Books.id != ORIG.id)
					AS FindDuplicates ON t2.bookID = FindDuplicates.id
				) as t4 ON t1.id = t4.bookID";
	UpdateDatabase($sql);



        $sql = "DELETE FROM BookPublishers WHERE bookID = '".$id."'";
        UpdateDatabase($sql);
        
        echo "PUBLISHERS 1=".$publishers;
        //TODO: In future several publisher will have to be added. In the old structure it was not working at all 
                $sql = "INSERT INTO BookPublishers (bookID, PublisherID) VALUES ('".$id."','".$publishers."')";
                UpdateDatabase($sql);
           ///    foreach ($publishers as $pub){
           // }
       
	$sql = "UPDATE products SET author_id = ". $a_id .", SessionID = '".$_SESSION['session_id']."', Confirmed = 1 WHERE id = ".$id;
	UpdateDatabase($sql);

        $sql = "UPDATE UserViews SET Validated = 1 WHERE UserViewsID = ".$userviewsid;
        UpdateDatabase($sql);
        }
    }
    //
    //
    // ##############################################################################################
    //
    //




// release book lock.
$sql = "DELETE FROM BookLock WHERE userID = '".$userid."'";
UpdateDatabase($sql);

// book id set?
if (isset($_GET['bookid'])){
  $bookid = $_GET['bookid'];

  $sql = "SELECT T1.* ,
    CASE
                WHEN T2.idSuggestedTitlesListItems is not null THEN
                        0
                WHEN C1.priority is not null THEN
                        C1.priority
                ELSE
                        99
                END AS OrderList
        , CASE WHEN T2.idSuggestedTitlesListItems is null THEN 0 ELSE 1 END AS SuggList
	FROM products AS T1
	FROM products AS T1
        LEFT JOIN SuggestedTitlesListItems AS T2 ON T1.id = T2.bookID
        LEFT JOIN ozon.ozon_book_categories AS O1 ON T1.ozon_id = O1.id
        LEFT JOIN ozon.ozon_categories AS C1 ON O1.category_id = C1.id
        LEFT JOIN BookLock AS BL ON T1.id = BL.bookID
	WHERE T1.id = $bookid
        LIMIT 1";
} else {
  // book id not set?? so get next book where confirmed=0.

  $sql = "SELECT T1.* ,
	CASE
		WHEN T2.idSuggestedTitlesListItems is not null THEN
			0
		WHEN C1.priority is not null THEN
			C1.priority
		ELSE
			99
		END AS OrderList
        , CASE WHEN T2.idSuggestedTitlesListItems is null THEN 0 ELSE 1 END AS SuggList
        FROM products AS T1
        LEFT JOIN SuggestedTitlesListItems AS T2 ON T1.id = T2.bookID
	LEFT JOIN ozon_prod_caty_rel AS O1 ON T1.ozon_id = O1.book_id
	LEFT JOIN categories AS C1 ON O1.category_id = C1.self_id
	LEFT JOIN BookLock AS BL ON T1.id = BL.bookID
        WHERE BL.idBookLock is null AND T1.TitleRU is not null
        AND (T1.confirmed=0  or  T1.confirmed is null) AND T1.TitleRU != '' AND T1.DescriptionRU is not null AND T1.DescriptionRU != ''
        ORDER BY OrderList, T1.Year DESC, random()
        LIMIT 1";

}

     
$sql_result = GetDatabaseRecords($sql);
///$sql_num=mysql_num_rows($sql_result);
  $sql_num=pg_num_rows($sql_result);

   

 //$sql_row=pg_fetch_array($result);
// get book data....
while($sql_row=pg_fetch_array($sql_result))
{
        //$id=$sql_row["bookID"];
        $id   = $sql_row["id"];
        $t_en = $sql_row["titleru"];
        $t_ru = htmlspecialchars($sql_row["titleru"]);
        $d_ru = $sql_row["descriptionru"];
        $d_en = $sql_row["descriptionru"];
        $barcode=$sql_row["barcode"];
        $publisherID=$sql_row["publisher"];
        $publisherName=$sql_row["publisher"];
        $author=$sql_row["author_id"];
        $year=$sql_row["year"];
        $source_id=$sql_row["source_id"];
        $ozon_id=$sql_row["ozon_id"];
	$priority=$sql_row["orderlist"];
	$confirmed=$sql_row["confirmed"];
	//$image_url=$sql_row["ImageID"] . '.jpg';
	$image_url=$sql_row["imageurl"] ;
	if ( strstr($image_url,".jpg") == false ) { $image_url .=".jpg"; }
//echo $image_url;
	$suggList=$sql_row["sugglist"];
}

if ($id=="")
{
   echo "<script>alert('No data to be validated')</script>";
   echo "<script>navigate('index.php')</script>";
   exit();
}


InsertDatabaseRecord("INSERT INTO BookLock (bookID, userID) VALUES ('".$id."', '".$userid."')");
// update record of who isviewing the book....
 InsertDatabaseRecord("INSERT INTO UserViews (userid, sessionID, bookID, DateViewed, Validated) VALUES ('".$userid."','".$_SESSION['session_id']."','".$id."',now()::timestamp,0)");
          $sql =  "SELECT max(userviewsid) as id from UserViews";
          $result_1 =GetDatabaseRecords($sql);
          $userviewsid_new_id =  pg_fetch_array($result_1);
          $userviewsid  = $userviewsid_new_id["id"]; 

//
// get Title , Description etc from the translations table
// for this book ID. based on the dateConfirmed date (latest) record.
//
$sql = "select * from BookTranslations where bookID = " . $id . " and dateLastEffective is null LIMIT 1";
// .. FILL IN BLANKS to get english translation text to display in 2nd box
$sql_result = GetDatabaseRecords($sql);

  $sql_num=pg_num_rows($sql_result);
//$sql_num=mysql_num_rows($sql_result);
// get book data....

 //$sql_row=pg_fetch_array($result);
if ( $sql_num != 0 ) {
//	while($sql_row=mysql_fetch_array($sql_result))
	while($sql_row=pg_fetch_array($sql_result))
	{
        	$t_en= htmlspecialchars($sql_row["TitleEN"]);
		$d_en =$sql_row["DescriptionEN"];
	}
}
else
{
	$t_en = "";
	$d_en = "";
}



// get the publisher(s) for this book.
$sql="SELECT PublisherID FROM BookPublishers WHERE bookID=".$id;
$result = GetDatabaseRecords($sql);
$selected_publishers=pg_fetch_array($result);
// get list of publishers.
$sql="SELECT PublisherID, PublisherNameRU, PublisherNameEN FROM PublisherList ORDER BY PublisherNameRU";
$result = GetDatabaseRecords($sql);


// loop round the publishers list -- make up a drop down list.
$options="";
while ($row=pg_fetch_array($result)) {
    $pub_id=$row[strtolower("Publisherid")];
    $pub_name=$row[strtolower("Publishernameru")];
    $pub_name_EN=$row[strtolower("PublisherNameEN")];
    $wt1 = $row["weight"];

    $sql = "SELECT PublisherID FROM BookPublishers WHERE bookID=".$id." AND PublisherID=".$pub_id;
    $sql_result = GetDatabaseRecords($sql);
    $sql_num=pg_num_rows($sql_result);

	if ($sql_num > 0)
	{
		$s_publishers.="<option value=\"$pub_id\">$pub_name ($pub_name_EN)</option>";
	} else {
    		$combo.="<option value=\"$pub_id\">$pub_name ($pub_name_EN)</option>";
	}
}

// get ISBN's for book.
$sql="SELECT DISTINCT ISBN FROM BookISBNs WHERE bookID = ".$id." ORDER BY ISBN";
$result = GetDatabaseRecords($sql);
$isbn="";
while ($row=pg_fetch_array($result)) {
    $isbn_text=$row[ strtolower("ISBN")];
    $isbn.=$isbn_text."\r";
}

// get author data for author id.
if ($author!="")
{
    $sql = "SELECT * FROM Authors WHERE Author_ID=".$author;
    $result = GetDatabaseRecords($sql);

    while($sql_row=pg_fetch_array($result))
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

$host = $_SERVER['HTTP_HOST'];
$image= "http://" . $host . "/product_images/" . $image_url;

$content_r = '';
if ($suggList == 1)
	{
	$content_r=	 "<p><strong>SUGGESTED LIST</strong></p>";
	} else {
	$content_r=	 "<p><strong>CATEGORY PRIORITY : ".$priority."</strong></p>";
	}
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

 <?= "PUBLISHER= ".$publishers ?>
<center>
<table border=0 cellspacing=30>

<tr>
<td valign=top>

<p><img src=<?= $image ?> width=200px></p>
<div>
<?=	$content_r ?>
</div>
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
			<tr><td valign=top>English Title</td><td align=left><input type=text value ="<?= $t_en ?>" name="TitleEN" size=50> Confirmed: <input type=checkbox <?= $confirmed==1 ? 'checked disabled' : '' ?>></td><td></td><td valign=top rowspan=3>ISBN</td><td rowspan=3><textarea name="ISBN" rows=5 cols=20><?= $isbn ?></textarea></td></tr>
<tr><td></td><td></td><td></td></tr>
			<tr><td valign=top>Publisher Name</td><td align=left><?= $publisherName ?></td><td></td></tr>
			<tr><td valign=top>Publisher Match


			</td><td colspan=5 align=left>

				<table border=0>
				    <tr><td width=550>
<div class="ui-widget">
	<label>Select Publisher: </label>
	<select id="combobox" name="publishers">
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
						<li id="tabHeader_1">Description<?= $confirmed==0 ? ' ***' : '' ?></li>
						<li id="tabHeader_3">Author<?= $a_confirmed==0 ? ' ***' : '' ?></li>
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
								<tr><td colspan=2 align=left> Confirmed: <input type=checkbox <?= $confirmed==1 ? 'checked disabled' : '' ?></td></tr>
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
								<tr><td>Weight:</td><td><textarea rows="1" cols="8" name="weight1" <?= $wt1==1 ? 'readonly' : '' ?> ><?= $wt1 ?></textarea></td></tr>
								<tr><td>Confirmed</td><td align=left><input type=hidden name="author_ID" value="<?= $author ?>"><input type=checkbox name="authorConfirmed" <?= $a_confirmed==1 ? 'checked disabled' : '' ?> ></td></tr>
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



$sql = "SELECT count(*) as noConfirmed FROM products WHERE Confirmed=1 ";
    $result = GetDatabaseRecords($sql);

    while($sql_row=pg_fetch_array($result))
    {
        $books_confirmed=$sql_row["noConfirmed"];
    }


$sql = "SELECT count(*) as noConfirmed FROM products WHERE Confirmed = 0 ";
    $result = GetDatabaseRecords($sql);

    while($sql_row=pg_fetch_array($result))
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
        FROM UserSessions
        INNER JOIN Books ON UserSessions.UserHistoryID = Books.SessionID
        WHERE userID = ".$userid." AND LoginDate > now()::timestamp AND Books.Confirmed = 1
        GROUP BY userHistoryID, userID
) AS TimeWorked
GROUP BY userID";
$result = GetDatabaseRecords($sql);

    while($sql_row=pg_fetch_array($result))
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
