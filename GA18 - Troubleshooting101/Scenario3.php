<?php
$conn = mysqli_connect("localhost","root","","class_db");
$age = $_GET['age'];

//$sql = "SELECT * FROM students WHERE age = $age";
$stmt = $conn->prepare("SELECT * FROM students WHERE age = ?");
$stmt->bind_param("i", $age);
$stmt->execute();
$res = mysqli_query($conn, $sql);
?>

<?php 
    /* Explaination.
        A direct sql injection na when a user enters 1 it returns all
        records. kaya using prepared statements stop the database from
        reading input as SQL code.
    */
?>