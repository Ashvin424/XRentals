<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$username = 'admin'; // Default admin username
$password = 'password'; // Default admin password (CHANGE THIS IN PRODUCTION)

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO admins (username, password) VALUES (?, ?)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $param_username, $param_password);

    $param_username = $username;
    $param_password = $hashed_password;

    if ($stmt->execute()) {
        echo "Admin user '{$username}' added successfully.\n";
    } else {
        echo "Error: Could not add admin user. " . $stmt->error . "\n";
    }
    $stmt->close();
} else {
    echo "Error: Could not prepare statement. " . $conn->error . "\n";
}

$conn->close();
?>