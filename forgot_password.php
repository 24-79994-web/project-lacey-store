<?php
require 'config.php';

// Kunin ang data mula sa JavaScript
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$new_password = $data['new_password'] ?? '';

if (empty($username) || empty($new_password)) {
    echo json_encode(["success" => false, "message" => "Missing fields."]);
    exit;
}

// I-hash ang bagong password
$hashed_password = hash('sha256', $new_password);

// I-check kung existing ang username
$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // I-update ang password
    $update = $conn->prepare("UPDATE users SET password=? WHERE username=?");
    $update->bind_param("ss", $hashed_password, $username);
    
    if ($update->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Username not found."]);
}
?>