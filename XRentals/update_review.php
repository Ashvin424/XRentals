<?php
// Initialize the session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "config/database.php";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate review_id and room_id
    if(!isset($_POST["review_id"]) || !isset($_POST["room_id"])) {
        $_SESSION["review_error"] = "Invalid request.";
        header("location: rooms.php");
        exit;
    }

    $review_id = $_POST["review_id"];
    $room_id = $_POST["room_id"];
    
    // Validate rating
    if(empty(trim($_POST["rating"])) || !is_numeric($_POST["rating"]) || $_POST["rating"] < 1 || $_POST["rating"] > 5) {
        $_SESSION["review_error"] = "Invalid rating value.";
        header("location: room_details.php?id=" . $room_id);
        exit;
    }
    
    // Validate review text
    if(empty(trim($_POST["review"]))) {
        $_SESSION["review_error"] = "Please enter your review.";
        header("location: room_details.php?id=" . $room_id);
        exit;
    }

    $rating = trim($_POST["rating"]);
    $review = trim($_POST["review"]);
    
    // Verify that the review belongs to the current user
    $check_sql = "SELECT id FROM room_reviews WHERE id = ? AND user_id = ?";
    if($stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $review_id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) == 0) {
            $_SESSION["review_error"] = "You are not authorized to edit this review.";
            header("location: room_details.php?id=" . $room_id);
            exit;
        }
        mysqli_stmt_close($stmt);
    }
    
    // Update the review
    $update_sql = "UPDATE room_reviews SET rating = ?, review = ? WHERE id = ? AND user_id = ?";
    if($stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($stmt, "isii", $rating, $review, $review_id, $_SESSION["id"]);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION["review_success"] = "Your review has been updated successfully.";
        } else {
            $_SESSION["review_error"] = "Something went wrong. Please try again later.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION["review_error"] = "Something went wrong. Please try again later.";
    }
    
    // Close connection
    mysqli_close($conn);
    
    // Redirect back to the room details page
    header("location: room_details.php?id=" . $room_id);
    exit;
} else {
    // Not a POST request, redirect to rooms page
    header("location: rooms.php");
    exit;
} 