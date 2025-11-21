<?php
$conn = mysqli_connect("localhost","root","","class_db");

$first = $_POST['fname'];
$last = $_POST['lname'];

//fix
if (empty($first) || empty($last)) {
    echo "Please fill out all fields.";
    exit;
}

$sql = "INSERT INTO students (first_name,last_name) VALUES ('$first', '$last')";
mysqli_query($conn, $sql);
echo "Inserted!";
?>

<?php 
    /* Explanation:
        for checking if fields are empty before inserting to avoid black rows.

        sql erors happen or empty rows get inserted pag nag leave ng blank fields ang user.
    */
?>