<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Statistics
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$pending_farmers = $conn->query("SELECT COUNT(*) FROM users WHERE role='farmer' AND is_verified=0")->fetch_row()[0];
$total_revenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE order_status='Delivered'")->fetch_row()[0];
$total_revenue = $total_revenue ? $total_revenue : 0; // Handle null case

// Analytics Queries
// 1. Top Consumers
$consumer_query = "SELECT u.name, COUNT(o.order_id) as order_count 
                   FROM orders o 
                   JOIN users u ON o.consumer_id = u.user_id 
                   GROUP BY o.consumer_id 
                   ORDER BY order_count DESC LIMIT 5";
$con_result = $conn->query($consumer_query);
$con_labels = [];
$con_data = [];
while ($row = $con_result->fetch_assoc()) {
    $con_labels[] = $row['name'];
    $con_data[] = $row['order_count'];
}

// 2. Top Products
$product_query = "SELECT p.product_name, SUM(oi.quantity) as total_qty 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.product_id 
                  GROUP BY oi.product_id 
                  ORDER BY total_qty DESC LIMIT 5";
$prod_result = $conn->query($product_query);
$prod_labels = [];
$prod_data = [];
while ($row = $prod_result->fetch_assoc()) {
    $prod_labels[] = $row['product_name'];
    $prod_data[] = $row['total_qty'];
}
// 3. Top Rated Farmers
$rating_query = "SELECT u.name, u.average_rating, COUNT(r.rating_id) as total_ratings 
                 FROM users u 
                 LEFT JOIN ratings r ON u.user_id = r.farmer_id 
                 WHERE u.role = 'farmer' 
                 GROUP BY u.user_id 
                 ORDER BY u.average_rating DESC LIMIT 5";
$rate_result = $conn->query($rating_query);
$rate_labels = [];
$rate_data = [];
while ($row = $rate_result->fetch_assoc()) {
    $rate_labels[] = $row['name'] . " (" . $row['total_ratings'] . ")";
    $rate_data[] = $row['average_rating'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Farm2Society</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex: 1;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: var(--primary-color);
        }

        /* Chart Styles */
        .charts-container {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .chart-wrapper {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 400px;
        }

        .chart-wrapper h3 {
            text-align: center;
            margin-bottom: 15px;
            color: #34495e;
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <a href="../index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_orders.php">All Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Admin Dashboard</h2>

        <div class="stats-grid">
            <div class="stat-card" style="border-left: 5px solid #27ae60; background: #f0f9f4;">
                <div class="stat-number" style="color: #27ae60;">₹<?php echo number_format($total_revenue, 2); ?></div>
                <div>Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div>Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: orange;"><?php echo $pending_farmers; ?></div>
                <div>Pending Farmer Verifications</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">
            <div class="chart-wrapper">
                <h3>Top Farmers by Orders</h3>
                <canvas id="consumerChart"></canvas>
            </div>
            <div class="chart-wrapper">
                <h3>Top Selling Products</h3>
                <canvas id="productChart"></canvas>
            </div>
            <div class="chart-wrapper">
                <h3>Top Rated Farmers</h3>
                <canvas id="ratingChart"></canvas>
            </div>
        </div>

        <div class="actions">
            <a href="manage_users.php" class="btn" style="width: auto;">Verify Farmers</a>
        </div>
    </div>

    <script>
        // Set Global Config for Dark Theme compatibility
        Chart.defaults.color = '#6B7C73'; // Text color (muted green/grey)
        Chart.defaults.borderColor = 'rgba(0,0,0,0.05)'; // Grid lines
        Chart.defaults.font.family = "'Outfit', sans-serif";

        // Sample Data for Consumers vs Orders
        const consumerData = {
            labels: <?php echo json_encode($con_labels); ?>,
            data: <?php echo json_encode($con_data); ?>
        };

        // Sample Data for Products vs Quantity
        const productData = {
            labels: <?php echo json_encode($prod_labels); ?>,
            data: <?php echo json_encode($prod_data); ?>
        };

        // Data for Top Rated Farmers
        const ratingData = {
            labels: <?php echo json_encode($rate_labels); ?>,
            data: <?php echo json_encode($rate_data); ?>
        };

        // Color Palette
        const palette = [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(199, 199, 199, 0.7)',
            'rgba(83, 102, 255, 0.7)',
            'rgba(40, 167, 69, 0.7)',
            'rgba(220, 53, 69, 0.7)'
        ];

        const paletteBorder = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(199, 199, 199, 1)',
            'rgba(83, 102, 255, 1)',
            'rgba(40, 167, 69, 1)',
            'rgba(220, 53, 69, 1)'
        ];

        function getPalette(count) {
            return Array.from({ length: count }, (_, i) => palette[i % palette.length]);
        }

        function getPaletteBorder(count) {
            return Array.from({ length: count }, (_, i) => paletteBorder[i % paletteBorder.length]);
        }

        // Function to assign colors (highlight max value)
        function getHighlightColors(data, defaultColor, highlightColor) {
            const maxVal = Math.max(...data);
            return data.map(val => val === maxVal ? highlightColor : defaultColor);
        }

        // Render Consumer Chart (Bar)
        const ctxConsumer = document.getElementById('consumerChart').getContext('2d');
        new Chart(ctxConsumer, {
            type: 'bar',
            data: {
                labels: consumerData.labels,
                datasets: [{
                    label: 'Number of Orders',
                    data: consumerData.data,
                    backgroundColor: getHighlightColors(consumerData.data, 'rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.8)'),
                    borderColor: getHighlightColors(consumerData.data, 'rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)'),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `Orders: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Render Product Chart (Pie)
        const ctxProduct = document.getElementById('productChart').getContext('2d');
        new Chart(ctxProduct, {
            type: 'pie',
            data: {
                labels: productData.labels,
                datasets: [{
                    label: 'Quantity Sold (kg)',
                    data: productData.data,
                    backgroundColor: getPalette(productData.data.length),
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.label}: ${context.raw} kg`;
                            }
                        }
                    }
                }
            }
        });

        // Render Rating Chart (Bar)
        const ctxRating = document.getElementById('ratingChart').getContext('2d');
        new Chart(ctxRating, {
            type: 'bar',
            data: {
                labels: ratingData.labels,
                datasets: [{
                    label: 'Average Rating (1-5)',
                    data: ratingData.data,
                    backgroundColor: 'rgba(255, 206, 86, 0.7)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y', // Horizontal Bar Chart
                scales: {
                    x: { beginAtZero: true, max: 5 }
                }
            }
        });
    </script>
</body>

</html>