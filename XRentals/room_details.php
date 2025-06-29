<?php
// Initialize the session
session_start();

// Include config file
require_once "config/database.php";

// Check if room ID is provided
if(!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: rooms.php");
    exit();
}

$room_id = $_GET["id"];
$room = null;
$owner = null;
$error = "";

// Prepare a select statement
$sql = "SELECT r.*, u.username, u.full_name FROM rooms r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.id = ?";

if($stmt = mysqli_prepare($conn, $sql)) {
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    
    // Attempt to execute the prepared statement
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            $room = mysqli_fetch_assoc($result);
        } else {
            $error = "Room not found.";
        }
    } else {
        $error = "Something went wrong. Please try again later.";
    }
    
    // Close statement
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $room ? htmlspecialchars($room["title"]) : "Room Details"; ?> - XRentals</title>
    <link rel="icon" type="image/x-icon" href="logo/head_logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Animation Keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Animation Classes */
        .animate-fade-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .animate-fade {
            animation: fadeIn 0.6s ease-out forwards;
            opacity: 0;
        }

        .animate-scale {
            animation: scaleIn 0.6s ease-out forwards;
            opacity: 0;
        }

        /* Animation Delays */
        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
            --text-color: #333;
            --light-gray: #f8f9fa;
        }

        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.8rem 2rem;
            min-height: 75px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            /* animation: slideDown 0.5s ease-out forwards; */
        }

        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            height: 55px;
            width: auto;
            margin-right: 1rem;
        }

        .nav-link {
            color: var(--primary-color) !important;
            font-weight: 500;
            margin: 0 1.2rem;
            position: relative;
            transition: color 0.3s ease;
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--accent-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            color: var(--accent-color) !important;
        }

        .navbar-toggler {
            border: none;
            padding: 0.8rem;
        }

        .navbar-toggler-icon {
            width: 1.3em;
            height: 1.3em;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 991px) {
            .navbar {
                padding: 0.8rem;
                min-height: 65px;
            }
            
            .navbar-brand img {
                height: 45px;
            }

            .nav-link {
                margin: 0;
                padding: 0.6rem 0.5rem;
            }
        }

        .room-details-container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .room-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .room-title {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .room-price {
            font-size: 1.8rem;
            color: var(--accent-color);
            font-weight: 600;
            margin-bottom: 20px;
        }

        .room-location {
            color: var(--text-color);
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .room-description {
            color: var(--text-color);
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .features-section {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: var(--text-color);
        }

        .feature-item i {
            color: var(--accent-color);
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .contact-section {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 10px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: var(--text-color);
        }

        .contact-item i {
            color: var(--accent-color);
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .contact-button {
            background: var(--accent-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .contact-button:hover {
            background: #2980b9;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .amenity-badge {
            background: var(--accent-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .error-message {
            text-align: center;
            color: var(--secondary-color);
            font-size: 1.2rem;
            margin: 50px 0;
        }

        @media (max-width: 768px) {
            .room-details-container {
                margin: 20px;
                padding: 20px;
            }

            .room-image {
                height: 300px;
            }

            .room-title {
                font-size: 2rem;
            }

            .room-price {
                font-size: 1.5rem;
            }
        }

        .rating-summary {
            text-align: center;
            margin-bottom: 2rem;
        }
        .average-rating h3 {
            font-size: 2.5rem;
            margin: 0;
        }
        .average-rating p {
            color: #666;
            margin: 0;
        }
        .review-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .review-item {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .reviewer-info strong {
            display: block;
            margin-bottom: 0.5rem;
        }
        .rating .fas {
            margin-right: 2px;
        }
        .review-date {
            color: #666;
            font-size: 0.9rem;
        }
        .review-content {
            color: #333;
            line-height: 1.6;
        }
        .text-warning {
            color: #ffc107;
        }
        .text-muted {
            color: #ddd;
        }
        .review-meta {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .btn-edit-review {
            background: none;
            border: none;
            color: var(--accent-color);
            padding: 0;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-edit-review:hover {
            color: #2980b9;
        }
        .edit-form {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            color: white;
        }
        .btn-delete-review {
            background: none;
            border: none;
            color: var(--secondary-color);
            padding: 0;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-delete-review:hover {
            color: #c0392b;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
            position: relative;
        }
        .modal-header {
            margin-bottom: 15px;
        }
        .modal-title {
            margin: 0;
            color: var(--primary-color);
        }
        .modal-body {
            margin-bottom: 20px;
            color: #666;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-modal {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        .btn-delete {
            background-color: var(--secondary-color);
            color: white;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="index.php">
            <img src="logo/logo.png" alt="XRentals Logo">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="list_your_room.php">List Your Room</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rooms.php">Listed Rooms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contactUs.php">Contact</a>
                </li>
                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <img src="<?php echo isset($_SESSION['profile_image']) ? 'uploads/profile_images/' . $_SESSION['profile_image'] : 'uploads/profile_images/default.png'; ?>" 
                                 alt="Profile" 
                                 style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-color);">
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-4" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php if(!empty($error)): ?>
            <div class="error-message animate-fade">
                <?php echo $error; ?>
            </div>
        <?php elseif($room): ?>
            <div class="room-details-container">
                <?php if(!empty($room["image_path"])): ?>
                    <img src="<?php echo htmlspecialchars($room["image_path"]); ?>" 
                         alt="<?php echo htmlspecialchars($room["title"]); ?>" 
                         class="room-image animate-scale">
                <?php endif; ?>

                <h1 class="room-title animate-fade-up"><?php echo htmlspecialchars($room["title"]); ?></h1>
                <div class="room-price animate-fade-up delay-1">₹<?php echo number_format($room["price"]); ?> per month</div>
                <div class="room-location animate-fade-up delay-1">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?php echo htmlspecialchars($room["location"]); ?>
                </div>

                <div class="room-description animate-fade-up delay-2">
                    <?php echo nl2br(htmlspecialchars($room["description"])); ?>
                </div>

                <div class="features-section animate-fade-up delay-2">
                    <h2 class="section-title">Room Features</h2>
                    <div class="feature-item animate-fade delay-3">
                        <i class="fas fa-home"></i>
                        <span>Room Type: <?php echo htmlspecialchars($room["room_type"]); ?></span>
                    </div>
                    <div class="feature-item animate-fade delay-3">
                        <i class="fas fa-couch"></i>
                        <span>Furnishing: <?php echo htmlspecialchars($room["furnishing"]); ?></span>
                    </div>
                    <div class="feature-item animate-fade delay-3">
                        <i class="fas fa-bath"></i>
                        <span>Bathroom: <?php echo htmlspecialchars($room["bathroom_type"]); ?></span>
                    </div>
                </div>

                <div class="features-section animate-fade-up delay-3">
                    <h2 class="section-title">Amenities</h2>
                    <?php if($room["parking"]): ?>
                        <span class="amenity-badge animate-fade delay-4"><i class="fas fa-parking"></i> Parking</span>
                    <?php endif; ?>
                    <?php if($room["wifi"]): ?>
                        <span class="amenity-badge animate-fade delay-4"><i class="fas fa-wifi"></i> WiFi</span>
                    <?php endif; ?>
                    <?php if($room["air_conditioning"]): ?>
                        <span class="amenity-badge animate-fade delay-4"><i class="fas fa-snowflake"></i> AC</span>
                    <?php endif; ?>
                    <?php if($room["balcony"]): ?>
                        <span class="amenity-badge animate-fade delay-4"><i class="fas fa-door-open"></i> Balcony</span>
                    <?php endif; ?>
                    <?php if($room["water_supply"]): ?>
                        <span class="amenity-badge animate-fade delay-4"><i class="fas fa-tint"></i> 24/7 Water Supply</span>
                    <?php endif; ?>
                </div>

                <div class="features-section animate-fade-up delay-3">
                    <h2 class="section-title">Reviews</h2>
                    <?php
                    // Fetch average rating
                    $avg_rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM room_reviews WHERE room_id = ?";
                    if($stmt = mysqli_prepare($conn, $avg_rating_sql)) {
                        mysqli_stmt_bind_param($stmt, "i", $room_id);
                        mysqli_stmt_execute($stmt);
                        $avg_result = mysqli_stmt_get_result($stmt);
                        $rating_data = mysqli_fetch_assoc($avg_result);
                        $avg_rating = round($rating_data['avg_rating'], 1);
                        $total_reviews = $rating_data['total_reviews'];
                    }
                    ?>
                    
                    <div class="rating-summary animate-fade delay-4">
                        <div class="average-rating">
                            <h3><?php echo $avg_rating ? $avg_rating : '0.0'; ?> <i class="fas fa-star text-warning"></i></h3>
                            <p><?php echo $total_reviews; ?> reviews</p>
                        </div>
                    </div>

                    <?php
                    // Display success message if set
                    if(isset($_SESSION['review_success'])) {
                        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['review_success']) . '</div>';
                        unset($_SESSION['review_success']);
                    }
                    
                    // Display error message if set
                    if(isset($_SESSION['review_error'])) {
                        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['review_error']) . '</div>';
                        unset($_SESSION['review_error']);
                    }
                    ?>

                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <?php
                        // Check if user has already reviewed this room
                        $has_reviewed = false;
                        $check_review_sql = "SELECT id FROM room_reviews WHERE room_id = ? AND user_id = ?";
                        if($stmt = mysqli_prepare($conn, $check_review_sql)) {
                            mysqli_stmt_bind_param($stmt, "ii", $room_id, $_SESSION["id"]);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_store_result($stmt);
                            $has_reviewed = mysqli_stmt_num_rows($stmt) > 0;
                            mysqli_stmt_close($stmt);
                        }
                        ?>

                        <?php if(!$has_reviewed): ?>
                            <div class="review-form animate-fade delay-4">
                                <h3>Write a Review</h3>
                                <form action="submit_review.php" method="POST">
                                    <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                                    <div class="form-group">
                                        <label for="rating">Rating</label>
                                        <select class="form-control" name="rating" required>
                                            <option value="">Select Rating</option>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Very Good</option>
                                            <option value="3">3 - Good</option>
                                            <option value="2">2 - Fair</option>
                                            <option value="1">1 - Poor</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="review">Your Review</label>
                                        <textarea class="form-control" name="review" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-check-circle"></i> You have already submitted a review for this room.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-center"><a href="login.php">Login</a> to write a review</p>
                    <?php endif; ?>

                    <div class="reviews-list animate-fade delay-5">
                        <?php
                        // Fetch reviews
                        $reviews_sql = "SELECT r.*, u.username, u.full_name 
                                      FROM room_reviews r 
                                      JOIN users u ON r.user_id = u.id 
                                      WHERE r.room_id = ? 
                                      ORDER BY r.created_at DESC";
                        if($stmt = mysqli_prepare($conn, $reviews_sql)) {
                            mysqli_stmt_bind_param($stmt, "i", $room_id);
                            mysqli_stmt_execute($stmt);
                            $reviews_result = mysqli_stmt_get_result($stmt);
                            
                            while($review = mysqli_fetch_assoc($reviews_result)) {
                                ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <strong><?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?></strong>
                                            <div class="rating">
                                                <?php
                                                for($i = 1; $i <= 5; $i++) {
                                                    echo '<i class="fas fa-star ' . ($i <= $review['rating'] ? 'text-warning' : 'text-muted') . '"></i>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="review-meta">
                                            <div class="review-date">
                                                <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                            </div>
                                            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["id"] == $review['user_id']): ?>
                                                <button class="btn btn-sm btn-edit-review" onclick="editReview(<?php 
                                                    echo htmlspecialchars(json_encode([
                                                        'id' => $review['id'],
                                                        'rating' => $review['rating'],
                                                        'review' => $review['review']
                                                    ])); 
                                                ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-delete-review" onclick="confirmDeleteReview(<?php echo $review['id']; ?>, <?php echo $room_id; ?>)">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="review-content" id="review-content-<?php echo $review['id']; ?>">
                                        <?php echo nl2br(htmlspecialchars($review['review'])); ?>
                                    </div>
                                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["id"] == $review['user_id']): ?>
                                        <div class="edit-form" id="edit-form-<?php echo $review['id']; ?>" style="display: none;">
                                            <form action="update_review.php" method="POST" class="mt-3">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                                                <div class="form-group">
                                                    <label for="edit-rating-<?php echo $review['id']; ?>">Rating</label>
                                                    <select class="form-control" name="rating" id="edit-rating-<?php echo $review['id']; ?>" required>
                                                        <option value="5" <?php echo $review['rating'] == 5 ? 'selected' : ''; ?>>5 - Excellent</option>
                                                        <option value="4" <?php echo $review['rating'] == 4 ? 'selected' : ''; ?>>4 - Very Good</option>
                                                        <option value="3" <?php echo $review['rating'] == 3 ? 'selected' : ''; ?>>3 - Good</option>
                                                        <option value="2" <?php echo $review['rating'] == 2 ? 'selected' : ''; ?>>2 - Fair</option>
                                                        <option value="1" <?php echo $review['rating'] == 1 ? 'selected' : ''; ?>>1 - Poor</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit-review-<?php echo $review['id']; ?>">Your Review</label>
                                                    <textarea class="form-control" name="review" id="edit-review-<?php echo $review['id']; ?>" rows="4" required><?php echo htmlspecialchars($review['review']); ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update Review</button>
                                                <button type="button" class="btn btn-secondary" onclick="cancelEdit(<?php echo $review['id']; ?>)">Cancel</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                            }
                            if(mysqli_num_rows($reviews_result) == 0) {
                                echo '<p class="text-center">No reviews yet</p>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="contact-section animate-fade-up delay-4">
                    <h2 class="section-title">Contact Information</h2>
                    <p>Listed by: <?php echo htmlspecialchars($room["full_name"] ?? $room["username"]); ?></p>
                    
                    <?php if(!empty($room["contact_phone"])): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($room["contact_phone"]); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($room["contact_email"])): ?>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($room["contact_email"]); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($room["contact_whatsapp"])): ?>
                        <div class="contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <span><?php echo htmlspecialchars($room["contact_whatsapp"]); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($room["contact_whatsapp"])): ?>
                        <a href="https://wa.me/<?php echo htmlspecialchars($room["contact_whatsapp"]); ?>" class="contact-button" target="_blank">
                            <i class="fab fa-whatsapp"></i> Contact on WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete Review</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this review? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" action="delete_review.php" method="POST" style="display: inline;">
                    <input type="hidden" name="review_id" id="delete_review_id">
                    <input type="hidden" name="room_id" id="delete_room_id">
                    <button type="submit" class="btn-modal btn-delete">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function editReview(reviewData) {
        // Hide review content and show edit form
        document.getElementById('review-content-' + reviewData.id).style.display = 'none';
        document.getElementById('edit-form-' + reviewData.id).style.display = 'block';
    }

    function cancelEdit(reviewId) {
        // Show review content and hide edit form
        document.getElementById('review-content-' + reviewId).style.display = 'block';
        document.getElementById('edit-form-' + reviewId).style.display = 'none';
    }

    function confirmDeleteReview(reviewId, roomId) {
        document.getElementById('deleteModal').style.display = 'block';
        document.getElementById('delete_review_id').value = reviewId;
        document.getElementById('delete_room_id').value = roomId;
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('deleteModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>
<?php
// Close connection at the end of the file
mysqli_close($conn);
?> 