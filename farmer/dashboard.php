<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM products WHERE farmer_id = $farmer_id");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Farmer Dashboard - Farm2Society</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <header>
        <nav>
            <a href="../index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="add_product.php">Add Vegetable</a></li>
                <li><a href="view_orders.php">View Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="dashboard-header" style="margin-bottom: 40px;">
            <h2>Welcome back, <?php echo $_SESSION['name']; ?></h2>
            <a href="add_product.php" class="btn" style="width: auto; padding: 10px 25px; margin: 0;">+ Add New
                Vegetable</a>
        </div>

        <!-- analytics-section -->
        <div class="analytics-section" style="margin-bottom: 50px;">
            <div class="dashboard-header" style="border:none; margin-bottom:20px; align-items:center;">
                <h3 style="font-size: 1.5rem; color: var(--text-on-dark); margin:0;">Analytics Overview</h3>
                <div class="filter-controls" style="display:flex; gap:10px;">
                    <select id="filterMonth" class="btn-nav"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); cursor:pointer;">
                        <option value="" style="color:black;">All Months</option>
                        <option value="1" style="color:black;">January</option>
                        <option value="2" style="color:black;">February</option>
                        <option value="3" style="color:black;">March</option>
                        <option value="4" style="color:black;">April</option>
                        <option value="5" style="color:black;">May</option>
                        <option value="6" style="color:black;">June</option>
                        <option value="7" style="color:black;">July</option>
                        <option value="8" style="color:black;">August</option>
                        <option value="9" style="color:black;">September</option>
                        <option value="10" style="color:black;">October</option>
                        <option value="11" style="color:black;">November</option>
                        <option value="12" style="color:black;">December</option>
                    </select>
                    <select id="filterYear" class="btn-nav"
                        style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); cursor:pointer;">
                    </select>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="charts-container" style="gap: 20px; margin-bottom: 30px;">
                <div class="stat-card" style="flex: 1;">
                    <div class="stat-number" id="totalOrderVal">0</div>
                    <div>Total Orders</div>
                </div>
                <div class="stat-card" style="flex: 1;">
                    <div class="stat-number" id="totalKgVal">0</div>
                    <div>Vegetables Sold (kg)</div>
                </div>
                <div class="stat-card" style="flex: 1;">
                    <div class="stat-number" id="totalEarningsVal">₹0</div>
                    <div>Total Earnings</div>
                </div>
                <div class="stat-card" style="flex: 1;">
                    <div class="stat-number" id="bestSellerVal" style="font-size: 1.8rem; padding: 10px 0;">-</div>
                    <div>Best Selling</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-container">
                <div class="chart-wrapper">
                    <h3>Monthly Orders Trend</h3>
                    <canvas id="ordersChart"></canvas>
                </div>
                <div class="chart-wrapper">
                    <h3>Most Sold Vegetables</h3>
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <h3
            style="margin-bottom: 30px; font-size: 1.5rem; color: var(--text-on-dark); border-bottom: 2px solid var(--primary-color); display: inline-block; padding-bottom: 10px;">
            Your Products</h3>

        <?php if ($result->num_rows > 0): ?>
            <div class="product-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <!-- Visual Header -->
                        <div class="card-visual-header">
                            <i class="fas fa-seedling"></i>
                        </div>

                        <div class="product-info">
                            <div>
                                <h4><?php echo $row['product_name']; ?></h4>
                                <div class="product-details">
                                    <p>Available: <strong><?php echo $row['quantity']; ?> kg</strong></p>
                                    <span class="price-tag">₹<?php echo $row['price']; ?> <span
                                            style="font-size:0.8em; font-weight:400; color:#666;">/ kg</span></span>
                                </div>
                            </div>

                            <div class="actions" style="margin-top: 20px; display: flex; gap: 10px;">
                                <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn"
                                    style="background-color: var(--primary-dark); padding: 8px;">Edit</a>
                                <!-- <a href="delete_product.php?id=<?php echo $row['product_id']; ?>" class="btn-delete">Delete</a> -->
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding: 60px;">
                <i class="fas fa-box-open" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <h3 style="color: var(--text-on-dark);">You haven't added any products yet.</h3>
                <a href="add_product.php" class="btn" style="width: auto; margin-top: 20px;">Add First Product</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Init Years
        const yearSelect = document.getElementById('filterYear');
        const currentYear = new Date().getFullYear();
        for (let i = currentYear; i >= currentYear - 2; i--) {
            let opt = document.createElement('option');
            opt.value = i;
            opt.textContent = i;
            opt.style.color = 'black';
            yearSelect.appendChild(opt);
        }

        // Charts Instantiation
        let ordersChartInstance = null;
        let salesChartInstance = null;

        function initCharts() {
            // Orders Chart (Line/Bar)
            const ctx1 = document.getElementById('ordersChart').getContext('2d');
            ordersChartInstance = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Orders',
                        data: [], // To be filled
                        backgroundColor: '#74C69D',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });

            // Sales Chart (Pie/Bar)
            const ctx2 = document.getElementById('salesChart').getContext('2d');
            salesChartInstance = new Chart(ctx2, {
                type: 'doughnut', // Pie/Doughnut for veggie distribution
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#FFB5A7', '#FCD5CE', '#F8EDEB', '#74C69D', '#B7E4C7', '#95D5B2', '#52B788'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });
        }

        async function fetchAnalytics() {
            const m = document.getElementById('filterMonth').value;
            const y = document.getElementById('filterYear').value;

            try {
                const res = await fetch(`get_analytics_data.php?month=${m}&year=${y}`);
                const data = await res.json();

                if (data.success) {
                    // Update Summary Cards
                    document.getElementById('totalOrderVal').innerText = data.summary.total_orders;
                    document.getElementById('totalKgVal').innerText = data.summary.total_kg;
                    document.getElementById('totalEarningsVal').innerText = '₹' + data.summary.total_earnings;
                    document.getElementById('bestSellerVal').innerText = data.summary.best_seller;

                    // Update Orders Chart
                    // Monthly data is always full year trend
                    ordersChartInstance.data.datasets[0].data = data.monthly_orders;
                    ordersChartInstance.update();

                    // Update Sales Chart
                    const labels = data.veg_sales.map(item => item.name);
                    const quantities = data.veg_sales.map(item => item.quantity);

                    if (labels.length === 0) {
                        salesChartInstance.data.labels = ['No Data'];
                        salesChartInstance.data.datasets[0].data = [1];
                        salesChartInstance.data.datasets[0].backgroundColor = ['#e0e0e0'];
                    } else {
                        salesChartInstance.data.labels = labels;
                        salesChartInstance.data.datasets[0].data = quantities;

                        // Generate distinct vibrant colors
                        const colors = labels.map((_, index) => {
                            // Golden angle approximation for distinct colors
                            const hue = (index * 137.508) % 360;
                            return `hsl(${hue}, 75%, 65%)`;
                        });

                        salesChartInstance.data.datasets[0].backgroundColor = colors;
                    }
                    salesChartInstance.update();
                }
            } catch (err) {
                console.error('Error fetching analytics:', err);
            }
        }

        // Event Listeners
        document.getElementById('filterMonth').addEventListener('change', fetchAnalytics);
        document.getElementById('filterYear').addEventListener('change', fetchAnalytics);

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
            fetchAnalytics();
        });
    </script>
</body>

</html>