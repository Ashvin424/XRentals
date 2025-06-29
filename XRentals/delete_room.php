<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config/database.php";

// Check if room ID is provided
if(!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: profile.php");
    exit;
}

$room_id = $_GET["id"];
$user_id = $_SESSION["id"];

// First verify that the room belongs to the logged-in user
$verify_sql = "SELECT id FROM rooms WHERE id = ? AND user_id = ?";
if($verify_stmt = mysqli_prepare($conn, $verify_sql)){
    mysqli_stmt_bind_param($verify_stmt, "ii", $room_id, $user_id);
    if(mysqli_stmt_execute($verify_stmt)){
        mysqli_stmt_store_result($verify_stmt);
        if(mysqli_stmt_num_rows($verify_stmt) != 1){
            // Room doesn't exist or doesn't belong to user
            $_SESSION["error_message"] = "Invalid room or unauthorized access.";
            header("location: profile.php");
            exit;
        }
    }
    mysqli_stmt_close($verify_stmt);
}

// Delete the room
$delete_sql = "DELETE FROM rooms WHERE id = ? AND user_id = ?";
if($delete_stmt = mysqli_prepare($conn, $delete_sql)){
    mysqli_stmt_bind_param($delete_stmt, "ii", $room_id, $user_id);
    if(mysqli_stmt_execute($delete_stmt)){
        $_SESSION["success_message"] = "Room deleted successfully.";
    } else {
        $_SESSION["error_message"] = "Something went wrong. Please try again later.";
    }
    mysqli_stmt_close($delete_stmt);
}

// Redirect back to profile page
header("location: profile.php");
exit;
?> 