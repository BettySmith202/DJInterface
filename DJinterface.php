<html>
<?php
    include ('settingUserAndPass.php');
    
    try{
        $dsn = "mysql:host=courses;dbname=z1989080";
        $pdo = new PDO($dsn,$username,$password);
    }
    catch(PDOexception $e){
        echo "connection failed: " . $e->getMessage();
    }
    echo "Table of the Normal Queue<br/><br/>";

    echo "<tr>";
    echo "<th>user_id</th>";
    echo "<th>filename_id</th>";
    echo "<th>time</th>";
    echo "<th>cost</th>";
    echo "</tr>";
?>
</html>
