<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SESSION['cart'])) {
    $consumer_id = $_SESSION['user_id'];
    $total_amount = $_POST['total_amount'];

    // Start Transaction
    $conn->begin_transaction();

    try {
        // 1. Insert into Orders
        $stmt = $conn->prepare("INSERT INTO orders (consumer_id, total_amount) VALUES (?, ?)");
        $stmt->bind_param("id", $consumer_id, $total_amount);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // 2. Prepare statements for Loop
        $stmt_check = $conn->prepare("SELECT quantity, product_name FROM products WHERE product_id = ? FOR UPDATE");
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_update = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");

        foreach ($_SESSION['cart'] as $item) {
            $pid = $item['product_id'];
            $qty_ordered = $item['quantity'];
            $price = $item['price'];

            // A. Lock and Check Stock
            $stmt_check->bind_param("i", $pid);
            $stmt_check->execute();
            $res_check = $stmt_check->get_result();
            if ($res_check->num_rows === 0) {
                throw new Exception("Product ID $pid not found.");
            }
            $product_row = $res_check->fetch_assoc();
            $current_stock = $product_row['quantity'];
            $product_name = $product_row['product_name'];

            if ($current_stock < $qty_ordered) {
                throw new Exception("Insufficient stock for '$product_name'. Available: $current_stock kg, Requested: $qty_ordered kg.");
            }

            // B. Update Stock
            $stmt_update->bind_param("di", $qty_ordered, $pid);
            $stmt_update->execute();

            // C. Insert Order Item
            $stmt_item->bind_param("iidd", $order_id, $pid, $qty_ordered, $price);
            $stmt_item->execute();
        }

        // Commit and Clear Cart
        $conn->commit();
        $_SESSION['cart'] = [];
        $_SESSION['success_msg'] = "Order placed successfully! Order ID: #$order_id";
        header("Location: orders.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_msg'] = "Order Failed: " . $e->getMessage();
        header("Location: cart.php");
        exit();
    }
} else {
    header("Location: cart.php");
}
?>