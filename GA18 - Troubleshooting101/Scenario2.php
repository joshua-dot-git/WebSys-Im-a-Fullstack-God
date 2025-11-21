<?php 
$conn = mysqli_connect("localhost","root","","class_db"); 
$fname = $_POST['fname']; 
//$sql = "SELECT * FROM students WHERE first_name = $fname";
$sql = "SELECT * FROM students WHERE first_name = '$fname'";
$res = mysqli_query($conn, $sql); 
?> 

<html>
    <form action="Scenario2.php" method="post">
        <label for="">First Name:</label>
        <input type="text" name="fname">
        <input type="submit" value="Search">
    </form>
</html>

<?php
    /*  
        I've added the missing qoutes in line 4 on &fname
        because sql thinks ana is a column name; it's non-existent

        I've also added a form input for searching because it's part
        of the goal if i understood correctly xd.
    */
?>