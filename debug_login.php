<?php
include 'includes/db_connect.php';

echo "Checking database connection...\n";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully.\n";

$email = 'admin@farm2society.com';
$password = 'admin123';

echo "Checking user: $email\n";
$stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $name, $hashed_password, $role);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
    echo "User found. Role: $role\n";
    echo "Hash: $hashed_password\n";
    if (password_verify($password, $hashed_password)) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
        // Let's try to verify with a new hash of 'admin123'
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "New hash for 'admin123': $new_hash\n";
    }
} else {
    echo "User NOT found.\n";
}
$stmt->close();
?>
