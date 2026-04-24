<?php
// ============================================
// config.php - Database Configuration
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password
define('DB_NAME', 'stock_db');

define('SITE_NAME', 'Stock Management System');
define('BASE_URL', 'http://localhost/stock-management/');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("
    <div style='font-family:sans-serif;padding:40px;text-align:center;color:#e74c3c;'>
        <h2>⚠️ Database Connection Failed</h2>
        <p>" . $conn->connect_error . "</p>
        <p>Please check your database settings in config.php</p>
    </div>");
}

$conn->set_charset("utf8");

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth check function
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . BASE_URL . "dashboard.php?error=access_denied");
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function sanitize($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}
?>
