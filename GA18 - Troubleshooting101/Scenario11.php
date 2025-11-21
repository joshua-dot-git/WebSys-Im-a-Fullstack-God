<!--
<form method="GET" action="save.php">
    <input name="email">
</form>

wront methodm, it shoud be POST in form
-->

<form method="POST" action="save.php">
    <input name="email">
</form>

<?php 
//$email = $_POST['email'];
$email = $_GET['email'];//use GET method instead of POST
?>