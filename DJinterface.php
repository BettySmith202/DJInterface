<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<h1>DJ Interface</h1>
</head>
<body>

<?php
try
{
include('settingUserAndPass.php');
$dsn = "mysql:host=courses;dbname=z1989080";
$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
echo "Connection to database failed: " . $e->getMessage();
}?>

<?php
function fetchQueues($pdo)
{
$rs = $pdo->prepare("SELECT name, pq.filename_id, pq.time, pq.cost, k.version, s.title, s.artist
			FROM PriorityQueue pq
			JOIN KaraokeFiles k ON pq.filename_id = k.filename_id
			JOIN Song s ON k.song_id = s.song_id
            JOIN User u ON pq.user_id = u.user_id
			ORDER BY pq.cost DESC, pq.time ASC");
$rs->execute();
$priorityQueue = $rs->fetchAll(PDO::FETCH_ASSOC);

$rs = $pdo->prepare("SELECT name, nq.filename_id, nq.time, k.version, k.filename_id, s.title, s.artist
			FROM NormalQueue nq
			JOIN KaraokeFiles k ON nq.filename_id = k.filename_id
			JOIN Song s ON k.song_id = s.song_id
            JOIN User u ON nq.user_id = u.user_id
			ORDER BY nq.time ASC");
$rs->execute();
$normalQueue = $rs->fetchAll(PDO::FETCH_ASSOC);
return ['priority' => $priorityQueue, 'normal' => $normalQueue];
}
?>

<?php
$queues = fetchQueues($pdo);
?>

<h2>Priority Queue</h2>
<table border='1'>
<thead>
<tr>
<th>User's Name</th>
<th>Song Title</th>
<th>Artist</th>
<th>Version</th>
<th>Filename ID</th>
<th>Time</th>
<th>Cost</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
foreach ($queues['priority'] as $entry)
{
echo "<tr><td>" . $entry['name'] . "</td><td>" . $entry['title'] . "</td><td>" . $entry['artist'] . "</td><td>" . $entry['version'] . "</td><td>" . $entry['filename_id'] . "</td><td>" . $entry['time'] . "</td><td>$" .$entry['cost'] . "</td>";
echo "<td>";
echo "<a href='process.php?filename_id=" . $entry['filename_id'] . "'>Play</a> |";
echo "<a href='delete.php?filename_id=" . $entry['filename_id'] . "'>Delete</a>";
echo "</td></tr>";
}
?>
</tbody>
</table>

<?php
$queues = fetchQueues($pdo);
?>

<h2>Normal Queue</h2>
<table border='1'>
<thead>
<tr>
<th>User's Name</th>
<th>Song Title</th>
<th>Artist</th>
<th>Version</th>
<th>Filename ID</th>
<th>Time</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
foreach ($queues['normal'] as $entry)
{
echo "<tr><td>" . $entry['name'] . "</td><td>" . $entry['title'] . "</td><td>" . $entry['artist'] . "</td><td>" . $entry['version'] . "</td><td>" . $entry['filename_id'] . "</td><td>" . $entry['time'] . "</td>";
echo "<td>";
echo "<a href='process.php?filename_id=" . $entry['filename_id'] . "'>Play</a> |";
echo "<a href='delete.php?filename_id=" . $entry['filename_id'] . "'>Delete</a>";
echo "</td></tr>";
}
?>
</tbody>
</table>

<?php
if (isset($_GET['filename_id']))
{
$filename_id = $_GET['filename_id'];
echo "Now playing.";
}
?>

<?php
if (isset($_GET['filename_id']))
{
$filename_id = $_GET['filename_id'];
$rs = $pdo->prepare("DELETE FROM PrioriyQueue WHERE filename_id = ?");
$rs->execute([$filename_id]);
$rs = $pdo->prepare("DELETE FROM NormalQueue WHERE filename_id = ?");
$rs->execute([$filename_id]);

echo "Deleted queue entry.";
}
?>

</body>
</html>
