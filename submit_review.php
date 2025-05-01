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

// Define variables and initialize with empty values
$rating = $review = "";
$rating_err = $review_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate room_id
    if(!isset($_POST["room_id"]) || empty($_POST["room_id"])) {
        header("location: rooms.php");
        exit;
    }
    $room_id = $_POST["room_id"];
    
    // Validate rating
    if(empty(trim($_POST["rating"]))) {
        $rating_err = "Please select a rating.";
    } elseif(!is_numeric($_POST["rating"]) || $_POST["rating"] < 1 || $_POST["rating"] > 5) {
        $rating_err = "Invalid rating value.";
    } else {
        $rating = trim($_POST["rating"]);
    }
    
    // Validate review
    if(empty(trim($_POST["review"]))) {
        $review_err = "Please enter your review.";
    } else {
        $review = trim($_POST["review"]);
    }
    
    // Check if user has already reviewed this room
    $check_sql = "SELECT id FROM room_reviews WHERE room_id = ? AND user_id = ?";
    if($stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $room_id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION["review_error"] = "You have already reviewed this room.";
            header("location: room_details.php?id=" . $room_id);
            exit;
        }
        mysqli_stmt_close($stmt);
    }
    
    // Check input errors before inserting in database
    if(empty($rating_err) && empty($review_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO room_reviews (room_id, user_id, rating, review) VALUES (?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "iiis", $room_id, $_SESSION["id"], $rating, $review);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                // Review added successfully
                $_SESSION["review_success"] = "Your review has been submitted successfully.";
                header("location: room_details.php?id=" . $room_id);
                exit;
            } else {
                $_SESSION["review_error"] = "Something went wrong. Please try again later.";
                header("location: room_details.php?id=" . $room_id);
                exit;
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    } else {
        $_SESSION["review_error"] = "Please fix the errors and try again.";
        header("location: room_details.php?id=" . $room_id);
        exit;
    }
    
    // Close connection
    mysqli_close($conn);
} else {
    // Not a POST request, redirect to rooms page
    header("location: rooms.php");
    exit;
} 