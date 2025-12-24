<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $farmer_id = $_POST['farmer_id'];
    $rating = $_POST['rating'];

    // Validate inputs
    if ($rating < 1 || $rating > 5) {
        die("Invalid rating");
    }

    // Insert Rating
    $stmt = $conn->prepare("INSERT INTO ratings (order_id, farmer_id, rating) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $order_id, $farmer_id, $rating);

    if ($stmt->execute()) {
        // Recalculate Average Rating for Farmer
        $avg_query = "SELECT AVG(rating) as avg_rating FROM ratings WHERE farmer_id = ?";
        $stmt_avg = $conn->prepare($avg_query);
        $stmt_avg->bind_param("i", $farmer_id);
        $stmt_avg->execute();
        $result = $stmt_avg->get_result();
        $row = $result->fetch_assoc();

        $new_avg = round($row['avg_rating'], 2);

        // Update User Table
        $update_user = $conn->prepare("UPDATE users SET average_rating = ? WHERE user_id = ?");
        $update_user->bind_param("di", $new_avg, $farmer_id);
        $update_user->execute();

        $_SESSION['success_msg'] = "Rating submitted successfully!";
        header("Location: orders.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    header("Location: orders.php");
    exit();
}
?>