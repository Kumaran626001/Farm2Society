<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$farmer_id = $_SESSION['user_id'];
$month = isset($_GET['month']) ? intval($_GET['month']) : null; // 1-12
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Base query parts
$where_clause = "WHERE p.farmer_id = ? AND o.order_status = 'Delivered'";
$params = [$farmer_id];
$types = "i";

// ---------------------------------------------------------
// 1. Monthly Orders Analysis (Trend for the selected year)
// ---------------------------------------------------------
$monthly_orders_sql = "
    SELECT 
        MONTH(o.order_date) as month, 
        COUNT(DISTINCT o.order_id) as total_orders
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    $where_clause AND YEAR(o.order_date) = ?
    GROUP BY MONTH(o.order_date)
    ORDER BY month ASC
";

// Prepare parameters for monthly query (always filtered by year)
$monthly_stmt = $conn->prepare($monthly_orders_sql);
$monthly_stmt->bind_param("ii", $farmer_id, $year);
$monthly_stmt->execute();
$monthly_result = $monthly_stmt->get_result();

$monthly_data = array_fill(1, 12, 0); // Initialize all months to 0
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[$row['month']] = $row['total_orders'];
}
$monthly_stmt->close();


// ---------------------------------------------------------
// 2. Vegetable Sales & Summary (Filtered by Month & Year)
// ---------------------------------------------------------
// If month is selected, filter by it. Otherwise, summary for the whole year.
$filter_sql = " AND YEAR(o.order_date) = ?";
$filter_params = [$farmer_id, $year];
$filter_types = "ii";

if ($month) {
    $filter_sql .= " AND MONTH(o.order_date) = ?";
    $filter_params[] = $month;
    $filter_types .= "i";
}

// Data for Pie Chart & Best Seller
$veg_sales_sql = "
    SELECT 
        p.product_name, 
        SUM(oi.quantity) as total_quantity,
        SUM(oi.quantity * oi.price) as total_revenue
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    $where_clause $filter_sql
    GROUP BY p.product_id
    ORDER BY total_quantity DESC
";

$veg_stmt = $conn->prepare($veg_sales_sql);
$veg_stmt->bind_param($filter_types, ...$filter_params);
$veg_stmt->execute();
$veg_result = $veg_stmt->get_result();

$veg_sales_data = [];
$total_orders_count_sql = "SELECT COUNT(DISTINCT o.order_id) as count FROM orders o JOIN order_items oi ON o.order_id = oi.order_id JOIN products p ON oi.product_id = p.product_id $where_clause $filter_sql";

// Execute total orders count query separately to be accurate with Distinct
$count_stmt = $conn->prepare($total_orders_count_sql);
$count_stmt->bind_param($filter_types, ...$filter_params);
$count_stmt->execute();
$count_res = $count_stmt->get_result()->fetch_assoc();
$summary_total_orders = $count_res['count'];
$count_stmt->close();


$summary_total_kg = 0;
$summary_total_earnings = 0;
$best_selling_veg = "N/A";
$max_qty = 0;

while ($row = $veg_result->fetch_assoc()) {
    $veg_sales_data[] = [
        'name' => $row['product_name'],
        'quantity' => floatval($row['total_quantity']),
        'revenue' => floatval($row['total_revenue'])
    ];
    
    $summary_total_kg += $row['total_quantity'];
    $summary_total_earnings += $row['total_revenue'];
    
    if ($row['total_quantity'] > $max_qty) {
        $max_qty = $row['total_quantity'];
        $best_selling_veg = $row['product_name'];
    }
}
$veg_stmt->close();


// Response
echo json_encode([
    'success' => true,
    'monthly_orders' => array_values($monthly_data), // [Jan_count, Feb_count, ...]
    'veg_sales' => $veg_sales_data,
    'summary' => [
        'total_orders' => $summary_total_orders,
        'total_kg' => number_format($summary_total_kg, 2),
        'total_earnings' => number_format($summary_total_earnings, 2),
        'best_seller' => $best_selling_veg
    ]
]);
?>
