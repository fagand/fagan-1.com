<?php

$servername = "localhost:3306";
$username   = "fagancom_wedding";
$password   = "weddingp@ss";
$dbname     = "fagancom_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database successfully <br />";
}
catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

//try {
//    
//    $query = $conn->prepare("INSERT INTO survey (transportype, car, bus, train, bike, walk, yesNo) VALUES (?,?,?,?,?,?,?)");
//    
//    $query->bindParam(1, $transportype);
//    $query->bindParam(2, $car);
//    $query->bindParam(3, $bus);
//    $query->bindParam(4, $train);
//    $query->bindParam(5, $bike);
//    $query->bindParam(6, $walk);
//    $query->bindParam(7, $yesNo);
//    
//    $transportype = $_POST['select'];
//    
//    if ($_POST['car'] == 1)
//        $car = $_POST['car'];
//    else
//        $car = 0;
//    
//    if ($_POST['bus'] == 1)
//        $bus = $_POST['bus'];
//    else
//        $bus = 0;
//    
//    if ($_POST['train'] == 1)
//        $train = $_POST['train'];
//    else
//        $train = 0;
//    
//    if ($_POST['bike'] == 1)
//        $bike = $_POST['bike'];
//    else
//        $bike = 0;
//    
//    if ($_POST['walk'] == 1)
//        $walk = $_POST['walk'];
//    else
//        $walk = 0;
//    
//    $yesNo=$_POST['yesNo'];
//    
//    if ($_POST['yesNo'] == null) {
//        echo '<script language="javascript">';
//        echo 'alert("Second message message")';
//        echo '</script>';
//        echo 'going back';
//        return;
//    } else
//        $query->execute();
//}
//catch (PDOException $e) {
//    echo "Error in binding" . $e->getMessage();
//}

$conn = null;
?>
<p><?php 
    echo 'You have entered <br> Primary Transport: '.$_POST['select'];
    
    echo '<br>Other forms of tranpsort: '; 
    if ($_POST['car'] == 1)
        echo '<br> car = '.$_POST['car'];
    
    if ($_POST['bus'] == 1)
        echo '<br> bus = '.$_POST['bus'];
    
    if ($_POST['train'] == 1)
        echo '<br> train = '.$_POST['train'];
    
    if ($_POST['bike'] == 1)
        echo '<br> bike = '.$_POST['bike'];
    
    if ($_POST['walk'] == 1)
        echo '<br> walk = '.$_POST['walk'];
    echo '<br>Do you want contacted: '.$_POST['yesNo'];
?>
</p>
<form method="get" action="/gettinghere.php">
    <button type="submit">Click to return to survey page</button>
</form>
