<?php
include 'includes/db_connect.php';

$email = 'admin@farm2society.com';
$password = 'admin123';
$role = 'admin';
$name = 'Super Admin';
$is_verified = 1;

// Check if user already exists
$checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "Admin user already exists.<br>";
} else {
    // Creating new admin user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_verified) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("ssssi", $name, $email, $hashed_password, $role, $is_verified);

    if ($insertStmt->execute()) {
        echo "Admin user created successfully.<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
    $insertStmt->close();
}

$checkStmt->close();
$conn->close();
?>