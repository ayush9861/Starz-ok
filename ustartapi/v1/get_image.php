<?php 
require_once '../include/DbConnect.php';
// Check connection
$db = new DbConnect();
$conn = $db->connect();

$id = $_GET['id']; 
$query = "SELECT epic_picture,epic_type FROM hs_hr_emp_picture WHERE emp_number = $id"; 
$result = mysqli_query($conn,$query); 
$photo = mysqli_fetch_array($result); 
header('Content-Type:'.$photo['epic_type']); 
echo $photo['epic_picture']; 
?>