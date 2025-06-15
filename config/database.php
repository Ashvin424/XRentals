<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'xrentals');
// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Attempt to connect to MySQL database
try {
    // Create connection with explicit port
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, '', DB_PORT);

    if($conn) {
        // First, create database if it doesn't exist
        $create_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
        mysqli_query($conn, $create_db);
        
        // Select the database
        mysqli_select_db($conn, DB_NAME);
        
        // Set charset to utf8mb4
        mysqli_set_charset($conn, "utf8mb4");
    }
} catch (mysqli_sql_exception $e) {
    die("Connection Error (" . $e->getCode() . "): " . $e->getMessage() . "\n" . 
        "Please try these steps:\n" .
        "1. Open XAMPP Control Panel\n" .
        "2. Click 'Shell' button\n" .
        "3. Type 'mysql -u root' to test direct connection\n" .
        "4. If that works but web connection fails, check your PHP configuration");
}

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}
?> 
