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
include("secrets7.php");

//My function to display tables
  function other_one($rows) {
        if(count($rows) > 0) {
             echo"<table border='1' cellspacing='1' cellpadding='4'>";
             echo"<tr>";

             foreach($rows[0] as $key =>$item) {
                  echo"<th>$key</th>";
             } //end heading
             echo"</tr>";

             foreach($rows as $row) {
                echo"<tr>";
                  foreach($row as $key => $item) {
                          echo"<td>$item</td>";
                  } //end inner foreach
                echo"</tr>";
             } //end outer foreach
             echo"</table>";
        } //end if
        else { echo"No data to display at this time"; }
  } //end function



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
    $dsn = "mysql:host=courses;dbname=z1986586";
    $pdo = new PDO($dsn, $username, $password);

    // Query for data
    $rs = $pdo->query("SELECT title FROM Song;");
    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);

    $rsu = $pdo->query("SELECT user_id, name FROM User;");
    $users = $rsu->fetchAll(PDO::FETCH_ASSOC);

    $rss = $pdo->query("SELECT song_id, title FROM Song;");
    $songs = $rss->fetchAll(PDO::FETCH_ASSOC);

    $rsa = $pdo->query("SELECT song_id, artist FROM Song;");
    $artists = $rsa->fetchAll(PDO::FETCH_ASSOC);

    $rsc = $pdo->query("SELECT contributor_id, name FROM Contributor;");
    $contributors = $rsc->fetchAll(PDO::FETCH_ASSOC);


    $showartist = $pdo->query("SELECT artist FROM Song;");
    $showartistresults = $showartist->fetchAll(PDO::FETCH_ASSOC);
//other_one($showartistresults);

//Stuff dani deleted lol

// For User
        if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['user'])) {
                $selectuser = $_POST['user'];
                $userqry = "SELECT user_id FROM User WHERE User.user_id = :selectuser";
                $userrsp = $pdo->prepare($userqry);
                $userrsp->execute(['selectuser' => $selectuser]);
                $userresults = $userrsp->fetchAll(PDO::FETCH_ASSOC);
        } //end  if


//For Song Title
        if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['song'])) {
                $selectsong = $_POST['song'];
                $songqry = "SELECT filename_id FROM KaraokeFiles WHERE song_id IN (
				SELECT song_id FROM Song WHERE Song.song_id = :selectsong)";
                $songrsp = $pdo->prepare($songqry);
                $songrsp->execute(['selectsong' => $selectsong]);
                $songresults = $songrsp->fetchAll(PDO::FETCH_ASSOC);
        } //end  if

//For Song Artist
        if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['artist'])) {
                $selectartist = $_POST['artist'];
                $artistqry = "SELECT filename_id FROM KaraokeFiles WHERE song_id IN (
				SELECT song_id FROM Song WHERE Song.song_id = :selectartist)";
                $artistrsp = $pdo->prepare($artistqry);
                $artistrsp->execute(['selectartist' => $selectartist]);
                $artistresults = $artistrsp->fetchAll(PDO::FETCH_ASSOC);
        } //end  if

//For Song Contributor Name
        if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['contributor'])) {
                $selectcontrib = $_POST['contributor'];
                $contribqry = "SELECT filename_id FROM KaraokeFiles WHERE song_id IN (
				SELECT DISTINCT song_id FROM Contributed
                                 WHERE contributor_id IN (SELECT contributor_id
                                FROM Contributor WHERE Contributor.contributor_id = :selectcontrib ))";
                $contribrsp = $pdo->prepare($contribqry);
                $contribrsp->execute(['selectcontrib' => $selectcontrib]);
                $contribresults = $contribrsp->fetchAll(PDO::FETCH_ASSOC);
        } //end  if


////NEED TO DO THIS LOSER --> INSERT OR WHAT?????
   if($_SERVER['REQUEST_METHOD'] == 'POST') {
	 $filename_id = $_POST['filename_id'] ?? null;
	 $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : null;
	//$selectuser = isset($_POST['selectuser']) ? $_POST['selectuser'] : null;

   if(!$filename_id) {
       die("No Song Was Selected. Please Try Again."); }

//Free Queue
   if (null == $amount && $_SERVER['REQUEST_METHOD'] == 'POST') {
   	echo"You Opted In For the Free Queue";
	$Ibrokeqry = "INSERT INTO NormalQueue (user_id, filename_id, time)
                        VALUES(:selectuser, :filename_id, CURRENT_TIMESTAMP)";
        $Ibrokers = $pdo->prepare($Ibrokeqry);
        $Ibrokers->execute(['selectuser' => $selectuser, 'filename_id' => $filename_id]);

        $Bdispqry = "SELECT * FROM NormalQueue";
                        $Bdisprs = $pdo->query($Bdispqry);
                        $Bdispresults = $Bdisprs->fetchAll(PDO::FETCH_ASSOC);
                        other_one($Bdispresults);
   } //end broke

//Money Queue
    else if (null != $amount && $_SERVER['REQUEST_METHOD'] == 'POST'){
    	echo"You Opted In For the Priority Queue";
  	$Ipayqry = "INSERT INTO PriorityQueue (user_id, filename_id, cost, time)
           		VALUES(:selectuser, :filename_id, :amount, CURRENT_TIMESTAMP)";
  	$Ipayrs = $pdo->prepare($Ipayqry);
  	$Ipayrs->execute(['selectuser' => $selectuser, 'filename_id' => $filename_id, 'amount' => $amount]);

/*	$dispqry = "SELECT * FROM PriorityQueue";
                        $disprs = $pdo->query($dispqry);
                        $dispresults = $disprs->fetchAll(PDO::FETCH_ASSOC);
			other_one($dispresults);
*/
	} //end $$
 } //end Loser


 } catch (PDOException $e) {
    echo "Connection to database failed: " . $e->getMessage();
 }

// Get the sort parameters from the URL (if any)
$sort_column = isset($_GET['sort_by']) ? $_GET['sort_by'] : null;
$sort_order = isset($_GET['order']) && $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';

?>

<!-- Users Dropdown -->
<h2>Who Are You?</h2>
<h3>User:</h3>
<form action="" method="POST">
    <label for="user">Select Your Name:</label>
    <select name="user" id="user" required>
        <option value="">-----Users-----</option>
        <?php
        foreach ($users as $user) {
            echo "<option value='{$user['user_id']}'>{$user['user_id']}: {$user['name']}</option>";
        }
        ?>
    </select>


 <!-- Money Option Dropdown -->
 <br/><br/>
 <h2>Pay Money To Have Your Song Played Earlier!</h2>
 <h5>**The Higher The Amount, The Better For You (scroll down)**</h5>



    <h3>~~~~~~~~~~~~~~~~~~~~~~~~~~~~</h3>
    <h2>Options For Song Selection:</h2>
    <h5>*Please Scroll Further Down To Submit Your Choice*</h5>

    <!-- Songs Title Table -->

    <div style="background-color: #f2f2f2; display: inline-block; margin-left: 45px; margin-right: 45px; vertical-align: top;">
        <?php draw_table($songs, $sort_column, $sort_order); ?>
    </div>

    <!-- Songs Artist Table -->
    <div style="background-color: #f2f2f2; display: inline-block; margin-left: 45px; margin-right: 45px; vertical-align: top;">
        <?php draw_table($showartistresults, $sort_column, $sort_order); ?>
    </div>

    <!-- Songs Contributor Table -->
    <div style="background-color: #f2f2f2; display: inline-block; margin-left: 45px; margin-right: 45px; vertical-align: top;">
        <?php draw_table($contributors, $sort_column, $sort_order); ?>
    </div>


    <!-- Songs Dropdown -->
    <h3>By Title:</h3>
    <label for="song">Select a Song:</label>
    <select name="song" id="song" onchange="updateHiddenInput('filename_id', this.value)">
        <option value="">-----Titles-----</option>
        <?php
        foreach ($songs as $song) {
            echo "<option value='{$song['song_id']}'>{$song['song_id']}: {$song['title']}</option>";
        }
        ?>
    </select>
    <input type="hidden" id="hidden_filename_id" name="filename_id">

    <!-- Artist Dropdown -->
    <br/><br/>
    <h3>By Artist:</h3>
    <label for="artist">Select an Artist:</label>
    <select name="artist" id="artist" onchange="updateHiddenInput('filename_id', this.value)">
        <option value="">-----Artists-----</option>
        <?php
        foreach ($artists as $artist) {
            echo "<option value='{$artist['song_id']}'>{$artist['song_id']}: {$artist['artist']}</option>";
        }
        ?>
    </select>


    <!-- Contributor Dropdown -->
    <br/><br/>
    <h3>By Contributor:</h3>
    <label for="contributor">Select A Contributor:</label>
    <select name="contributor" id="contributor" onchange="updateHiddenInput('filename_id', this.value)">
        <option value="">-----Contributors-----</option>
        <?php
        foreach ($contributors as $contributor) {
            echo "<option value='{$contributor['contributor_id']}'>{$contributor['contributor_id']}: {$contributor['name']}</option>";
        }
        ?>
    </select>


 <h3>Amount:</h3>
 <!-- Money -->
  <label for="amount">Amount: (Exact Change) $</label>
  <input type="text" name="amount" id="amount" step="1" pattern="^[0-9]+$"
        title="Accepts Positive Integers Only" placeholder="Enter Amount (If Paying)">

	 <script>
	function updateHiddenInput(input, value) {
		const hiddenField = document.getElementById(`hidden_${input}`);
		hiddenField.value = value;
		console.log(`Updated ${input}:`, hiddenField.value);
	}
	</script>
    <input type="submit" name="submit" value="Submit"/>
</form>


<?php

//check all empty at the very beginingempty($artistresults)) {
/*
      if (!empty($userresults)) {
		echo"<h4>Selected User </h4>";
        	other_one($userresults);
      }
*/
    if (empty($userresults) && $_SERVER['REQUEST_METHOD'] == 'POST') {
         	echo"No User Was Selected";
         	echo"<br/>";
    }

    if (empty($songresults) && empty($artistresults) && empty($contribresults) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        	echo"<h4>**No Song Was Selected**</h4>";
        	exit("**Bye**");
    }
/*
    if (!empty($songresults)) { //For the Song_ID of Song Display
                echo"<h4>Song Selected:</h4>";
                other_one($songresults);
    } if (empty($songresults) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        	echo"No Song Selected By Title";
        	echo"<br/>";
      }

    if (!empty($artistresults)) {
                echo"<h4>Artist Selected:</h4>";
                other_one($artistresults);
    } if (empty($artistresults) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        echo"No Song Selected By Artist";
        echo"<br/>";}

    if (!empty($contribresults)) {
                echo"<h4>Contributor Selected:</h4>";
                other_one($contribresults);
    } if (empty($contribresults) && $_SERVER['REQUEST_METHOD'] == 'POST') {
                echo "No Song Selected By Contributor";
                echo"<br/>";}
*/
?>

</body></html>

