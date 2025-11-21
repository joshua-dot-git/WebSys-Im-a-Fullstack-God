<?php
//$id = $_GET['id'];
$id = (int)$_GET['id']; //correction
$sql = "SELECT * FROM students WHERE id = $id";

/* IDs are numbers; plus removing qoutes on id ensures proper lookup. */
?>

