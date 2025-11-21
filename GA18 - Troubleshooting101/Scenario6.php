<?php
$conn = mysqli_connect("localhost","root","","class_db");

//fix
$id = intval($_GET['id']);

$sql = "DELETE FROM students WHERE id = " . $_GET['id'];
mysqli_query($conn, $sql);
?>

<?php 
    /* Explaination:
        yung code is unsafe, because a user can inject harmful values,
        which a user can delete all rows / records. hence using intval() 
        to prevents injections like 0 or 1=1
    */
?>