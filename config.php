<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
$host = "localhost"; $user = "root"; $pass = ""; $dbname = "laceystore";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die(json_encode(["error" => "DB failed"])); }
?>