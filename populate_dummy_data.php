<?php
include 'includes/db_connect.php';

echo "Starting dummy data population...\n";

// 1. Ensure a Farmer exists
$farmer_id = 0;
$result = $conn->query("SELECT user_id FROM users WHERE role = 'farmer' LIMIT 1");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $farmer_id = $row['user_id'];
    echo "Using existing farmer ID: $farmer_id\n";
} else {
    // Create dummy farmer
    $password = password_hash('farmer123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password, role, is_verified) VALUES ('Dummy Farmer', 'farmer@dummy.com', '$password', 'farmer', 1)");
    $farmer_id = $conn->insert_id;
    echo "Created new dummy farmer with ID: $farmer_id\n";
}

// 2. Define Products
$vegetables = [
    ['name' => 'Potato', 'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Tomato', 'image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Onion', 'image' => 'https://images.unsplash.com/photo-1618512496248-a07fe83aa8cb?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Carrot', 'image' => 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Spinach', 'image' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Broccoli', 'image' => 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Cabbage', 'image' => 'https://images.unsplash.com/photo-1550953859-9f79ba0d937a?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Cauliflower', 'image' => 'https://images.unsplash.com/photo-1568584711075-3d021a7c3ca3?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Eggplant', 'image' => 'https://images.unsplash.com/photo-1615485967073-63a92540c497?auto=format&fit=crop&w=500&q=60'],
    ['name' => 'Cucumber', 'image' => 'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?auto=format&fit=crop&w=500&q=60']
];

// 3. Insert 40 records
$stmt = $conn->prepare("INSERT INTO products (farmer_id, product_name, price, quantity, image_path) VALUES (?, ?, ?, ?, ?)");
$count = 0;

for ($i = 0; $i < 40; $i++) {
    $veg = $vegetables[array_rand($vegetables)];
    $name = $veg['name'];
    $image = $veg['image'];
    $price = rand(10, 100); // Random price between 10 and 100
    $qty = rand(10, 500); // Random quantity

    $stmt->bind_param("isdds", $farmer_id, $name, $price, $qty, $image);
    if ($stmt->execute()) {
        $count++;
    }
}

echo "Successfully inserted $count dummy products.\n";
?>