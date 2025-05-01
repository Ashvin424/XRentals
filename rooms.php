<?php
// Initialize the session
session_start();

// Include config file
require_once "config/database.php";

// Initialize filter variables
$location_search = isset($_GET['location']) ? $_GET['location'] : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'newest';

// Build the SQL query based on filters
$sql = "SELECT r.*, u.username as owner, 
        (SELECT AVG(rating) FROM room_reviews WHERE room_id = r.id) as avg_rating,
        (SELECT COUNT(*) FROM room_reviews WHERE room_id = r.id) as total_reviews 
        FROM rooms r 
        JOIN users u ON r.user_id = u.id 
        WHERE 1=1";

if (!empty($location_search)) {
    $sql .= " AND r.location LIKE '%" . mysqli_real_escape_string($conn, $location_search) . "%'";
}

if (!empty($min_price)) {
    $sql .= " AND r.price >= " . (int)$min_price;
}

if (!empty($max_price)) {
    $sql .= " AND r.price <= " . (int)$max_price;
}

// Add sorting
switch ($sort_by) {
    case 'price_low':
        $sql .= " ORDER BY r.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY r.price DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY r.created_at DESC";
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listed Rooms - XRentals</title>
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

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
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

        .animate-right {
            animation: slideInRight 0.6s ease-out forwards;
            opacity: 0;
        }

        .animate-left {
            animation: slideInLeft 0.6s ease-out forwards;
            opacity: 0;
        }

        /* Animation Delays */
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }

        /* Navbar Styles */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
            --text-color: #333;
            --light-gray: #f8f9fa;
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

        /* Special styling for login button */
        .nav-link.btn.btn-primary {
            color: white !important;
            background-color: var(--accent-color);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        /* Mobile responsive navbar */
        @media (max-width: 991.98px) {
            .navbar {
                padding: 0.6rem 1.5rem;
                min-height: 65px;
            }

            .navbar-brand img {
                height: 45px;
            }

            .nav-link {
                margin: 0;
                padding: 0.6rem 0.5rem;
            }

            .navbar-toggler {
                padding: 0.4rem;
                border: none;
            }

            .navbar-toggler:focus {
                outline: none;
                box-shadow: none;
            }

            .navbar-toggler-icon {
                width: 1.2em;
                height: 1.2em;
            }
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

        /* Adjust main content for fixed navbar */
        .main-content {
            margin-top: 90px;
        }

        /* Rooms List Styles */
        .rooms-container {
            padding-top: 100px;  /* Add padding to account for fixed navbar */
            padding-bottom: 50px;
            background-color: var(--light-gray);
        }

        .room-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 30px;
        }

        .room-card:hover {
            transform: translateY(-10px);
        }

        .room-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }

        .room-details {
            padding: 1.5rem;
        }

        .price-tag {
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .room-title {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .room-location {
            color: #666;
            margin-bottom: 1rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
        }

        /* Search and Filter Styles */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--primary-color);
            font-weight: 500;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .filter-buttons button {
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: 500;
        }

        .clear-filters {
            background: none;
            border: 1px solid var(--accent-color);
            color: var(--accent-color);
        }

        .apply-filters {
            background: var(--accent-color);
            border: none;
            color: white;
        }

        /* Add these styles to your existing CSS */
        .room-rating {
            margin: 10px 0;
        }

        .stars {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stars i {
            color: #ffc107;
            font-size: 14px;
        }

        .stars .far.fa-star {
            color: #ddd;
        }

        .rating-text {
            margin-left: 8px;
            font-size: 14px;
            color: #666;
        }

        .room-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            margin-bottom: 30px;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .room-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .room-details {
            padding: 20px;
        }

        .price-tag {
            background: var(--accent-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .room-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .room-location {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .room-features {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #666;
        }

        .room-features span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .room-features i {
            color: var(--accent-color);
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

    <!-- Rooms List Section -->
    <div class="rooms-container">
        <div class="container">
            <div class="section-title animate-fade-up">
                <h2>Available Rooms</h2>
                <p>Find your perfect room from our wide selection</p>
            </div>

            <div class="filter-section animate-fade-up delay-2">
                <form class="filter-form" method="GET">
                    <div class="filter-group animate-fade delay-3">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location_search); ?>" placeholder="Enter location">
                    </div>
                    <div class="filter-group animate-fade delay-3">
                        <label for="min_price">Min Price</label>
                        <input type="number" id="min_price" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>" placeholder="Min price">
                    </div>
                    <div class="filter-group animate-fade delay-3">
                        <label for="max_price">Max Price</label>
                        <input type="number" id="max_price" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>" placeholder="Max price">
                    </div>
                    <div class="filter-group animate-fade delay-3">
                        <label for="sort_by">Sort By</label>
                        <select id="sort_by" name="sort_by">
                            <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>
                    <div class="filter-buttons animate-fade delay-4">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="rooms.php" class="btn clear-filters">Clear Filters</a>
                    </div>
                </form>
            </div>
            
            <div class="row">
                <?php
                if($result = mysqli_query($conn, $sql)){
                    if(mysqli_num_rows($result) > 0){
                        $delay = 3;
                        while($room = mysqli_fetch_array($result)){
                            $delay = min(8, $delay + 1); // Increment delay up to max 8
                ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="room-card animate-fade-up delay-<?php echo $delay; ?>">
                                    <img src="<?php echo !empty($room['image_path']) ? htmlspecialchars($room['image_path']) : 'images/default-room.jpg'; ?>" alt="Room Image" class="room-image">
                                    <div class="room-details">
                                        <div class="price-tag">₹<?php echo number_format($room['price']); ?> per month</div>
                                        <h3 class="room-title"><?php echo htmlspecialchars($room['title']); ?></h3>
                                        <p class="room-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($room['location']); ?></p>
                                        <div class="room-features">
                                            <span><i class="fas fa-home"></i> <?php echo htmlspecialchars($room['room_type']); ?></span>
                                            <span><i class="fas fa-couch"></i> <?php echo htmlspecialchars($room['furnishing']); ?></span>
                                        </div>
                                        <div class="room-rating">
                                            <div class="stars">
                                                <?php
                                                $rating = round($room['avg_rating'], 1);
                                                for($i = 1; $i <= 5; $i++) {
                                                    if($i <= $rating) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } else if($i - 0.5 <= $rating) {
                                                        echo '<i class="fas fa-star-half-alt"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                                ?>
                                                <span class="rating-text">
                                                    <?php 
                                                    if($room['avg_rating']) {
                                                        echo number_format($room['avg_rating'], 1) . ' (' . $room['total_reviews'] . ' reviews)';
                                                    } else {
                                                        echo 'No reviews yet';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <a href="room_details.php?id=<?php echo $room['id']; ?>" class="btn btn-primary mt-3">View Details</a>
                                    </div>
                                </div>
                            </div>
                <?php
                        }
                        mysqli_free_result($result);
                    } else {
                ?>
                        <div class="col-12">
                            <div class="alert alert-info animate-fade text-center" role="alert">
                                No rooms found matching your criteria.
                            </div>
                        </div>
                <?php
                    }
                } else {
                ?>
                    <div class="col-12">
                        <div class="alert alert-danger animate-fade text-center" role="alert">
                            Oops! Something went wrong. Please try again later.
                        </div>
                    </div>
                <?php
                }
                mysqli_close($conn);
                ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 