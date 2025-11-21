<?php
$conn = mysqli_connect("localhost","root","","class_db");
$id = $_POST['id'];
$email = $_POST['email'];
$sql = "UPDATE students SET email=$email WHERE id=$id";
$res = mysqli_query($conn, $sql);
//echo "updated!";

//fix
if ($res) {
    echo "Updated!";
} else {
    echo "Update failed" . mysqli_error($conn);
}
?>

<?php 
    /* Explaination:
        The error is that when update fails, the script sill prints updated!
        but it misleading and incorrect. so adding if para the script only 
        prints "Updated" if query actually succeeded.
    */
?>
