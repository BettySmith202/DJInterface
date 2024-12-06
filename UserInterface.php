<!DOCTYPE html>
<html>
<head>
    <title>Karaoke Songs</title>
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1617801003287-1a71d7792fdc?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8d2hpdGUlMjB0ZXh0dXJlfGVufDB8fDB8fHww');
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: 100% 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            cursor: pointer;
            padding: 10px;
            background-color: #f2f2f2;
        }

        th:hover {
            background-color: #ddd;
        }

        td {
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

<?php
echo "<h1>Karaoke Night!</h1>";
include("settingUserAndPass.php");
function draw_table($rows, $sort_column = null, $sort_order = 'ASC') {
    // Sort the rows if a sort column is specified
    if ($sort_column !== null) {
        usort($rows, function ($a, $b) use ($sort_column, $sort_order) {
            $valueA = $a[$sort_column];
            $valueB = $b[$sort_column];

            if (is_numeric($valueA)) $valueA = (float) $valueA;
            if (is_numeric($valueB)) $valueB = (float) $valueB;

            if ($valueA == $valueB) return 0;
            if ($sort_order == 'ASC') {
                return $valueA < $valueB ? -1 : 1;
            } else {
                return $valueA > $valueB ? -1 : 1;
            }
        });
    }
    
    
    if (count($rows) > 0) {
        echo "<table>";
        echo "<tr>";

        // Table headers
        foreach ($rows[0] as $key => $item) {
            echo "<th><a href='?sort_by=$key&order=" . ($sort_order == 'ASC' ? 'DESC' : 'ASC') . "'>$key</a></th>";
        }
        echo "</tr>";

        // Table rows
        foreach ($rows as $row) {
            echo "<tr>";
            foreach ($row as $key => $item) {
                echo "<td>$item</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No data to display at this time.";
    }
    
 }

try {
    $dsn = "mysql:host=courses;dbname=z1989080";
    $pdo = new PDO($dsn, $username, $password);

    // Query for data
    $rss = $pdo->query("SELECT song_id, title, artist FROM Song;");
    $songs_and_artists = $rss->fetchAll(PDO::FETCH_ASSOC);

    $rsu = $pdo->query("SELECT user_id, name FROM User;");
    $users = $rsu->fetchAll(PDO::FETCH_ASSOC);

 
 //To get the USER_ID
  if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['user'])) {
                $selectuser = $_POST['user'];

                $userqry = "SELECT user_id FROM User WHERE User.user_id = :selectuser";
                $userrsp = $pdo->prepare($userqry);
                $userrsp->execute(['selectuser' => $selectuser]);
                $userresults = $userrsp->fetch(PDO::FETCH_ASSOC);
	//For Safety
	 if (empty($userresults)) { echo"No User Was Selected"; }
   } //end  if

 //To NOT display the data TWICE after submission
 $dispTwice = false;

//To Get the song textbox
  if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['song_search'])) {
	$song_s = trim($_POST['song_search']);

	//Mark Visited
        $dispTwice=true;
        //You tried to Search A Song
	if(!empty($song_s)) {
	   $song_sqry = "SELECT Song.song_id, Song.title, Song.artist, KaraokeFiles.version FROM Song
			LEFT JOIN KaraokeFiles ON Song.song_id = KaraokeFiles.song_id
			WHERE Song.title LIKE :song_s
			OR Song.artist LIKE :song_s";

	   $song_srs = $pdo->prepare($song_sqry);
	   $song_srs->execute(['song_s' => '%' . $song_s . '%']);
	   $matching_songs = $song_srs->fetchAll(PDO::FETCH_ASSOC);

	//Search Did NOT Match Any Songs
		if (empty($matching_songs)) {
			echo"<h3>Your Search Did Not Generate Any Matches, Please Look Through The Available Songs</h3>";
		        $song_sqry = "SELECT * FROM Song
                       			LEFT JOIN Contributed ON Song.song_id = Contributed.song_id
                        		LEFT JOIN Contributor ON Contributed.contributor_id = Contributor.contributor_id
					LEFT JOIN KaraokeFiles ON Song.song_id = KaraokeFiles.song_id";
            		$song_srs = $pdo->query($song_sqry);
            		$all_available_s = $song_srs->fetchAll(PDO::FETCH_ASSOC);
		} //end if match

	} //end if
	else if (empty($song_s)) {
		echo"<h3>You Did Not Submit Any Songs. Please Take A Look At the Available Options</h3>";
	        $song_sqry = "SELECT * FROM Song
                	      LEFT JOIN Contributed ON Song.song_id = Contributed.song_id
                              LEFT JOIN Contributor ON Contributed.contributor_id = Contributor.contributor_id
			      LEFT JOIN KaraokeFiles ON Song.song_id = KaraokeFiles.song_id";
                $song_srs = $pdo->query($song_sqry);
	        $all_available_s = $song_srs->fetchAll(PDO::FETCH_ASSOC);
	} //end else empty
  } //end  song_search if
/*
//To Get the artist textbox
 if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['artist_search'])) {
    $song_s = trim($_POST['artist_search']);

    // Mark Visited
    $dispTwice = true;
    // You tried to Search an artist
    if (!empty($song_s)) {
        $song_sqry = "SELECT Song.song_id, Song.title, Song.artist, KaraokeFiles.version FROM Song
                      LEFT JOIN KaraokeFiles ON Song.song_id = KaraokeFiles.song_id
                      WHERE Song.artist LIKE :song_s";

        $song_srs = $pdo->prepare($song_sqry);
        $song_srs->execute(['song_s' => '%' . $song_s . '%']);
        $matching_songs = $song_srs->fetchAll(PDO::FETCH_ASSOC);

        // Search Did NOT Match Any Songs
        if (empty($matching_songs)) {
            echo "<h3>Your Search Did Not Generate Any Matches, Please Look Through The Available Songs</h3>";
            $song_sqry = "SELECT * FROM Song
                          LEFT JOIN Contributed ON Song.song_id = Contributed.song_id
                          LEFT JOIN Contributor ON Contributed.contributor_id = Contributor.contributor_id
                          LEFT JOIN KaraokeFiles ON Song.song_id = KaraokeFiles.song_id";
            $song_srs = $pdo->query($song_sqry);
            $all_available_s = $song_srs->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        echo "<h3>You Did Not Submit Any Songs. Please Take A Look At the Available Options</h3>";
        $song_sqry = "SELECT * FROM Song
                      LEFT JOIN Contributed ON Song.song_id = Contributed.song_id
                      LEFT JOIN Contributor ON Contributed.contributor_id = Contributor.contributor_id
                      LEFT JOIN KaraokeFiles ON Song.song_id = KaraokeFiles.song_id";
        $song_srs = $pdo->query($song_sqry);
        $all_available_s = $song_srs->fetchAll(PDO::FETCH_ASSOC);
    }
}
*/

   //Added for safety
   $check = false;

 //TO match song_id with filename_id
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	//If There Is Match
	if (isset($_POST['s_song_id'])) {
        	$selectedSongId = $_POST['s_song_id'];

		$fileqry = "SELECT filename_id FROM KaraokeFiles WHERE song_id = :selectedSongId";
		$filersp = $pdo->prepare($fileqry);
		$filersp->execute(['selectedSongId' => $selectedSongId]);
		$fileresults = $filersp->fetch(PDO::FETCH_ASSOC);

	//Got Them All
	  if ($fileresults && $userresults && $selectedSongId) {
	 	  $check = true;
		  $userID = $userresults['user_id'];
		  $filenameID = $fileresults['filename_id'];
	  } else { die("No FileName Match For Your Selected Song"); } //Should Not Run

        } else { echo "<h3>Please Take A Look At The Available Songs And Take Your Pick</h3>"; }
   } //end big if


//The amount so Insert into Queue
 if ($_SERVER['REQUEST_METHOD'] === 'POST' && $check == true) {

	$amount = isset($_POST['amount']) ? (int)$_POST['amount'] : null;
    echo $amount;
        //Free queue
	if (null == $amount && $_SERVER['REQUEST_METHOD'] == 'POST') {
   		echo"<br/>You Opted In For the Free Queue. Thank You!";

	   	if(!$selectuser || !$filenameID) {
		    die("User or Song was not Selected. Please Refresh and try Again"); } //end if check

	  try {	//Normal Queue
		$Ibrokeqry = "INSERT INTO NormalQueue (user_id, filename_id, time)
                	        VALUES(:selectuser, :filename_id, CURRENT_TIMESTAMP)";
        	$Ibrokers = $pdo->prepare($Ibrokeqry);
        	$Ibrokers->execute(['selectuser' => $selectuser, 'filename_id' => $filenameID]);

	   } catch (PDOexception $e) {
		die("Error: Unable to Insert into Normal Queue: " . $e->getMessage());
	   } //end catch

	//Pay Queue
        } else if (null != $amount && $_SERVER['REQUEST_METHOD'] == 'POST'){
		echo"<br/>You Opted In For the Priority Queue. Thank You For Your Purchase!";

		//Safety
	       if(!$selectuser || !$filenameID) {
                    die("User or Song was not Selected. Please Refresh and try Again"); } //end if check

	       try { //Priority Queue

	  	  $Ipayqry = "INSERT INTO PriorityQueue (user_id, filename_id, cost, time)
           		    VALUES(:selectuser, :filename_id, :amount, CURRENT_TIMESTAMP)";
  		  $Ipayrs = $pdo->prepare($Ipayqry);
  		  $Ipayrs->execute(['selectuser' => $selectuser, 'filename_id' => $filenameID,
				  'amount' => $amount]);

	       } catch (PDOexception $e) {
               		die("Error: Unable to Insert into Normal Queue: " . $e->getMessage());
               } //end catch
	} //end pay else

   } //end Insert


 } catch (PDOException $e) {
    echo "Connection to database failed: " . $e->getMessage();
 }


// Get the sort parameters from the URL (if any)
$sort_column = isset($_GET['sort_by']) ? $_GET['sort_by'] : null;
$sort_order = isset($_GET['order']) && $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';

?>

<!-- Users Dropdown -->
 <h3>Sign Up After Song Search!</h3>

 <!-- Money Option Dropdown -->
 <h3>Pay Money To Have Your Song Played Earlier!</h3>
 <h5>&nbsp&nbsp&nbsp&nbsp**Option Available After Song Search**</h5>

    <form action="" method="POST">
    <h3>~~~~~~~~~~~~~~~~~~~~~~~~~~~~</h3>
    <h2>Options For Song Selection:</h2>
    <h5>*Please Scroll Further Down To Submit Your Choice*</h5>

    <!-- Songs Title and Artist Tables -->
    <div style="background-color: #f2f2f2; display: inline-block; margin-left: 45px; margin-right: 45px; vertical-align: top;">
        <?php draw_table($songs_and_artists, $sort_column, $sort_order); ?>
    </div>


    <!-- TextBox -->

    <h3>By Title:</h3>
    <label for="song_search">Select a Song:</label>
    <input type="text" name="song_search" id="song_search" placeholder="Enter A Song Title"
	pattern="^[A-Za-z\s]+$" title="Must Contain Letters Only"/><br/>
    <label for="song_search">Select an artist:</label>
    <input type="text" name="artist_search" value="Enter an Artist" placeholder="Enter an Artist"
	pattern="^[A-Za-z\s]+$" title="Must Contain Letters Only"/><br/>
    <label for="song_search">Select a contributor:</label>
    <input type="text" name="contributor_search" value="Enter a Contributor" placeholder="Enter a Contributor"
	pattern="^[A-Za-z\s]+$" title="Must Contain Letters Only"/><br/>



   <input type="submit" name="Search" value="Search"/>
   </form>

				

 <!-- ANOTHER FORM THING -->
<?php
    include('settingUserAndPass.php');
    try{
        $dsn = "mysql:host=courses;dbname=z1989080";
        $pdo = new PDO($dsn, $username, $password);

    }
    catch(PDOexception $e){
        echo "connection failed: " . $e->getMessage();
    }
    if ($_POST["artist_search"] != "Enter an Artist")
    {
        echo"<form action='' method='POST'>";
	echo"<br/>";
	echo"<table border=\"1\">";
	echo"<tr>";
	echo"<th>Select</th><th>Song ID</th><th>Title</th><th>Artist</th>";
    //<th>Contributor</th>
	     /*<th>Type</th>*/
    echo "<th>Version</th>";
	echo"</tr>";
        
        $sql = "select Song.song_id, title, artist,version from Song, KaraokeFiles where Song.song_id=KaraokeFiles.song_id && Song.artist=? group by Song.song_id";
        $result=$pdo->prepare($sql);
        $success=$result->execute(array($_POST["artist_search"]));

        if($success)
        {
	    while ($i = $result->fetch()) 
        {
            echo"<tr>";
            echo "<td><input type='radio' name='s_song_id' value='${i['song_id']}' required></td>";
            echo"<td>${i['song_id']}</td>";
            echo"<td>${i['title']}</td>";
            echo"<td>${i['artist']}</td>";
            //echo"<td>{$i['name']}</td>";
            //echo"<td>{$i['type']}</td>";
            echo"<td>${i['version']}</td>";
            echo"</tr>";
            } //end while
        }
        else
        {
            echo "what?";
        }
	echo"</table><br/>";
    //echo $_POST['s_song_id'];
	//User
	echo"<h2>Who Are You?</h2>";
	echo"<h3>User:</h3>";
    	echo"<label for=\"user\">Select Your Name:</label>";
	echo"<select name=\"user\" id=\"user\" required>";
        echo"<option value=\"\">-----Users-----</option>";
        foreach ($users as $user) {
            echo "<option value='{$user['user_id']}'>{$user['user_id']}: {$user['name']}</option>";
	}
	echo"</select>";

	// Money
	echo"<h3>Pay:&nbsp&nbsp(Priority Placed On Higher Bids)</h3>";
  	echo"<label for=\"amount\">Amount: (Exact Change) $ </label>";
	echo"<input type=\"text\" name=\"amount\" id=\"amount\" step=\"1\" pattern=\"^[0-9]+$\"
		title=\"Accepts Positive Integers Only\" placeholder=\"Enter Amount (If Paying)\">";
	echo"&nbsp&nbsp&nbsp";
    
//The Submit Button
	echo"<input type=\"submit\" name=\"SubmitSongSelection\" value=\"Submit Song Selection\"/>";
	echo"</form>";

    }
    else if ($_POST["contributor_search"] != "Enter a Contributor")
    {
        echo"<form action='' method='POST'>";
	echo"<br/>";
	echo"<table border=\"1\">";
	echo"<tr>";
	echo"<th>Select</th><th>Song ID</th><th>Title</th><th>Contributor</th><th>Type</th>";
    echo "<th>Version</th>";
	echo"</tr>";
        
        $sql = "select Song.song_id, title, artist,version,name,type from Song, KaraokeFiles,Contributor,Contributed where Song.song_id=KaraokeFiles.song_id && Contributed.song_id=Song.song_id && Contributor.contributor_id=Contributed.contributor_id && Contributor.name=?";
        $result=$pdo->prepare($sql);
        $success=$result->execute(array($_POST["contributor_search"]));

        if($success)
        {
	    while ($i = $result->fetch()) 
        {
            echo"<tr>";
            echo "<td><input type='radio' name='s_song_id' value='${i['song_id']}' required/></td>";
            echo"<td>${i['song_id']}</td>";
            echo"<td>${i['title']}</td>";
            //echo"<td>${i['artist']}</td>";
            echo"<td>{$i['name']}</td>";
            echo"<td>{$i['type']}</td>";
            echo"<td>${i['version']}</td>";
            echo"</tr>";
            } //end while
        }
        else
        {
            echo "what?";
        }
	echo"</table><br/>";

	//User
	echo"<h2>Who Are You?</h2>";
	echo"<h3>User:</h3>";
    	echo"<label for=\"user\">Select Your Name:</label>";
	echo"<select name=\"user\" id=\"user\" required>";
        echo"<option value=\"\">-----Users-----</option>";
        foreach ($users as $user) {
            echo "<option value='{$user['user_id']}'>{$user['user_id']}: {$user['name']}</option>";
	}
	echo"</select>";

	// Money
	echo"<h3>Pay:&nbsp&nbsp(Priority Placed On Higher Bids)</h3>";
  	echo"<label for=\"amount\">Amount: (Exact Change) $ </label>";
	echo"<input type=\"text\" name=\"amount\" id=\"amount\" step=\"1\" pattern=\"^[0-9]+$\"
		title=\"Accepts Positive Integers Only\" placeholder=\"Enter Amount (If Paying)\">";
	echo"&nbsp&nbsp&nbsp";

//The Submit Button
	echo"<input type=\"submit\" name=\"SubmitSongSelection\" value=\"Submit Song Selection\"/>";
	echo"</form>";

    }

 // **HERE I DISPLAY THE TABLES AND THE SORT THING SHOULD WORK I THINK** 
 else if (!empty($matching_songs) && $dispTwice ) {
	echo"<form action=\"\" method=\"POST\">";
	echo"<br/>";
	echo"<table border=\"1\">";
	echo"<tr>";
	echo"<th>Select</th><th>Song ID</th><th>Title</th><th>Artist</th>";
    //<th>Contributor</th>
	     /*<th>Type</th>*/
    echo "<th>Version</th>";
	echo"</tr>";

	  foreach ($matching_songs as $i) {
		echo"<tr><td>";
		echo"<input type=\"radio\" name=\"s_song_id\" value=\"{$i['song_id']}\" required>";
		echo"</td>";
		echo"<td>{$i['song_id']}</td>";
		echo"<td>{$i['title']}</td>";
		echo"<td>{$i['artist']}</td>";
		//echo"<td>{$i['name']}</td>";
		//echo"<td>{$i['type']}</td>";
		echo"<td>{$i['version']}</td>";
		echo"</tr>";
	  } //end each
	echo"</table><br/>";

	//User
	echo"<h2>Who Are You?</h2>";
	echo"<h3>User:</h3>";
    	echo"<label for=\"user\">Select Your Name:</label>";
	echo"<select name=\"user\" id=\"user\" required>";
        echo"<option value=\"\">-----Users-----</option>";
        foreach ($users as $user) {
            echo "<option value='{$user['user_id']}'>{$user['user_id']}: {$user['name']}</option>";
	}
	echo"</select>";

	// Money
	echo"<h3>Pay:&nbsp&nbsp(Priority Placed On Higher Bids)</h3>";
  	echo"<label for=\"amount\">Amount: (Exact Change) $ </label>";
	echo"<input type=\"text\" name=\"amount\" id=\"amount\" step=\"1\" pattern=\"^[0-9]+$\"
		title=\"Accepts Positive Integers Only\" placeholder=\"Enter Amount (If Paying)\">";
	echo"&nbsp&nbsp&nbsp";

//The Submit Button
	echo"<input type=\"submit\" name=\"SubmitSongSelection\" value=\"Submit Song Selection\"/>";
	echo"</form>";
 } //end if

 //If No matches were found or they submitted empty
 else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	echo"<form action=\"\" method=\"POST\">";

	$blank_search = $_POST['song_search'] ?? null;
	if ( ($blank_search == null || empty($matching_songs)) && $dispTwice) {
	echo"In the absence of a search. All Songs are displayed:";

	echo"<br/>";
        echo"<table border=\"1\">";
        echo"<tr>";
        echo"<th>Select</th><th>Song ID</th><th>Title</th><th>Artist</th><th>Contributor</th>
             <th>Type</th><th>Version</th>";

        echo"</tr>";
          foreach ($all_available_s as $i) {
                echo"<tr><td>";
                echo"<input type=\"radio\" name=\"s_song_id\" value=\"{$i['song_id']}\" required>";
                echo"</td>";
                echo"<td>{$i['song_id']}</td>";
                echo"<td>{$i['title']}</td>";
                echo"<td>{$i['artist']}</td>";
                echo"<td>{$i['name']}</td>";
                echo"<td>{$i['type']}</td>";
                echo"<td>{$i['version']}</td>";
                echo"</tr>";
          } //end each
        echo"</table><br/>";

   	//User
        echo"<h2>Who Are You?</h2>";
        echo"<h3>User:</h3>";
        echo"<label for=\"user\">Select Your Name:</label>";
        echo"<select name=\"user\" id=\"user\" required>";
        echo"<option value=\"\">-----Users-----</option>";
        foreach ($users as $user) {
            echo "<option value='{$user['user_id']}'>{$user['user_id']}: {$user['name']}</option>";
        }
        echo"</select>";

        // Money
        echo"<h3>Pay:&nbsp&nbsp(Priority Placed On Higher Bids)</h3>";
        echo"<label for=\"amount\">Amount: (Exact Change) $ </label>";
        echo"<input type=\"text\" name=\"amount\" id=\"amount\" step=\"1\" pattern=\"^[0-9]+$\"
                title=\"Accepts Positive Integers Only\" placeholder=\"Enter Amount (If Paying)\">";
        echo"&nbsp&nbsp$nbsp";

	//The Submit Button
        echo"<input type=\"submit\" name=\"SubmitSongSelection\" value=\"Submit Song Selection\"/>";
        echo"</form>";
      } //end null if
 } //end else
?>

</body></html>
