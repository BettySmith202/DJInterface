<?php
try
{
include('secrets.php');
$dsn = "mysql:host=courses;dbname=z1988019";
$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['filename_id']))
{
$filename_id = $_GET['filename_id'];
$filename_id = intval($filename_id);

$rs = $pdo->prepare("DELETE FROM PriorityQueue WHERE filename_id = :filename_id");
$rs->bindParam(':filename_id', $filename_id, PDO::PARAM_INT);
$rs->execute();

if ($rs->rowCount() == 0)
{
$rs = $pdo->prepare("DELETE FROM NormalQueue WHERE filename_id = :filename_id");
$rs->bindParam(':filename_id', $filename_id, PDO::PARAM_INT);
$rs->execute();
}

header("Location: DJInterface.php?message=Queue+entry+deleted+successfully");
exit();
}
}
catch (PDOException $e)
{
echo "Error: " . $e->getMessage();
}
?>
