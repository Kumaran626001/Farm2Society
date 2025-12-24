<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Fetch orders containing products from this farmer
// This query joins order_items, orders, products, and users (consumer)
$query = "SELECT 
            oi.order_item_id, 
            o.order_id, 
            o.order_date, 
            u.name as consumer_name, 
            u.location as consumer_location,
            p.product_name, 
            oi.quantity, 
            oi.price,
            o.order_status
          FROM order_items oi
          JOIN orders o ON oi.order_id = o.order_id
          JOIN products p ON oi.product_id = p.product_id
          JOIN users u ON o.consumer_id = u.user_id
          WHERE p.farmer_id = ?
          ORDER BY o.order_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    // Note: This updates the whole order status. In a complex system, we might update item status.
    // For simplicity, we assume one order roughly corresponds to one delivery or we update the main order.
    // However, if multiple farmers are in one order, this might be tricky. 
    // Simplified: Farmer updates the main order status. Ideally, should be per item or split orders.
    // Let's assume for this MVP that the order status generally reflects the delivery.

    $update_stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $update_stmt->bind_param("si", $status, $order_id);
    $update_stmt->execute();
    header("Location: view_orders.php"); // Refresh
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Orders - Farm2Society</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <nav>
            <a href="../index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="add_product.php">Add Vegetable</a></li>
                <li><a href="view_orders.php">View Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Received Orders</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Consumer</th>
                        <th>Item</th>
                        <th>Qty (kg)</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo date("d-m-Y", strtotime($row['order_date'])); ?></td>
                            <td>
                                <?php echo $row['consumer_name']; ?><br>
                                <small><?php echo $row['consumer_location']; ?></small>
                            </td>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td>₹<?php echo $row['price'] * $row['quantity']; ?></td>
                            <td>
                                <span
                                    style="font-weight:bold; color: <?php echo ($row['order_status'] == 'Delivered') ? 'green' : 'orange'; ?>">
                                    <?php echo $row['order_status']; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                    <select name="status" style="padding: 5px;">
                                        <option value="Pending" <?php if ($row['order_status'] == 'Pending')
                                            echo 'selected'; ?>>
                                            Pending</option>
                                        <option value="Packed" <?php if ($row['order_status'] == 'Packed')
                                            echo 'selected'; ?>>
                                            Packed</option>
                                        <option value="Delivered" <?php if ($row['order_status'] == 'Delivered')
                                            echo 'selected'; ?>>Delivered</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn"
                                        style="width:auto; padding: 5px 10px;">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders received yet.</p>
        <?php endif; ?>
    </div>
</body>

</html>