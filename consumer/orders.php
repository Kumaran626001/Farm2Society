<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../login.php");
    exit();
}

$consumer_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE consumer_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $consumer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Orders - Farm2Society</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Styles handled in style.css -->
</head>

<body>
    <header>
        <nav>
            <a href="index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <ul>
                <li><a href="dashboard.php">Browse Vegetables</a></li>
                <li><a href="cart.php">My Cart</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Order History</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()):
                $order_id = $row['order_id'];
                $order_date = date("d M Y, h:i A", strtotime($row['order_date']));
                $status = $row['order_status'];
                // Determine Status Class
                $s_lower = strtolower($status);
                if ($s_lower == 'pending') $status_class = 'status-pending';
                elseif ($s_lower == 'completed' || $s_lower == 'delivered') $status_class = 'status-completed';
                elseif ($s_lower == 'cancelled') $status_class = 'status-cancelled';
                else $status_class = 'status-default';

                // Fetch Items and Farmers
                $items_query = "SELECT oi.*, p.product_name, p.farmer_id, u.name as farmer_name 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.product_id 
                               JOIN users u ON p.farmer_id = u.user_id
                               WHERE oi.order_id = ?";
                $stmt_items = $conn->prepare($items_query);
                $stmt_items->bind_param("i", $order_id);
                $stmt_items->execute();
                $items_result = $stmt_items->get_result();

                // Check existing ratings
                $ratings_query = "SELECT * FROM ratings WHERE order_id = ?";
                $stmt_ratings = $conn->prepare($ratings_query);
                $stmt_ratings->bind_param("i", $order_id);
                $stmt_ratings->execute();
                $ratings_result = $stmt_ratings->get_result();
                $existing_ratings = [];
                while ($r = $ratings_result->fetch_assoc()) {
                    $existing_ratings[$r['farmer_id']] = $r['rating'];
                }

                $farmers_in_order = [];
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <br>
                        <span>Order #<?php echo $order_id; ?></span>
                        <span><?php echo $order_date; ?></span>
                    </div>

                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Farmer</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items_result->fetch_assoc()):
                                $farmers_in_order[$item['farmer_id']] = $item['farmer_name'];
                                ?>
                                <tr>
                                    <td><?php echo $item['product_name']; ?></td>
                                    <td><?php echo $item['farmer_name']; ?></td>
                                    <td><?php echo $item['quantity']; ?> kg</td>
                                    <td>₹<?php echo $item['price']; ?></td>
                                    <td>₹<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <div class="order-footer">
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo $status; ?></span>
<br>
                        <!-- Rating Section -->
                        <?php if ($status == 'Delivered'): ?>
                            <div class="rating-section" style="margin-left: 20px;">
                                <?php foreach ($farmers_in_order as $fid => $fname): ?>
                                    <?php if (isset($existing_ratings[$fid])): ?>
                                        <span style="color: gold; margin-right: 15px;">
                                            Rated <?php echo $fname; ?>:
                                            <?php echo str_repeat('★', $existing_ratings[$fid]); ?>
                                        </span>
                                        <br>
                                    <?php else: ?>
                                        <form action="rate_farmer.php" method="POST" style="display:inline; margin-right: 15px;">
                                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                            <input type="hidden" name="farmer_id" value="<?php echo $fid; ?>">
                                            <label>Rate <?php echo $fname; ?>:</label>
                                            <select name="rating" required>
                                                <option value="5">5 ★</option>
                                                <option value="4">4 ★</option>
                                                <option value="3">3 ★</option>
                                                <option value="2">2 ★</option>
                                                <option value="1">1 ★</option>
                                            </select>
                                            <button type="submit" class="btn"
                                                style="width:auto; padding: 2px 5px; font-size: 0.8em;">Submit</button>
                                        </form>
                                        
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <span class="total-amount">Total: ₹<?php echo $row['total_amount']; ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You haven't placed any orders yet.</p>
        <?php endif; ?>
    </div>
</body>

</html>