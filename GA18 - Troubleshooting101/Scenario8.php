<?php
$conn = mysqli_connect("localhost","root","","class_db");
$res = mysqli_query($conn,"SELECT * FROM students");

//missing lopp
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['email'];
}

/* incorrect.

$row = mysqli_fetch_assoc($res);
echo $row['email']; // prints first only

*/
?>

<?php 
    /*
        Explanation:
        using while loop prints every row, hindi lng yung
        first one.
    */
?>

