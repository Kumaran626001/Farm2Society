<?php
session_start();
include '../includes/db_connect.php';

$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['role'] === 'consumer';
$user_name = $is_logged_in ? $_SESSION['name'] : 'Guest';

// 1. Fetch Distinct Farmers for Dropdown
$farmers = $conn->query("SELECT user_id, name FROM users WHERE role = 'farmer'");

// Fetch products with Farmer Name and Rating
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
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-section {
            /* Keep existing overrides if any, or just new styles */
        }
    </style>
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

        <div class="dashboard-layout">
            <!-- Filter Sidebar -->
            <aside class="filter-sidebar">
                <div class="filter-header-visual">
                    <h3><i class="fas fa-filter"></i> Filters</h3>
                </div>
                <form class="filter-form" method="GET">
                    <!-- Search Section -->
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search products..." class="sidebar-search"
                            value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    </div>

                    <!-- Farmers Section -->
                    <div class="filter-section-block">
                        <h4>Farmers</h4>
                        <div class="filter-item">
                            <select name="farmer" class="sidebar-select">
                                <option value="">All Farmers</option>
                                <?php
                                $farmers->data_seek(0); // Reset pointer
                                while ($f = $farmers->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $f['user_id']; ?>" <?php echo (isset($_GET['farmer']) && $_GET['farmer'] == $f['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo $f['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Price Section -->
                    <div class="filter-section-block">
                        <h4>Price Range</h4>
                        <div class="price-inputs">
                            <input type="number" name="min_price" placeholder="Min"
                                value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Max"
                                value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>">
                        </div>
                    </div>

                    <!-- Rating Section -->
                    <div class="filter-section-block">
                        <h4>Rating</h4>
                        <select name="min_rating" class="sidebar-select">
                            <option value="">Any Rating</option>
                            <option value="5" <?php echo (isset($_GET['min_rating']) && $_GET['min_rating'] == 5) ? 'selected' : ''; ?>>5 Stars</option>
                            <option value="4" <?php echo (isset($_GET['min_rating']) && $_GET['min_rating'] == 4) ? 'selected' : ''; ?>>4+ Stars</option>
                            <option value="3" <?php echo (isset($_GET['min_rating']) && $_GET['min_rating'] == 3) ? 'selected' : ''; ?>>3+ Stars</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-apply">Apply Filters</button>
                        <a href="dashboard.php" class="btn btn-clear">Clear Filters</a>
                    </div>
                </form>
            </aside>

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
                                            <?php
                                            $stars = round($row['average_rating']);
                                            echo $stars > 0 ? str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) : 'No ratings';
                                            ?>
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
        </div> <!-- End product-grid -->
    </div> <!-- End dashboard-layout -->
    </div> <!-- End container -->

    </div> <!-- End container -->

    <!-- Chatbot Widget -->
    <div id="chatbot-container">
        <div id="chat-window">
            <div class="chat-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <!-- Zoomed Avatar Wrapper -->
                    <div
                        style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; border: 2px solid rgba(255,255,255,0.8); flex-shrink: 0; background: #f0fff4;">
                        <img src="../assets/img/ai_avatar.png" alt="AI"
                            style="width: 100%; height: 100%; object-fit: cover; transform: scale(1.35);">
                    </div>
                    <span>Veggie Assistant</span>
                </div>
                <button onclick="toggleChat()" class="close-chat">&times;</button>
            </div>
            <div class="chat-body" id="chat-body">
                <div class="message bot-message">
                    <!-- Zoomed Avatar Wrapper -->
                    <div
                        style="width: 24px; height: 24px; min-width: 24px; border-radius: 50%; overflow: hidden; margin-top: 2px; background: #f0fff4;">
                        <img src="../assets/img/ai_avatar.png" alt="AI"
                            style="width: 100%; height: 100%; object-fit: cover; transform: scale(1.35);">
                    </div>
                    Hello! 👋 I’m your Veggie Assistant. How can I help you today?
                </div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="user-input" placeholder="Ask any questions..."
                    onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>

        <div id="chat-greeting" onclick="toggleChat()">Hi, I am Veggie Assistant</div>

        <button id="chat-toggle-btn" onclick="toggleChat()"
            style="padding: 0; overflow: hidden; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2); background: #f0fff4;">
            <img src="../assets/img/ai_avatar.png" class="toggle-avatar" alt="Chat"
                style="width: 100%; height: 100%; object-fit: cover; display: block; border-radius: 50%; transform: scale(1.4);">
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
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .chat-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .msg-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 2px;
            /* Align with text top */
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

        /* Mobile Responsiveness handled in style.css */


        /* Chat Greeting Bubble */
        #chat-greeting {
            position: absolute;
            bottom: 85px;
            right: 0;
            background: white;
            padding: 15px 20px;
            border-radius: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            font-weight: 700;
            font-size: 15px;
            line-height: 1.3;
            color: #34495e;
            cursor: pointer;
            white-space: normal;
            max-width: 140px;
            text-align: center;
            animation: bounceGreeting 3s infinite ease-in-out;
            z-index: 999;
            border: 1px solid rgba(0,0,0,0.05);
        }

        @keyframes bounceGreeting {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>

    <script>
        function toggleChat() {
            const chatWindow = document.getElementById('chat-window');
            const greeting = document.getElementById('chat-greeting');
            const isMobile = window.innerWidth <= 768;

            if (chatWindow.style.display === 'none' || chatWindow.style.display === '') {
                chatWindow.style.display = 'flex';
                if (greeting) greeting.style.display = 'none';
                document.getElementById('user-input').focus();

                // Lock scroll on mobile
                if (isMobile) {
                    document.body.style.overflow = 'hidden';
                }
            } else {
                chatWindow.style.display = 'none';

                // Unlock scroll
                document.body.style.overflow = '';
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

            if (sender === 'bot') {
                const avatarWrapper = document.createElement('div');
                avatarWrapper.style.cssText = 'width: 24px; height: 24px; min-width: 24px; border-radius: 50%; overflow: hidden; margin-top: 2px; flex-shrink: 0; background: #f0fff4;';
                const avatarImg = document.createElement('img');
                avatarImg.src = '../assets/img/ai_avatar.png';
                avatarImg.style.cssText = 'width: 100%; height: 100%; object-fit: cover; transform: scale(1.35);';
                avatarWrapper.appendChild(avatarImg);
                messageDiv.appendChild(avatarWrapper);

                const textSpan = document.createElement('span');
                textSpan.innerText = " " + text;
                messageDiv.appendChild(textSpan);
            } else {
                messageDiv.innerText = text;
            }

            chatBody.appendChild(messageDiv);

            // Use requestAnimationFrame for smoother scrolling
            requestAnimationFrame(() => {
                chatBody.scrollTo({
                    top: chatBody.scrollHeight,
                    behavior: 'smooth'
                });
            });
        }

        function sendMessage() {
            const inputField = document.getElementById('user-input');
            const message = inputField.value.trim();

            if (message === '') return;

            appendMessage('user', message);
            inputField.value = '';

            // On mobile, keep focus but don't re-trigger keyboard scroll if possible
            // inputField.focus(); 

            fetch('temp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message: message })
            })
                .then(async response => {
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error("Invalid Server Response");
                    }
                })
                .then(data => {
                    appendMessage('bot', data.response);
                })
                .catch(error => {
                    appendMessage('bot', "Sorry, I'm having trouble connecting to the server.");
                });
        }

    </script>
</body>

</html>