<?php

//$page = $_GET['page'];
$page = intval($_GET['page']); //correction

$limit = 5;
$offset = $page * $limit;
$sql = "SELECT * FROM students LIMIT $offset, $limit";

//Explanation: using intval() + limits prevent huge numbers from crashing the DB. 
?>