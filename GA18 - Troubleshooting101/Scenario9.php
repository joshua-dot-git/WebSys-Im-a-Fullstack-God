<?php
//$id = $_POST['id']; wrong method.
$id = $_GET['id'];
?>
<a href="view.php?id=3">View Student</a>

<?php 
    /*
        Explanation: wrong method used. it should be
        GET not POST
    */
?>