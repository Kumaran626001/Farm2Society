<?php
// Output Buffering to catch hidden warnings
// Output Buffering to catch hidden warnings
ob_start();
error_reporting(E_ALL); // Enable ALL errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json');


// ENABLE LOGGING
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

// Handle Browser Test (GET Request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "<h1>Scripts Status: Active</h1>";
    echo "This means the file is accessible and PHP is working.<br>";
    if (file_exists('../includes/db_connect.php')) {
        echo "DB File found.<br>";
        include '../includes/db_connect.php';
        if (isset($conn) && !$conn->connect_error) {
            echo "Database: Connected.<br>";
        } else {
            echo "Database: Failed.<br>";
        }
    } else {
        echo "DB File MISSING.<br>";
    }
    exit;
}

// v4.0 Consumer Folder Handler
try {
    session_start();

    // Check if db_connect exists (Up one level)
    if (!file_exists('../includes/db_connect.php')) {
        throw new Exception("Database file not found at ../includes/db_connect.php");
    }

    include '../includes/db_connect.php';
    if (file_exists('../includes/api_config.php')) {
        include '../includes/api_config.php';
    }


    // Verify DB connection
    if ($conn->connect_error) {
        throw new Exception("DB Connection Failed: " . $conn->connect_error);
    }

    // Ensure user is logged in as consumer
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
        send_json_response('Please login to use the chatbot.');
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON received.");
    }

    $message = strtolower(trim($data['message'] ?? ''));
    $message = str_replace('?', '', $message);

    if (empty($message)) {
        send_json_response('Please ask a valid question.');
    }

    // 1. Dynamic Order Status Check
    if (strpos($message, 'track') !== false || strpos($message, 'status') !== false || strpos($message, 'where is my order') !== false || strpos($message, 'order status') !== false) {
        $consumer_id = $_SESSION['user_id'];
        $order_sql = "SELECT order_id, order_status, total_amount FROM orders WHERE consumer_id = ? ORDER BY order_date DESC LIMIT 1";
        $stmt = $conn->prepare($order_sql);
        if (!$stmt)
            throw new Exception("Query Prepare Failed: " . $conn->error);

        $stmt->bind_param("i", $consumer_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $order = $res->fetch_assoc();
            send_json_response("Your latest order (#" . $order['order_id'] . ") is currently **" . $order['order_status'] . "**. Total Amount: ₹" . $order['total_amount']);
        } else {
            send_json_response("You haven't placed any orders yet.");
        }
    }

    // 2. Define General Intents
    $intents = [
        'greeting' => [
            'keywords' => ['hello', 'hi', 'hey', 'start', 'good morning', 'good evening'],
            'response' => "Hello! 👋 I’m your Veggie Assistant. How can I help you today?"
        ],
        'place_order' => [
            'keywords' => ['how to place', 'how do i order', 'steps to buy', 'buy vegetables', 'create order', 'place order'],
            'response' => "To place an order: 1. Browse vegetables on your dashboard.2. Click 'Add' to put items in your cart.3. Go to Cart and click 'Checkout'.4. Select your address and pay."
        ],
        'add_to_cart' => [
            'keywords' => ['add to cart', 'add item', 'how to add'],
            'response' => "Use the 'Add' number input on any vegetable card to add it to your cart."
        ],
        'multiple_farmers' => [
            'keywords' => ['multiple consumer', 'multiple farmers', 'different farmers'],
            'response' => "Yes! You can add items from different farmers to your cart. We will handle the logistics."
        ],
        'payment' => [
            'keywords' => ['payment method', 'how to pay', 'payment option', 'pay online', 'credit card', 'upi', 'payment'],
            'response' => "We accept UPI, Debit/Credit Cards, Net Banking, and Cash on Delivery (COD)."
        ],
        'cod' => [
            'keywords' => ['cod', 'cash on delivery', 'pay cash'],
            'response' => "Yes, Cash on Delivery is available for most locations."
        ],
        'safe_payment' => [
            'keywords' => ['safe', 'secure', 'is it safe'],
            'response' => "Absolutely. All online payments are processed through a secure encrypted gateway."
        ],
        'delivery_timing' => [
            'keywords' => ['time', 'when', 'timing', 'arrive', 'deliver'],
            'required' => ['delivery'],
            'response' => "We deliver between 7:00 AM and 9:00 PM daily. Orders before 2 PM are delivered the same day."
        ],
        'cancel_order' => [
            'keywords' => ['cancel', 'cancellation'],
            'response' => "You can cancel your order from the 'My Orders' page only if it hasn't been 'Packed' yet."
        ],
        'refund' => [
            'keywords' => ['refund', 'money back'],
            'response' => "Refunds for cancelled prepaid orders are processed within 3-5 business days."
        ],
        'farmer_rating' => [
            'keywords' => ['best rating', 'best farmer', 'top rated'],
            'response' => "You can filter farmers by rating (4+ Stars, 5 Stars) using the filter options on the dashboard."
        ],
        'add_rating' => [
            'keywords' => ['add rating', 'give rating', 'rate a farmer', 'rate farmer', 'review farmer'],
            'response' => "To rate a farmer: Go to 'My Orders', find a delivered order, and click 'Rate Farmer'."
        ],
        'organic' => [
            'keywords' => ['organic', 'chemical', 'natural'],
            'response' => "All our farmers contractually agree to use organic practices, but please check the specific farmer's rating and details."
        ],
        'support' => [
            'keywords' => ['contact', 'support', 'help', 'phone', 'email'],
            'response' => "You can reach us at support@farm2society.com or call +91-9876543210."
        ],
        'vegetables' => [
            'keywords' => ['what vegetables', 'available'],
            'response' => "You can see all available vegetables on your dashboard. Is there a specific one you are looking for?"
        ]
    ];

    foreach ($intents as $key => $intent) {
        if (isset($intent['required'])) {
            $req_met = true;
            foreach ($intent['required'] as $req) {
                if (strpos($message, $req) === false) {
                    $req_met = false;
                    break;
                }
            }
            if (!$req_met)
                continue;
        }
        foreach ($intent['keywords'] as $keyword) {
            if (strpos($message, $keyword) !== false) {
                send_json_response($intent['response']);
            }
        }
    }

    // Keyword Extraction (Farmers)
    $farmer_sql = "SELECT name, average_rating FROM users WHERE role = 'farmer'";
    $farmer_res = $conn->query($farmer_sql);
    if ($farmer_res) {
        while ($f_row = $farmer_res->fetch_assoc()) {
            if (strpos($message, strtolower($f_row['name'])) !== false) {
                $rating = $f_row['average_rating'] ? round($f_row['average_rating'], 1) . " Stars" : "Not yet rated";
                send_json_response("Farmer **" . $f_row['name'] . "** has an average rating of **" . $rating . "**.");
            }
        }
    }

    // Keyword Extraction (Vegetables)
    $veg_query = "SELECT DISTINCT product_name FROM products";
    $veg_result = $conn->query($veg_query);
    $vegetables = [];
    if ($veg_result) {
        while ($row = $veg_result->fetch_assoc()) {
            $vegetables[] = strtolower($row['product_name']);
        }
    }

    $found_veg = null;
    foreach ($vegetables as $veg) {
        if (strpos($message, $veg) !== false) {
            $found_veg = $veg;
            break;
        }
    }

    if ($found_veg) {
        if (strpos($message, 'price') !== false || strpos($message, 'rate') !== false || strpos($message, 'cost') !== false) {
            $sql = "SELECT p.price, p.quantity, u.name as farmer_name FROM products p JOIN users u ON p.farmer_id = u.user_id WHERE LOWER(p.product_name) = ? AND p.quantity > 0 ORDER BY p.price ASC LIMIT 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt)
                throw new Exception("Prepare Failed: " . $conn->error);
            $stmt->bind_param("s", $found_veg);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                send_json_response("The current price of " . ucfirst($found_veg) . " starts at ₹" . $row['price'] . " per kg (Farmer: " . $row['farmer_name'] . ").");
            } else {
                send_json_response("Sorry, " . ucfirst($found_veg) . " is currently out of stock.");
            }
        } elseif (strpos($message, 'stock') !== false || strpos($message, 'quantity') !== false || strpos($message, 'available') !== false) {
            $sql = "SELECT SUM(quantity) as total_stock FROM products WHERE LOWER(product_name) = ? AND quantity > 0";
            $stmt = $conn->prepare($sql);
            if (!$stmt)
                throw new Exception("Prepare Failed: " . $conn->error);
            $stmt->bind_param("s", $found_veg);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row['total_stock'] > 0) {
                send_json_response("We have " . $row['total_stock'] . " kg of " . ucfirst($found_veg) . " available right now.");
            } else {
                send_json_response("Sorry, " . ucfirst($found_veg) . " is currently out of stock.");
            }
        } else {
            $sql = "SELECT p.price, u.name as farmer_name, u.average_rating FROM products p JOIN users u ON p.farmer_id = u.user_id WHERE LOWER(p.product_name) = ? AND p.quantity > 0 ORDER BY u.average_rating DESC, p.price ASC LIMIT 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt)
                throw new Exception("Prepare Failed: " . $conn->error);
            $stmt->bind_param("s", $found_veg);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $rating_star = $row['average_rating'] ? " (" . round($row['average_rating'], 1) . "★)" : "";
                send_json_response(ucfirst($found_veg) . " is available from **" . $row['farmer_name'] . "**" . $rating_star . " at ₹" . $row['price'] . "/kg.");
            } else {
                send_json_response("Sorry, " . ucfirst($found_veg) . " is currently out of stock.");
            }
        }
    }

    // Fallback to Groq (Llama 3) if no local intent is matched
    $ai_response = callGroq($message);
    if ($ai_response) {
        send_json_response($ai_response);
    } else {
        send_json_response("I didn’t understand that, and I'm having trouble connecting to my AI brain. Please ask about vegetable prices, availability, or orders.");
    }

} catch (Exception $e) {
    send_json_response("Error: " . $e->getMessage());
}

// Helper to safely send JSON
function send_json_response($message)
{
    ob_end_clean(); // Discard buffer and stop buffering
    echo json_encode(['response' => $message]);
    exit;
}

// Helper Function for Groq API (OpenAI Compatible Architecture)
function callGroq($user_message)
{
    if (!defined('GROQ_API_KEY') || GROQ_API_KEY === 'YOUR_GROQ_API_KEY_HERE') {
        return "I can answer that, but my AI Brain key is missing. Admin, please configure the Groq API Key.";
    }

    $url = GROQ_API_URL;

    $system_instruction = "You are an assistant for 'Farm2Consumer', a direct farmer-to-consumer vegetable marketplace. 
    Your goal is to answer general questions helpfully and briefly. 
    context: Users can buy fresh organic vegetables directly from farmers. 
    If asked about specific order data, real-time prices, or stock that you don't know, guide them to use specific keywords like 'track order', 'price of [vegetable]', or 'stock of [vegetable]'.
    Do not make up fake prices or order details.
    Keep answers concise (under 50 words) and friendly.";

    $data = [
        "model" => GROQ_MODEL,
        "messages" => [
            ["role" => "system", "content" => $system_instruction],
            ["role" => "user", "content" => $user_message]
        ],
        "temperature" => 0.7
    ];

    // cURL Request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ]);
    // Disable SSL verification for local WAMP environment
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($result === FALSE || !empty($error)) {
        return "Connection Error: " . $error;
    }

    $response_data = json_decode($result, true);

    // Check for success
    if (isset($response_data['choices'][0]['message']['content'])) {
        return $response_data['choices'][0]['message']['content'];
    }

    // Check for API errors
    if (isset($response_data['error']['message'])) {
        return "API Error: " . $response_data['error']['message'];
    }

    return null;
}