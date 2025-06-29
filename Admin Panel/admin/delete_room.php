<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Check if admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("location: admin_login.php");
    exit;
}

require_once __DIR__ . '/config/database.php';

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $room_id = trim($_GET['id']);

    // Prepare a delete statement
    $sql = "DELETE FROM rooms WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $room_id;

        if ($stmt->execute()) {
            // Redirect to manage_rooms page after successful deletion
            header("location: manage_rooms.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
} else {
    // If ID is not provided, redirect to manage_rooms page
    header("location: manage_rooms.php");
    exit();
}

$conn->close();
?>