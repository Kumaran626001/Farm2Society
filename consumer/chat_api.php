<?php
// chat_api.php - Alternative Cleaner Version
// Access this via browser to test: /consumer/chat_api.php

// 1. Diagnostics (Print to screen)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// 2. Handle Test Request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = [
        "status" => "active",
        "message" => "Chat API is Working!",
        "server" => $_SERVER['HTTP_HOST']
    ];
    echo json_encode($response);
    exit;
}

// 3. Main Logic
try {
    session_start();

    // DB Connection
    if (!file_exists('../includes/db_connect.php')) {
        throw new Exception("DB Config Missing");
    }
    include '../includes/db_connect.php';

    // Get Input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $user_msg = isset($data['message']) ? $data['message'] : '';

    if (empty($user_msg)) {
        echo json_encode(['response' => "Hello! I am ready."]);
        exit;
    }

    // -- Simple Echo Logic for now (to prove connection) --
    // We will restore full logic once connection is proven.

    // Simulate thinking
    $reply = "Server received: " . htmlspecialchars($user_msg);

    echo json_encode(['response' => $reply]);

} catch (Exception $e) {
    echo json_encode(['response' => "Error: " . $e->getMessage()]);
}
?>