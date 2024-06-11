<?php
$servername = "185.2.168.28";
$username = "yesinter_raju";
$password = "Raju@2020";
$dbname = "yesinter_bidding";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "INSERT INTO game_result (gain,loss)
VALUES (0,0)";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
