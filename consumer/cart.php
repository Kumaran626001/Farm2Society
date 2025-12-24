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
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

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
            'quantity' => $quantity
        ];
    }
    header("Location: dashboard.php"); // Redirect back to keep shopping
    exit();
}

// Remove from Cart
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $remove_id) {
            unset($_SESSION['cart'][$key]);
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex
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
</head>

<body>
    <header>
        <nav>
            <a href="../index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <ul>
                <li><a href="dashboard.php">Browse Vegetables</a></li>
                <li><a href="cart.php">My Cart</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Your Cart</h2>
        <?php
        if (isset($_SESSION['error_msg'])) {
            echo "<div style='background-color: rgba(220, 53, 69, 0.1); color: #dc3545; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #dc3545;'>" . $_SESSION['error_msg'] . "</div>";
            unset($_SESSION['error_msg']);
        }
        ?>
        <?php if (!empty($_SESSION['cart'])): ?>
            <div class="item-grid">
                <?php foreach ($_SESSION['cart'] as $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total_amount += $subtotal;
                    ?>
                    <div class="cart-card">
                        <div class="cart-details">
                            <h4><?php echo $item['product_name']; ?></h4>
                            <span class="cart-price">₹<?php echo $item['price']; ?> / kg</span>
                        </div>
                        <div class="cart-actions">
                            <div class="qty-badge">
                                <span><?php echo $item['quantity']; ?> kg</span>
                            </div>
                            <span class="cart-total">₹<?php echo number_format($subtotal, 2); ?></span>
                            <a href="cart.php?remove=<?php echo $item['product_id']; ?>" class="btn-remove"><i
                                    class="fas fa-trash"></i> Remove</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h3 style="color: var(--text-on-dark); margin-bottom: 20px;">Total Amount: <span
                        style="font-size: 2rem; color: var(--primary-color);">₹<?php echo number_format($total_amount, 2); ?></span>
                </h3>
                <form action="checkout.php" method="POST">
                    <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                    <button type="submit" class="btn"
                        style="width: auto; padding: 16px 48px; background: var(--primary-color); font-size: 1.2rem;">Proceed
                        to Checkout <i class="fas fa-arrow-right"></i></button>
                </form>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding: 60px;">
                <i class="fas fa-shopping-basket"
                    style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <h3 style="color: var(--text-on-dark);">Your cart is empty</h3>
                <a href="dashboard.php" class="btn" style="display: inline-block; width: auto; margin-top: 20px;">Start
                    Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>