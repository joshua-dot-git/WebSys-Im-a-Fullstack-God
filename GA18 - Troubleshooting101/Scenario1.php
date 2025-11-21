<?php 
$conn = mysqli_connect("localhost", "root", "", "class_db"); 
//$id = $_POST['id'];
$id = $_GET ['id'];
//$sql = "SELECT * FROM students WHERE id = $id";
$sql = "SELECT * FROM students WHERE student_id = $id"; 
$res = mysqli_query($conn, $sql); 
$r = mysqli_fetch_assoc($res); 
echo $r['first_name']; 

/* Explanation: 
    Needs to use $_GET instead of $_POST in line 3
    because we want to get something from the db.
    
    in line 5, id is wrong because it doesn't exist in the db,
    it should be student_id because that is the primary key
    
*/
?>