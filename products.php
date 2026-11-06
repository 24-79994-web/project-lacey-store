<?php
require 'config.php';
$action = $_GET['action'] ?? 'get_all';

if ($action == 'get_all') {
    $res = $conn->query("SELECT * FROM products ORDER BY name");
    $data = []; while ($r = $res->fetch_assoc()) $data[] = $r;
    echo json_encode($data);
} 
elseif ($action == 'scan') {
    $code = $_GET['code'] ?? '';
    $stmt = $conn->prepare("SELECT * FROM products WHERE barcode=?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    echo json_encode($r ? ["found"=>true, "product"=>$r] : ["found"=>false]);
}
elseif ($action == 'add') {
    $data = json_decode(file_get_contents("php://input"), true);
    $bc = empty($data['barcode']) ? null : $data['barcode'];
    $img = empty($data['image_url']) ? null : $data['image_url'];
    
    $stmt = $conn->prepare("INSERT INTO products (barcode, name, category, price, stock, reorder_level, image_url) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssdiss", $bc, $data['name'], $data['category'], $data['price'], $data['stock'], $data['reorder'], $img);
    
    if ($stmt->execute()) {
        if ($bc === null) {
            $newId = $stmt->insert_id;
            $conn->query("UPDATE products SET barcode='LCY".sprintf("%08d", $newId)."' WHERE id=$newId");
        }
        echo json_encode(["success" => true]);
    } else echo json_encode(["success" => false]);
}
elseif ($action == 'delete') {
    $id = $_GET['id'] ?? 0;
    $conn->query("DELETE FROM products WHERE id=$id");
    echo json_encode(["success" => true]);
}
elseif ($action == 'update_stock') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id']; $stock = $data['stock'];
    $conn->query("UPDATE products SET stock=$stock WHERE id=$id");
    echo json_encode(["success" => true]);
}
?>  