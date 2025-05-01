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

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $location = trim($_POST['location']);
    $contact_phone = trim($_POST['contact_phone']);
    $contact_email = trim($_POST['contact_email']);
    $contact_whatsapp = trim($_POST['contact_whatsapp']);
    
    // Get room features
    $room_type = trim($_POST['room_type']);
    $furnishing = trim($_POST['furnishing']);
    $bathroom_type = trim($_POST['bathroom_type']);
    $parking = isset($_POST['parking']) ? 1 : 0;
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $air_conditioning = isset($_POST['air_conditioning']) ? 1 : 0;
    $balcony = isset($_POST['balcony']) ? 1 : 0;
    $water_supply = isset($_POST['water_supply']) ? 1 : 0;
    
    // Handle file upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image_path = '';
    if(isset($_FILES["room_image"]) && $_FILES["room_image"]["error"] == 0) {
        $target_file = $target_dir . basename($_FILES["room_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["room_image"]["tmp_name"]);
        if($check !== false) {
            // Allow certain file formats
            if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif" ) {
                if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                }
            }
        }
    }
    
    if(empty($title) || empty($description) || empty($price) || empty($location)) {
        $error = "Please fill all required fields.";
    } elseif(!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Prepare an insert statement
        $sql = "INSERT INTO rooms (user_id, title, description, price, location, room_type, furnishing, bathroom_type, parking, wifi, air_conditioning, balcony, water_supply, image_path, contact_phone, contact_email, contact_whatsapp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "issdssssiiiiissss", 
                $_SESSION["id"],
                $title,
                $description,
                $price,
                $location,
                $room_type,
                $furnishing,
                $bathroom_type,
                $parking,
                $wifi,
                $air_conditioning,
                $balcony,
                $water_supply,
                $image_path,
                $contact_phone,
                $contact_email,
                $contact_whatsapp
            );
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to rooms page with success message
                $_SESSION['success_message'] = "Room listed successfully!";
                header("location: rooms.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again later. Error: " . mysqli_error($conn);
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            $error = "Error preparing statement: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Your Room - XRentals</title>
    <link rel="icon" type="image/x-icon" href="logo/head_logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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

        .animate-scale-fade {
            animation: scaleFadeIn 0.7s cubic-bezier(.4,0,.2,1) forwards;
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
        }

        /* Form Styles */
        .list-room-container {
            max-width: 800px;
            margin: 120px auto 50px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            /* animation: fadeInUp 0.6s ease-out forwards; */
            opacity: 0;
            transform: scale(0.96);
            animation: scaleFadeIn 0.7s cubic-bezier(.4,0,.2,1) forwards;
        }

        .list-room-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            transition: border-color 0.3s;
        }

        /* Select Element Styles */
        select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232c3e50' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding: 12px;
            padding-right: 2.5rem;
            cursor: pointer;
            color: var(--text-color);
            background-color: white;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
            transition: border-color 0.3s;
        }

        select.form-control option {
            padding: 12px;
            color: var(--text-color);
            background-color: white;
        }

        select.form-control option:checked {
            background-color: var(--accent-color);
            color: white;
        }

        select.form-control option:hover {
            background-color: var(--light-gray);
        }

        select.form-control option[value=""][disabled] {
            display: none;
        }

        select.form-control:focus {
            border-color: var(--accent-color);
            box-shadow: none;
            outline: none;
        }

        select.form-control:hover {
            border-color: var(--accent-color);
        }

        /* Firefox specific styles */
        @-moz-document url-prefix() {
            select.form-control {
                color: var(--text-color) !important;
                background-color: white !important;
            }
            
            select.form-control option {
                color: var(--text-color);
                background-color: white;
            }
        }

        /* Edge specific styles */
        @supports (-ms-ime-align: auto) {
            select.form-control {
                color: var(--text-color) !important;
                background-color: white !important;
            }
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .custom-control {
            margin-bottom: 10px;
        }

        .custom-control-label {
            color: var(--text-color);
            cursor: pointer;
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .submit-btn {
            background: var(--accent-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-control-file {
            padding: 10px 0;
        }

        small {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .list-room-container {
                margin: 20px;
                padding: 20px;
            }

            .section-title {
                font-size: 1.3rem;
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
                    <a class="nav-link active" href="list_your_room.php">List Your Room</a>
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
        <div class="list-room-container animate-scale-fade" data-aos="fade-up" data-aos-duration="800">
            <h2 class="section-title text-center mb-4 animate-scale-fade" data-aos="fade-up" data-aos-duration="1000">List Your Room</h2>
            
            <?php if(!empty($message)): ?>
                <div class="message" data-aos="fade-up" data-aos-delay="100"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($error)): ?>
                <div class="error" data-aos="fade-up" data-aos-delay="100"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <h3 class="section-title" data-aos="fade-up" data-aos-delay="200">Room Details</h3>
                
                <div class="form-group" data-aos="fade-up" data-aos-delay="300">
                    <label for="title">Room Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                
                <div class="form-group" data-aos="fade-up" data-aos-delay="400">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                </div>
                
                <div class="form-group" data-aos="fade-up" data-aos-delay="500">
                    <label for="price">Price (per month)</label>
                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group" data-aos="fade-up" data-aos-delay="600">
                    <label for="location">Location</label>
                    <input type="text" class="form-control" id="location" name="location" required>
                </div>

                <h3 class="section-title mt-4" data-aos="fade-up" data-aos-delay="700">Room Features</h3>
                
                <div class="form-group" data-aos="fade-up" data-aos-delay="800">
                    <label for="room_type">Room Type</label>
                    <select class="form-control" id="room_type" name="room_type" required>
                        <option value="" disabled selected>Select Room Type</option>
                        <option value="Single Room">Single Room</option>
                        <option value="Double Room">Double Room</option>
                        <option value="Triple Room">Triple Room</option>
                        <option value="1BHK">1BHK</option>
                        <option value="2BHK">2BHK</option>
                        <option value="3BHK">3BHK</option>
                    </select>
                </div>

                <div class="form-group" data-aos="fade-up" data-aos-delay="900">
                    <label for="furnishing">Furnishing Status</label>
                    <select class="form-control" id="furnishing" name="furnishing" required>
                        <option value="" disabled selected>Select Furnishing Status</option>
                        <option value="Fully Furnished">Fully Furnished</option>
                        <option value="Semi Furnished">Semi Furnished</option>
                        <option value="Unfurnished">Unfurnished</option>
                    </select>
                </div>

                <div class="form-group" data-aos="fade-up" data-aos-delay="1000">
                    <label for="bathroom_type">Bathroom Type</label>
                    <select class="form-control" id="bathroom_type" name="bathroom_type" required>
                        <option value="" disabled selected>Select Bathroom Type</option>
                        <option value="Attached">Attached Bathroom</option>
                        <option value="Common">Common Bathroom</option>
                    </select>
                </div>

                <div class="form-group" data-aos="fade-up" data-aos-delay="1100">
                    <label>Amenities</label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="parking" name="parking">
                        <label class="custom-control-label" for="parking">Parking Available</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="wifi" name="wifi">
                        <label class="custom-control-label" for="wifi">WiFi Available</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="air_conditioning" name="air_conditioning">
                        <label class="custom-control-label" for="air_conditioning">Air Conditioning</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="balcony" name="balcony">
                        <label class="custom-control-label" for="balcony">Balcony</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="water_supply" name="water_supply">
                        <label class="custom-control-label" for="water_supply">24/7 Water Supply</label>
                    </div>
                </div>

                <h3 class="section-title mt-4" data-aos="fade-up" data-aos-delay="1200">Contact Information</h3>
                <div class="form-group" data-aos="fade-up" data-aos-delay="1300">
                    <label for="contact_phone">Phone Number</label>
                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" placeholder="Enter your contact number">
                </div>
                
                <div class="form-group" data-aos="fade-up" data-aos-delay="1400">
                    <label for="contact_email">Email Address</label>
                    <input type="email" class="form-control" id="contact_email" name="contact_email" placeholder="Enter your email address">
                </div>
                
                <div class="form-group" data-aos="fade-up" data-aos-delay="1500">
                    <label for="contact_whatsapp">WhatsApp Number (optional)</label>
                    <input type="tel" class="form-control" id="contact_whatsapp" name="contact_whatsapp" placeholder="Enter your WhatsApp number">
                </div>

                <div class="form-group" data-aos="fade-up" data-aos-delay="1600">
                    <label for="room_image">Room Image</label>
                    <input type="file" class="form-control-file" id="room_image" name="room_image" accept="image/*">
                </div>

                <button type="submit" class="submit-btn" data-aos="fade-up" data-aos-delay="1700">List Room</button>
            </form>
        </div>
    </div>

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

        // Add loading animation for form elements
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('input, select, textarea');
            formElements.forEach(element => {
                element.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                    this.style.borderColor = 'var(--accent-color)';
                });
                element.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'none';
                    if (!this.value) {
                        this.style.borderColor = '#eee';
                    }
                });
            });
        });
    </script>
</body>
</html> 