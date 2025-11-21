<?php
$newEmail = $_POST['email'];
$id = $_POST['id']; //correction

$sql = "UPDATE students SET email='$newEmail'";
mysqli_query($conn,$sql);

//Explanation: Adding WHERE prevents updating every row.
?>