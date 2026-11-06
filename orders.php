<?php
require 'config.php';
$action = $_GET['action'] ?? '';

if ($action == 'create') {
    $data = json_decode(file_get_contents("php://input"), true);
    $u = $_SESSION['user']['id'] ?? 1; // Fallback kung wala session
    
    $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, customer_contact, address, total) VALUES (?,?,?,?,?)");
    $stmt->bind_param("isssd", $u, $data['customer_name'], $data['contact'], $data['address'], $data['total']);
    $stmt->execute();
    $orderId = $stmt->insert_id;

    foreach ($data['items'] as $item) {
        $conn->query("INSERT INTO order_items (order_id, product_id, barcode, quantity, price) VALUES ($orderId, {$item['productId']}, '{$item['barcode']}', {$item['qty']}, {$item['price']})");
        $conn->query("UPDATE products SET stock = stock - {$item['qty']} WHERE id = {$item['productId']}");
    }
    echo json_encode(["success" => true, "order_id" => $orderId]);
}
elseif ($action == 'get_all') {
    $res = $conn->query("SELECT * FROM orders ORDER BY id DESC");
    $data = []; while ($r = $res->fetch_assoc()) $data[] = $r;
    echo json_encode($data);
}
elseif ($action == 'update_status') {
    $data = json_decode(file_get_contents("php://input"), true);
    $conn->query("UPDATE orders SET status='{$data['status']}' WHERE id={$data['id']}");
    echo json_encode(["success" => true]);
}
?>