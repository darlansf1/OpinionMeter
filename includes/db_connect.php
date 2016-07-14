<?php
include_once 'psl-config.php';   
$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE, PORT);

$query = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci" ;
$stmt = $mysqli->prepare($query);

if($stmt->execute()){
	$stmt->close();
}else{
	$stmt->close();
	dbError($mysqli);
}
?>