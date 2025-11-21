<?php
$conn = mysqli_connect("localhost","root","","class_db");
//$email = $_POST['emial'];
$email = $_POST['email']; //correction
$sql = "SELECT * FROM students WHERE email='$email'";
$res = mysqli_query($conn, $sql);
?>

<?php 
    /* Explaination:
        A syntax error specifically mispelling of the post email on line 3
    */
?>