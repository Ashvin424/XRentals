<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config/database.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XRentals - Find Your Perfect Place</title>
    <link rel="icon" type="image/x-icon" href="logo/head_logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
            --text-color: #333;
            --light-gray: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            line-height: 1.6;
            color: var(--text-color);
        }

        /* Updated Navbar Styles */
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
            transform: none;
        }

        .navbar-toggler {
            border: none;
            padding: 0.8rem;
        }

        .navbar-toggler-icon {
            width: 1.3em;
            height: 1.3em;
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

            .hero {
                margin-top: 65px;
            }
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/container_bg.jpg');
            background-size: cover;
            background-position: center;
            height: 80vh;
            display: flex;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            animation: fadeIn 1s ease-out;
            margin-top: 75px;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .cta-button {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: pulse 2s infinite;
        }

        .cta-button:hover {
            background-color: #2980b9;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: none;
        }

        /* Featured Rooms Section */
        .featured-section {
            padding: 5rem 0;
            background-color: var(--light-gray);
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 2px;
            background: var(--accent-color);
            bottom: -10px;
            left: 25%;
            transform: scaleX(0);
            animation: lineExpand 0.8s ease forwards;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
        }

        .room-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
            opacity: 0;
            transform: scale(0.96);
            animation: scaleFadeIn 0.7s cubic-bezier(.4,0,.2,1) forwards;
        }

        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .room-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
            transition: opacity 0.3s ease;
            opacity: 0;
            transform: scale(1);
            transition: transform 0.6s ease;
        }

        .room-image.loaded {
            opacity: 1;
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
            animation: slideIn 0.5s ease-out;
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

        /* About Section */
        .about-section {
            padding: 5rem 0;
            background: white;
        }

        .about-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        /* Footer */
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 1.5rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .footer-section {
            opacity: 0;
            transform: scale(0.96);
            animation: scaleFadeIn 0.7s cubic-bezier(.4,0,.2,1) forwards;
        }

        .footer-section:nth-child(1) { animation-delay: 0.1s; }
        .footer-section:nth-child(2) { animation-delay: 0.2s; }
        .footer-section:nth-child(3) { animation-delay: 0.3s; }
        .footer-section:nth-child(4) { animation-delay: 0.4s; }

        .footer-section h3 {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 0.8rem;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: var(--accent-color);
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links img {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }

        .social-links img:hover {
            transform: translateY(-5px);
        }

        .copyright {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .navbar {
                padding: 1rem;
            }

            .featured-section {
                padding: 3rem 0;
            }

            .section-title h2 {
                font-size: 2rem;
            }
        }

        /* Animation Styles */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Mobile responsiveness for animations */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
                transition: none !important;
            }
        }

        /* Enhanced Animation Styles */
        @keyframes fadeInNav {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes lineExpand {
            from {
                transform: scaleX(0);
            }
            to {
                transform: scaleX(1);
            }
        }

        @keyframes scaleFadeIn {
            from {
                opacity: 0;
                transform: scale(0.96);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-scale-fade {
            animation: scaleFadeIn 0.7s cubic-bezier(.4,0,.2,1) forwards;
            opacity: 0;
        }

        .btn {
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .room-image {
            transform: scale(1);
            transition: transform 0.6s ease;
        }

        .room-card:hover .room-image {
            transform: scale(1.05);
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

    <!-- Hero Section -->
    <section class="hero animate-scale-fade" data-aos="fade-up" data-aos-duration="1200" data-aos-delay="200">
        <div class="hero-content">
            <h1>Find Your Next Perfect Place To Live</h1>
            <p>Discover comfortable and affordable rooms for rent in your desired location</p>
            <?php if(!isset($_SESSION['loggedin'])): ?>
                <a href="register.php" class="btn cta-button">Get Started</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured Rooms Section -->
    <section class="featured-section">
        <div class="container">
            <div class="section-title animate-scale-fade" data-aos="fade-up" data-aos-duration="1000">
                <h2>Recently Featured Rooms</h2>
                <p>Discover Our Premium Selection</p>
            </div>
            <div class="row">
                <?php
                $sql = "SELECT r.*, u.username as owner FROM rooms r 
                        JOIN users u ON r.user_id = u.id 
                        ORDER BY r.created_at DESC LIMIT 3";
                $result = mysqli_query($conn, $sql);
                
                if(mysqli_num_rows($result) > 0):
                    $delay = 200;
                    while($row = mysqli_fetch_assoc($result)):
                ?>
                <div class="col-md-4 animate-scale-fade" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>" data-aos-duration="800">
                    <div class="room-card">
                        <img src="<?php echo htmlspecialchars($row['image_path'] ?: 'images/default-room.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($row['title']); ?>" 
                             class="room-image"
                             onload="this.classList.add('loaded')">
                        <div class="room-details">
                            <span class="price-tag">₹<?php echo number_format($row['price'], 2); ?>/mo</span>
                            <h3 class="room-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="room-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></p>
                            <a href="room_details.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-block">View Details</a>
                        </div>
                    </div>
                </div>
                <?php 
                        $delay += 200;
                    endwhile;
                else:
                ?>
                <div class="col-12 text-center">
                    <p>No rooms available for showcase at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4 animate-scale-fade" data-aos="fade-up" data-aos-delay="800">
                <a href="rooms.php" class="btn cta-button">View All Rooms</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section animate-scale-fade" data-aos="fade-up" data-aos-duration="1000" data-aos-offset="200">
        <div class="container">
            <div class="about-content">
                <h2 class="mb-4">About XRentals</h2>
                <p>Welcome to XRentals, your premier destination for finding the perfect place to call home. 
                   We connect renters with a diverse range of properties that suit every lifestyle and budget.</p>
                <p>Our platform makes it easy to discover, compare, and secure your ideal rental space. 
                   Whether you're looking for a cozy room or a spacious apartment, we've got you covered.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer animate-scale-fade" data-aos="fade-up" data-aos-duration="1000" data-aos-offset="200">
        <div class="footer-content">
            <div class="footer-section animate-scale-fade">
                <h3>About Us</h3>
                <p>XRentals is your trusted partner in finding the perfect rental space. We're committed to making your house-hunting journey simple and enjoyable.</p>
            </div>
            <div class="footer-section animate-scale-fade">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="list_your_room.php">List Your Room</a></li>
                    <li><a href="rooms.php">Listed Rooms</a></li>
                    <li><a href="contactUs.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section animate-scale-fade">
                <h3>Contact Info</h3>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> XYZ Street, Bhavnagar, India</li>
                    <li><i class="fas fa-phone"></i> +91 1234567890</li>
                    <li><i class="fas fa-envelope"></i> info@xrentals.com</li>
                </ul>
            </div>
            <div class="footer-section animate-scale-fade">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><img src="logo/facebook-circle-logo-24.png" alt="Facebook"></a>
                    <a href="#"><img src="logo/twitter-logo-24.png" alt="Twitter"></a>
                    <a href="#"><img src="logo/instagram-logo-24.png" alt="Instagram"></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> XRentals. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS with enhanced settings
        AOS.init({
            duration: 800,
            easing: 'ease-out',
            once: true,
            offset: 100,
            delay: 100
        });

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add loading animation for images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.room-image');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.classList.add('loaded');
                });
            });
        });
    </script>
</body>
</html> 