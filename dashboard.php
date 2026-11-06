<?php
require 'config.php';

// Basic Stats
$p = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$o = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$r = $conn->query("SELECT IFNULL(SUM(total),0) s FROM orders WHERE status='completed'")->fetch_assoc()['s'];

// Best Selling Products (Top 5)
$topProducts = [];
$res = $conn->query("
    SELECT p.name, SUM(oi.quantity) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.status='completed' 
    GROUP BY p.id 
    ORDER BY total_sold DESC 
    LIMIT 5
");
while ($row = $res->fetch_assoc()) {
    $topProducts[] = $row;
}

// Revenue Trends (Last 7 Days)
$dailyRevenue = [];
$res = $conn->query("
    SELECT DATE(order_date) as date, SUM(total) as total 
    FROM orders 
    WHERE status='completed' AND order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
    GROUP BY DATE(order_date) 
    ORDER BY date ASC
");
while ($row = $res->fetch_assoc()) {
    $dailyRevenue[] = $row;
}

// Monthly Revenue (Last 6 Months)
$monthlyRevenue = [];
$res = $conn->query("
    SELECT DATE_FORMAT(order_date, '%b %Y') as month, SUM(total) as total 
    FROM orders 
    WHERE status='completed' AND order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    GROUP BY DATE_FORMAT(order_date, '%b %Y') 
    ORDER BY MIN(order_date) ASC
");
while ($row = $res->fetch_assoc()) {
    $monthlyRevenue[] = $row;
}

echo json_encode([
    "products" => $p, 
    "orders" => $o, 
    "revenue" => $r,
    "top_products" => $topProducts,
    "daily_revenue" => $dailyRevenue,
    "monthly_revenue" => $monthlyRevenue
]);
?>