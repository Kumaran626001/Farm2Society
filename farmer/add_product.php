<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $farmer_id = $_SESSION['user_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Insert Product
    $stmt = $conn->prepare("INSERT INTO products (farmer_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdd", $farmer_id, $product_name, $price, $quantity);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Database Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Product - Farm2Society</title>
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
        <h2>Add Vegetable</h2>
        <?php if (isset($error))
            echo "<p class='error-msg'>$error</p>"; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Vegetable Name</label>
                <input type="text" name="product_name" required>
            </div>
            <div class="form-group">
                <label>Price per Kg</label>
                <input type="number" step="0.01" name="price" required>
            </div>
            <div class="form-group">
                <label>Available Quantity (kg)</label>
                <input type="number" step="0.01" name="quantity" required>
            </div>

            <button type="submit" class="btn">Add Product</button>
        </form>
    </div>
</body>

</html>