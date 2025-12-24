<?php
include 'includes/db_connect.php';

// disable time limit
set_time_limit(0);

echo "Starting Dummy Data Generation...<br>";

// 1. Fetch Consumers
$consumers = [];
$c_res = $conn->query("SELECT user_id FROM users WHERE role='consumer'");
while ($row = $c_res->fetch_assoc())
    $consumers[] = $row['user_id'];

if (empty($consumers))
    die("No consumers found. Please register some consumers first.");

// 2. Fetch Products
$products = [];
$p_res = $conn->query("SELECT product_id, farmer_id, price FROM products");
while ($row = $p_res->fetch_assoc())
    $products[] = $row;

if (empty($products))
    die("No products found. Please add some products/farmers first.");

echo "Found " . count($consumers) . " consumers and " . count($products) . " products.<br>";

// 3. Generate Orders for 2025 (Jan to Dec)
$year = 2025;
$months = range(1, 12);
$order_count = 0;

$conn->begin_transaction();

try {
    foreach ($months as $month) {
        // Random number of orders per month (5 to 15)
        $num_orders = rand(5, 15);

        for ($i = 0; $i < $num_orders; $i++) {
            // Random Consumer
            $consumer_id = $consumers[array_rand($consumers)];

            // Random Date in Month
            $day = rand(1, 28); // Safe for all months
            $hour = rand(8, 20);
            $minute = rand(0, 59);
            $order_date = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $hour, $minute, 0);

            // Random items (1 to 4 unique items)
            $num_items = rand(1, 4);
            $order_products = [];

            // Pick random unique products
            $shuffled_products = $products;
            shuffle($shuffled_products);
            $selected_products = array_slice($shuffled_products, 0, $num_items);

            $total_amount = 0;
            $items_data = [];

            foreach ($selected_products as $prod) {
                // Random quantity: 0.5, 1, 2, 3, 5
                $qtys = [0.5, 1.0, 2.0, 3.0, 5.0];
                $qty = $qtys[array_rand($qtys)];
                $price = $prod['price'];
                $item_total = $price * $qty;

                $total_amount += $item_total;

                $items_data[] = [
                    'product_id' => $prod['product_id'],
                    'quantity' => $qty,
                    'price' => $price
                ];
            }

            // Random Status (Mostly Delivered for analytics)
            // 80% Delivered, 10% Pending, 5% Cancelled, 5% Packed
            $rand = rand(1, 100);
            if ($rand <= 80)
                $status = 'Delivered';
            elseif ($rand <= 90)
                $status = 'Pending';
            elseif ($rand <= 95)
                $status = 'Cancelled';
            else
                $status = 'Packed';

            // Insert Order
            $stmt = $conn->prepare("INSERT INTO orders (consumer_id, order_date, total_amount, order_status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $consumer_id, $order_date, $total_amount, $status);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();

            // Insert Items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items_data as $item) {
                $item_stmt->bind_param("iidd", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }
            $item_stmt->close();

            $order_count++;
        }
    }

    $conn->commit();
    echo "<h2 style='color:green'>Successfully generated $order_count orders!</h2>";
    echo "<p>Please go to your <a href='farmer/dashboard.php'>Dashboard</a> to check the analytics.</p>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<h2 style='color:red'>Error generating data: " . $e->getMessage() . "</h2>";
}
?>