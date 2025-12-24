<?php
session_start();
include '../includes/db_connect.php';

$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['role'] === 'consumer';
$user_name = $is_logged_in ? $_SESSION['name'] : 'Guest';

// 1. Fetch Distinct Farmers for Dropdown
$farmers = $conn->query("SELECT user_id, name FROM users WHERE role = 'farmer'");

// 2. Build Filter Query
$sql = "SELECT p.*, u.name as farmer_name, u.average_rating 
        FROM products p 
        JOIN users u ON p.farmer_id = u.user_id 
        WHERE p.quantity > 0";

$types = "";
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $sql .= " AND p.product_name LIKE ?";
    $types .= "s";
    $params[] = "%" . $_GET['search'] . "%";
}

if (isset($_GET['farmer']) && !empty($_GET['farmer'])) {
    $sql .= " AND p.farmer_id = ?";
    $types .= "i";
    $params[] = $_GET['farmer'];
}

if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $sql .= " AND p.price >= ?";
    $types .= "d";
    $params[] = $_GET['min_price'];
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $sql .= " AND p.price <= ?";
    $types .= "d";
    $params[] = $_GET['max_price'];
}

if (isset($_GET['min_rating']) && is_numeric($_GET['min_rating'])) {
    $sql .= " AND u.average_rating >= ?";
    $types .= "d";
    $params[] = $_GET['min_rating'];
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Consumer Dashboard - Farm2Society</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .rating-stars {
            color: gold;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <a href="../index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <ul>
                <li><a href="dashboard.php">Browse Vegetables</a></li>
                <?php if ($is_logged_in): ?>
                    <li><a href="cart.php">My Cart</a></li>
                    <li><a href="orders.php">My Orders</a></li>
                    <li><a href="../logout.php">Logout (<?php echo $user_name; ?>)</a></li>
                <?php else: ?>
                    <li><a href="../login.php?redirect=consumer/dashboard.php">Login</a></li>
                    <li><a href="../register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h2>Fresh Vegetables from Local Farmers</h2>
            <a href="cart.php" class="btn" style="width: auto;">View Cart</a>
        </div>

        <!-- Filter Form -->
        <form class="filter-section" method="GET">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Search product..."
                    value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            </div>
            <div class="filter-group">
                <select name="farmer">
                    <option value="">All Farmers</option>
                    <?php while ($f = $farmers->fetch_assoc()): ?>
                        <option value="<?php echo $f['user_id']; ?>" <?php echo (isset($_GET['farmer']) && $_GET['farmer'] == $f['user_id']) ? 'selected' : ''; ?>>
                            <?php echo $f['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-group">
                <input type="number" name="min_price" placeholder="Min Price" style="width: 80px;"
                    value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>">
                <input type="number" name="max_price" placeholder="Max Price" style="width: 80px;"
                    value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>">
            </div>
            <div class="filter-group">
                <select name="min_rating">
                    <option value="">Min Rating</option>
                    <option value="5" <?php echo (isset($_GET['min_rating']) && $_GET['min_rating'] == 5) ? 'selected' : ''; ?>>5 Stars</option>
                    <option value="4" <?php echo (isset($_GET['min_rating']) && $_GET['min_rating'] == 4) ? 'selected' : ''; ?>>4+ Stars</option>
                    <option value="3" <?php echo (isset($_GET['min_rating']) && $_GET['min_rating'] == 3) ? 'selected' : ''; ?>>3+ Stars</option>
                    <option value="2" <?php echo (isset($_GET['min_rating']) && $_GET['min_rating'] == 2) ? 'selected' : ''; ?>>2+ Stars</option>
                    <option value="1" <?php echo (isset($_GET['min_rating']) && $_GET['min_rating'] == 1) ? 'selected' : ''; ?>>1+ Stars</option>
                </select>
            </div>
            <button type="submit" class="btn" style="width: auto; padding: 8px 15px;">Filter</button>
            <a href="dashboard.php" class="btn"
                style="width: auto; padding: 8px 15px; background-color: #7f8c8d;">Clear</a>
        </form>

        <div class="product-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                    $stars = round($row['average_rating']);
                    ?>
                    <div class="product-card">
                        <!-- Visual Header Placeholder since we have no real images yet -->
                        <div class="card-visual-header">
                            <i class="fas fa-carrot"></i> <!-- Generic veg icon -->
                        </div>

                        <div class="product-info">
                            <div>
                                <h4><?php echo $row['product_name']; ?></h4>
                                <div class="product-details">
                                    <p><i class="fas fa-user-circle"></i> <?php echo $row['farmer_name']; ?></p>
                                    <p class="rating-stars">
                                        <?php echo $stars > 0 ? str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) : 'No ratings'; ?>
                                    </p>
                                    <p>Available: <strong><?php echo $row['quantity']; ?> kg</strong></p>
                                    <span class="price-tag">₹<?php echo $row['price']; ?> <span
                                            style="font-size:0.8em; font-weight:400; color:#666;">/ kg</span></span>
                                </div>
                            </div>

                            <?php if ($is_logged_in): ?>
                                <form action="cart.php" method="POST"
                                    style="display: flex; gap: 10px; align-items: center; margin-top: 15px;">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <input type="hidden" name="product_name" value="<?php echo $row['product_name']; ?>">
                                    <input type="hidden" name="price" value="<?php echo $row['price']; ?>">

                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['quantity']; ?>"
                                        style="width: 70px; padding: 10px; border: 2px solid #eee; border-radius: 8px; font-weight: bold; text-align: center;">

                                    <button type="submit" name="add_to_cart" class="btn"
                                        style="margin: 0; padding: 10px; flex-grow: 1; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        Add <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="../login.php?redirect=consumer/dashboard.php" class="btn"
                                    style="background-color: #e67e22; width: 100%; margin-top: 20px; display: block;">Login to
                                    Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center" style="grid-column: 1/-1; padding: 40px; color: rgba(255,255,255,0.5);">
                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px;"></i>
                    <p>No products found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Chatbot Widget -->
    <div id="chatbot-container">
        <div id="chat-window">
            <div class="chat-header">
                <span><i class="fas fa-robot"></i> Veggie Assistant</span>
                <button onclick="toggleChat()" class="close-chat">&times;</button>
            </div>
            <div class="chat-body" id="chat-body">
                <div class="message bot-message">
                    Hello! 👋 I’m your Veggie Assistant. How can I help you today?
                </div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="user-input" placeholder="Ask about price, stock..."
                    onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
        <button id="chat-toggle-btn" onclick="toggleChat()">
            <i class="fas fa-comment-dots"></i>
        </button>
    </div>

    <style>
        /* Chatbot Styles */
        #chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #chat-toggle-btn {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #chat-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        #chat-window {
            display: none;
            width: 350px;
            height: 450px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.15);
            flex-direction: column;
            overflow: hidden;
            position: absolute;
            bottom: 80px;
            right: 0;
            animation: slideUp 0.3s ease-out;
            border: 1px solid #e0e0e0;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-header {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
        }

        .close-chat {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        .chat-body {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .bot-message {
            background: #e8f5e9;
            color: #2c3e50;
            align-self: flex-start;
            border-bottom-left-radius: 2px;
            border: 1px solid #c8e6c9;
        }

        .user-message {
            background: #2ecc71;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 2px;
            box-shadow: 0 2px 5px rgba(46, 204, 113, 0.2);
        }

        .chat-input-area {
            padding: 10px;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        #user-input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
            transition: border-color 0.3s;
        }

        #user-input:focus {
            border-color: #2ecc71;
        }

        .chat-input-area button {
            background: #2ecc71;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-input-area button:hover {
            background: #27ae60;
        }

        /* Mobile Responsiveness */
        @media (max-width: 480px) {
            #chat-window {
                width: 90vw;
                height: 60vh;
                bottom: 70px;
                right: 5vw;
            }
        }
    </style>

    <script>
        function toggleChat() {
            const chatWindow = document.getElementById('chat-window');
            if (chatWindow.style.display === 'none' || chatWindow.style.display === '') {
                chatWindow.style.display = 'flex';
                document.getElementById('user-input').focus();
            } else {
                chatWindow.style.display = 'none';
            }
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function appendMessage(sender, text) {
            const chatBody = document.getElementById('chat-body');
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message');
            messageDiv.classList.add(sender === 'bot' ? 'bot-message' : 'user-message');
            messageDiv.innerText = text; // Secure from HTML injection
            chatBody.appendChild(messageDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function sendMessage() {
            const inputField = document.getElementById('user-input');
            const message = inputField.value.trim();

            if (message === '') return;

            // 1. Show User Message
            appendMessage('user', message);
            inputField.value = '';

            // 2. Show Loading Indicator (Optional enhancement - simplistic "..." for now)
            // Can be added if needed, but for now we wait for AJAX response

            // 3. AJAX Request to Backend
            fetch('../includes/chatbot_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            })
                .then(response => response.json())
                .then(data => {
                    appendMessage('bot', data.response);
                })
                .catch(error => {
                    console.error('Error:', error);
                    appendMessage('bot', "Sorry, I'm having trouble connecting to the server.");
                });
        }
    </script>
</body>

</html>