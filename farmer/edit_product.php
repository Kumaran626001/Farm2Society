<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$product_id = isset($_GET['id']) ? $_GET['id'] : 0;
$farmer_id = $_SESSION['user_id'];

// Verify Product Belongs to Farmer
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND farmer_id = ?");
$stmt->bind_param("ii", $product_id, $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Product not found or access denied.");
}

$product = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Update
    $stmt = $conn->prepare("UPDATE products SET price = ?, quantity = ? WHERE product_id = ?");
    $stmt->bind_param("ddi", $price, $quantity, $product_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Error updating product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Product - Farm2Society</title>
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="add_product.php">Add Vegetable</a></li>
                <li><a href="view_orders.php">View Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="form-container">
        <h2>Edit Product: <?php echo $product['product_name']; ?></h2>
        <?php if (isset($error))
            echo "<p class='error-msg'>$error</p>"; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Price per Kg</label>
                <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="form-group">
                <label>Available Quantity (kg)</label>
                <input type="number" step="0.01" name="quantity" value="<?php echo $product['quantity']; ?>" required>
            </div>
            <button type="submit" class="btn">Update Method</button>
        </form>
    </div>
</body>

</html>