<?php
/* TODO REMOVE THIS COMMENT EVENTUALLY but no th e code
if (!defined('SECURE_ACCESS')) {
    die("Direct Access Not Allowed");
}
    */

$host = "localhost";
$user = "root";
$password = "";
$database = "luckynest_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>