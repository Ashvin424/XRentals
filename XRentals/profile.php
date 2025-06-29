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

// Fetch user data
$user_id = $_SESSION["id"];
$sql = "SELECT * FROM users WHERE id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if($user = mysqli_fetch_assoc($result)){
            // User data fetched successfully
        } else {
            // User not found
            header("location: logout.php");
            exit;
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    mysqli_stmt_close($stmt);
}

// Fetch user's listed rooms
$rooms_sql = "SELECT * FROM rooms WHERE user_id = ?";
$rooms = array();
if($stmt = mysqli_prepare($conn, $rooms_sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        while($room = mysqli_fetch_assoc($result)){
            $rooms[] = $room;
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - XRentals</title>
    <link rel="icon" type="image/x-icon" href="logo/head_logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
            --text-color: #333;
            --light-gray: #f8f9fa;
        }

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
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }

        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 76px;
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

        /* Profile Styles */
        .profile-section {
            padding: 2rem 0;
            margin-top: 2rem;
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .profile-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-image-container {
            flex-shrink: 0;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-color);
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile-info h1 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        .profile-info p {
            color: #666;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
        }

        .profile-actions .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--secondary-color);
            border: none;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        /* Listed Rooms Section */
        .listed-rooms {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .listed-rooms h2 {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .room-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .room-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .room-card-content {
            padding: 1.5rem;
        }

        .room-card h3 {
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .room-card p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .room-card .price {
            color: var(--accent-color);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .room-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            flex: 1;
            text-align: center;
        }

        .btn-edit {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-delete {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-edit:hover, .btn-delete:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .no-rooms {
            text-align: center;
            padding: 3rem 0;
        }

        .no-rooms i {
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .no-rooms h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .no-rooms p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991px) {
            .navbar {
                padding: 0.6rem 1.5rem;
                min-height: 65px;
            }
            
            .navbar-brand img {
                height: 45px;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-actions {
                justify-content: center;
            }

            .profile-image {
                width: 120px;
                height: 120px;
            }

            .nav-link {
                margin: 0;
                padding: 0.6rem 0.5rem;
            }
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
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <img src="<?php echo isset($_SESSION['profile_image']) ? 'uploads/profile_images/' . $_SESSION['profile_image'] : 'uploads/profile_images/default.png'; ?>" 
                             alt="Profile" 
                             style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-color);">
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="profile-container">
            <div class="profile-header animate-scale">
                <div class="profile-image-container">
                    <img src="<?php echo isset($_SESSION['profile_image']) ? 'uploads/profile_images/' . $_SESSION['profile_image'] : 'uploads/profile_images/default.png'; ?>" 
                         alt="Profile Picture" 
                         class="profile-image">
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user["full_name"] ?: $user["username"]); ?></h1>
                    <p class="animate-fade delay-1"><i class="fas fa-user"></i> <?php echo htmlspecialchars($user["username"]); ?></p>
                    <p class="animate-fade delay-1"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user["email"]); ?></p>
                    <?php if(!empty($user["phone_number"])): ?>
                        <p class="animate-fade delay-1"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user["phone_number"]); ?></p>
                    <?php endif; ?>
                    <div class="profile-actions animate-fade delay-2">
                        <a href="update_profile.php" class="btn btn-primary">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </a>
                        <a href="logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <div class="listed-rooms animate-fade-up delay-2">
                <h2>My Listed Rooms</h2>
                <?php if(empty($rooms)): ?>
                    <div class="no-rooms animate-fade delay-3">
                        <i class="fas fa-home fa-3x"></i>
                        <h3>No Rooms Listed Yet</h3>
                        <p>Start listing your rooms to earn extra income!</p>
                        <a href="list_your_room.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> List a Room
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach($rooms as $index => $room): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="room-card animate-fade-up delay-<?php echo min(5, $index + 3); ?>">
                                    <img src="<?php echo !empty($room["image_path"]) ? htmlspecialchars($room["image_path"]) : 'images/default-room.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($room["title"]); ?>">
                                    <div class="room-card-content">
                                        <h3><?php echo htmlspecialchars($room["title"]); ?></h3>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($room["location"]); ?></p>
                                        <p class="price">₹<?php echo number_format($room["price"]); ?> per month</p>
                                        <div class="room-actions">
                                            <a href="edit_room.php?id=<?php echo $room["id"]; ?>" class="btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="delete_room.php?id=<?php echo $room["id"]; ?>" class="btn-delete" 
                                               onclick="return confirm('Are you sure you want to delete this room?')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 