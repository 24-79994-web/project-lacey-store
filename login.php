<?php
require 'config.php';
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = hash('sha256', $data['password'] ?? '');

$stmt = $conn->prepare("SELECT id, username, full_name, role FROM users WHERE username=? AND password=?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $_SESSION['user'] = $row;
    echo json_encode(["success" => true, "user" => $row]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
}
?>