<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

// Ensure user is logged in as consumer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    echo json_encode(['response' => 'Please login to use the chatbot.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($data['message'] ?? ''));

// Normalize message for easier matching
$message = str_replace('?', '', $message);


if (empty($message)) {
    echo json_encode(['response' => 'Please ask a valid question.']);
    exit;
}

// 1. Dynamic Order Status Check (High Priority)
if (strpos($message, 'track') !== false || strpos($message, 'status') !== false || strpos($message, 'where is my order') !== false || strpos($message, 'order status') !== false) {
    $consumer_id = $_SESSION['user_id'];
    $order_sql = "SELECT order_id, order_status, total_amount FROM orders WHERE consumer_id = ? ORDER BY order_date DESC LIMIT 1";
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("i", $consumer_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $order = $res->fetch_assoc();
        echo json_encode(['response' => "Your latest order (#" . $order['order_id'] . ") is currently **" . $order['order_status'] . "**. Total Amount: ₹" . $order['total_amount']]);
    } else {
        echo json_encode(['response' => "You haven't placed any orders yet."]);
    }
    exit;
}

// 2. Define General Intents and Keywords
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
        'keywords' => ['time', 'when', 'timing', 'arrive', 'deliver'], // Context: "delivery" checked separately or combined
        'required' => ['delivery'], // Must contain "delivery" AND one of the keywords above
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

// Check Intents
foreach ($intents as $key => $intent) {
    // If 'required' words exist, ensure they are present
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

    // Check optional keywords (at least one must match)
    foreach ($intent['keywords'] as $keyword) {
        if (strpos($message, $keyword) !== false) {
            echo json_encode(['response' => $intent['response']]);
            exit;
        }
    }
}


// Keyword Extraction for Specific Farmers
// Fetch all farmer names
$farmer_sql = "SELECT name, average_rating FROM users WHERE role = 'farmer'";
$farmer_res = $conn->query($farmer_sql);
while ($f_row = $farmer_res->fetch_assoc()) {
    $f_name_lower = strtolower($f_row['name']);
    // Check if farmer name is in the message
    if (strpos($message, $f_name_lower) !== false) {
        $rating = $f_row['average_rating'] ? round($f_row['average_rating'], 1) . " Stars" : "Not yet rated";
        echo json_encode(['response' => "Farmer **" . $f_row['name'] . "** has an average rating of **" . $rating . "**."]);
        exit; // Stop if farmer found
    }
}


// Keyword Extraction for Vegetables
// Get all unique product names from DB to match against
$veg_query = "SELECT DISTINCT product_name FROM products";
$veg_result = $conn->query($veg_query);
$vegetables = [];
while ($row = $veg_result->fetch_assoc()) {
    $vegetables[] = strtolower($row['product_name']);
}

$found_veg = null;
foreach ($vegetables as $veg) {
    if (strpos($message, $veg) !== false) {
        $found_veg = $veg;
        break;
    }
}

// Logic to fetch price/stock if a vegetable is identified
if ($found_veg) {
    // Check for "price" or "rate" intent
    if (strpos($message, 'price') !== false || strpos($message, 'rate') !== false || strpos($message, 'cost') !== false) {
        // Fetch lowest price details
        $sql = "SELECT p.price, p.quantity, u.name as farmer_name 
                FROM products p 
                JOIN users u ON p.farmer_id = u.user_id 
                WHERE LOWER(p.product_name) = ? AND p.quantity > 0 
                ORDER BY p.price ASC LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $found_veg);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $resp = "The current price of " . ucfirst($found_veg) . " starts at ₹" . $row['price'] . " per kg (Farmer: " . $row['farmer_name'] . ").";
            echo json_encode(['response' => $resp]);
        } else {
            echo json_encode(['response' => "Sorry, " . ucfirst($found_veg) . " is currently out of stock."]);
        }
        exit;
    }
    // Check for "stock" or "quantity" intent
    elseif (strpos($message, 'stock') !== false || strpos($message, 'quantity') !== false || strpos($message, 'available') !== false) {
        $sql = "SELECT SUM(quantity) as total_stock 
                FROM products 
                WHERE LOWER(product_name) = ? AND quantity > 0";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $found_veg);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['total_stock'] > 0) {
            echo json_encode(['response' => "We have " . $row['total_stock'] . " kg of " . ucfirst($found_veg) . " available right now."]);
        } else {
            echo json_encode(['response' => "Sorry, " . ucfirst($found_veg) . " is currently out of stock."]);
        }
        exit;
    }
    // Default fallback if just vegetable name is mentioned
    else {
        // Check for "who is the farmer" or "farmer" intent
        $show_farmer_details = (strpos($message, 'farmer') !== false || strpos($message, 'who') !== false);

        $sql = "SELECT p.price, u.name as farmer_name, u.average_rating 
                FROM products p 
                JOIN users u ON p.farmer_id = u.user_id 
                WHERE LOWER(p.product_name) = ? AND p.quantity > 0 
                ORDER BY u.average_rating DESC, p.price ASC LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $found_veg);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $rating_star = $row['average_rating'] ? " (" . round($row['average_rating'], 1) . "★)" : "";

            if ($show_farmer_details) {
                $resp = "The best-rated farmer for " . ucfirst($found_veg) . " is **" . $row['farmer_name'] . "**" . $rating_star . ". Price: ₹" . $row['price'] . "/kg.";
            } else {
                $resp = ucfirst($found_veg) . " is available from **" . $row['farmer_name'] . "**" . $rating_star . " at ₹" . $row['price'] . "/kg.";
            }
            echo json_encode(['response' => $resp]);
        } else {
            echo json_encode(['response' => "Sorry, " . ucfirst($found_veg) . " is currently out of stock."]);
        }
        exit;
    }
}

// Default Fallback
echo json_encode(['response' => "I didn’t understand that. Please ask about vegetable prices, availability, or orders."]);
?>