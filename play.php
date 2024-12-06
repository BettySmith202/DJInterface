<html>
<head>
<title>Karaoke Video</title>
<style>
body
{
background-image: url('https://images.unsplash.com/photo-1617801003287-1a71d7792fdc?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8d2hpdGUlMjB0ZXh0dXJlfGVufDB8fDB8fHww');
background-repeat: no-repeat;
background-attachment: fixed;
background-size: 100% 100%;
}
table
{
width: 100%;
border-collapse: collapse;
}
th
{
cursor: pointer;
padding: 10px;
background-color: #f2f2f2;
}
th:hover
{
background-color: #ddd;
}
td
{
padding: 10px;
text-align: center;
}
</style>
</head>
<center>
<body>

<?php
try
{
include('secrets.php');
$dsn = "mysql:host=courses;dbname=z1988019";
$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$filename_id = isset($_GET['filename_id']) ? $_GET['filename_id'] : null;

$query = "SELECT video_embedded_code, filename_id FROM KaraokeFiles WHERE filename_id = :filename_id";
$rs = $pdo->prepare($query);
$rs->bindParam(':filename_id', $filename_id, PDO::PARAM_INT);
$rs->execute();
while($row = $rs->fetch())
{
echo $row['video_embedded_code'];
echo '<br></div>';
echo '<a href="DJInterface.php">Go Back</a>';
}
}
catch (PDOException $e)
{
echo "Error: " . $e->getMessage();
}
?>

</body>
</center>
</html>
