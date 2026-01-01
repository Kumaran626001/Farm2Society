<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../login.php");
    exit();
}

// Initialize Cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to Cart Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    // 1. Capture Basic Info from Form (Always available)
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // 2. Fetch Additional Details (Farmer Name) from DB
    $farmer_name = 'Local Farmer'; // Default
    $image = '';

    if (isset($product_id)) {
        $stmt_details = $conn->prepare("SELECT p.product_name, p.price, u.name as farmer_name 
                                       FROM products p 
                                       JOIN users u ON p.farmer_id = u.user_id 
                                       WHERE p.product_id = ?");
        $stmt_details->bind_param("i", $product_id);
        $stmt_details->execute();
        $d_res = $stmt_details->get_result();

        if ($d_res->num_rows > 0) {
            $details = $d_res->fetch_assoc();
            $farmer_name = $details['farmer_name'];
            // Optional: We can trust DB price more than POST if we want, but POST is fine for now
            // $price = $details['price']; 
        }
    }

    // Check if product already exists in cart, update quantity
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'price' => $price,
            'quantity' => $quantity,
            'farmer_name' => $farmer_name,
            'image' => $image
        ];
    }
    header("Location: dashboard.php"); // Redirect back to keep shopping
    exit();
}

// Logic for Updating Quantity (Increment/Decrement)
if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['id']) && isset($_GET['change'])) {
    $p_id = $_GET['id'];
    $change = (int) $_GET['change'];

    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $p_id) {
            $new_qty = $item['quantity'] + $change;
            if ($new_qty >= 1) {
                $item['quantity'] = $new_qty;
            }
            break;
        }
    }
    header("Location: cart.php");
    exit();
}

// Logic for Removing Item
if (isset($_GET['remove'])) {
    $id_to_remove = $_GET['remove'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $id_to_remove) {
            unset($_SESSION['cart'][$key]);
            break; // Stop after removing first match
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    header("Location: cart.php");
    exit();
}

$total_amount = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Cart - Farm2Society</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/firefly.css">
    <script src="../assets/js/firefly.js"></script>
</head>

<body>
    <header>
        <nav>
            <a href="../index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <div class="menu-toggle" onclick="document.querySelector('header nav ul').classList.toggle('nav-active')">
                <i class="fas fa-bars"></i>
            </div>
            <ul>
                <li><a href="dashboard.php">Browse Vegetables</a></li>
                <li><a href="cart.php">My Cart</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">

        <?php
        if (isset($_SESSION['error_msg'])) {
            echo "<div style='background-color: rgba(220, 53, 69, 0.1); color: #dc3545; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #dc3545;'>" . $_SESSION['error_msg'] . "</div>";
            unset($_SESSION['error_msg']);
        }
        ?>

        <div class="cart-layout-grid">

            <!-- Left Column: Items -->
            <div class="cart-items-column">
                <h2 class="cart-title">Your Cart (<?php echo count($_SESSION['cart']); ?> items)</h2>

                <?php if (!empty($_SESSION['cart'])): ?>
                    <div class="cart-list-v2">
                        <?php foreach ($_SESSION['cart'] as $item):
                            $subtotal = $item['price'] * $item['quantity'];
                            $total_amount += $subtotal;
                            ?>
                            <div class="cart-item-card-v2">
                                <!-- Image Removed as requested -->

                                <div class="item-details-v2">
                                    <h4><?php echo $item['product_name']; ?></h4>
                                    <span class="farmer-by">by
                                        <?php echo isset($item['farmer_name']) ? $item['farmer_name'] : 'Local Farmer'; ?></span>
                                    <div class="price-row">Price: ₹<?php echo $item['price']; ?> / kg</div>
                                    <div class="subtotal-row">Subtotal: ₹<?php echo number_format($subtotal, 2); ?></div>
                                </div>

                                <div class="item-actions-v2">
                                    <div class="qty-display-box">
                                        <a href="cart.php?action=update&id=<?php echo $item['product_id']; ?>&change=-1"
                                            class="qty-btn">-</a>
                                        <span class="qty-val"><?php echo $item['quantity']; ?></span>
                                        <a href="cart.php?action=update&id=<?php echo $item['product_id']; ?>&change=1"
                                            class="qty-btn">+</a>
                                    </div>
                                    <a href="cart.php?remove=<?php echo $item['product_id']; ?>" class="btn-trash-v2">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 60px; background:white; border-radius:8px;">
                        <i class="fas fa-shopping-basket" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                        <h3 style="color: #666;">Your cart is empty</h3>
                        <a href="dashboard.php" class="btn"
                            style="display: inline-block; width: auto; margin-top: 20px;">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Summary -->
            <?php if (!empty($_SESSION['cart'])): ?>
                <div class="cart-summary-column">
                    <div class="summary-card-v2">
                        <h3>Order Summary</h3>

                        <div class="summary-line">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($total_amount, 2); ?></span>
                        </div>

                        <div class="summary-line">
                            <span>Delivery Fee:</span>
                            <span>₹30.00</span>
                        </div>

                        <div class="summary-line total-line">
                            <span>Total:</span>
                            <span>₹<?php echo number_format($total_amount + 30, 2); ?></span>
                        </div>



                        <form action="checkout.php" method="POST" style="margin-top: 20px;">
                            <input type="hidden" name="total_amount" value="<?php echo $total_amount + 30; ?>">
                            <button type="submit" class="btn-checkout-v2">Place Order
                                (₹<?php echo number_format($total_amount + 30, 2); ?>)</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>

</html>