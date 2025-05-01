<?php
// Initialize the session
session_start();

// Include config file
require_once "config/database.php";

// Define variables and initialize with empty values
$name = $email = $subject = $message_text = "";
$name_err = $email_err = $subject_err = $message_err = "";
$message = $error = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate name
    if(empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate subject
    if(empty(trim($_POST["subject"]))) {
        $subject_err = "Please enter a subject.";
    } else {
        $subject = trim($_POST["subject"]);
    }
    
    // Validate message
    if(empty(trim($_POST["message"]))) {
        $message_err = "Please enter your message.";
    } else {
        $message_text = trim($_POST["message"]);
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($email_err) && empty($subject_err) && empty($message_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
         
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_email, $param_subject, $param_message);
            
            // Set parameters
            $param_name = $name;
            $param_email = $email;
            $param_subject = $subject;
            $param_message = $message_text;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                $message = "Thank you for your message! We'll get back to you soon.";
                // Clear the form
                $name = $email = $subject = $message_text = "";
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - XRentals</title>
    <link rel="icon" type="image/x-icon" href="logo/head_logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --light-gray: #f8f9fa;
            --btn-text-color: #ffffff;
            --btn-hover-color: #2980b9;
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

        /* Adjust main content for fixed navbar */
        .contact-section {
            margin-top: 90px;
        }

        /* Contact Form Styles */
        .contact-section {
            padding: 80px 0;
            background-color: var(--light-gray);
        }

        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
        }

        .contact-info {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .contact-info-item i {
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .contact-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-group label {
            color: var(--primary-color);
            font-weight: 500;
        }

        .form-control {
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-1px);
        }

        .btn-primary {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            color: var(--btn-text-color) !important;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--btn-hover-color) !important;
            border-color: var(--btn-hover-color) !important;
        }

        .btn-submit {
            background-color: var(--accent-color);
            color: var(--btn-text-color);
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-block;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn-submit:hover {
            background-color: var(--btn-hover-color);
            color: var(--btn-text-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }

        .btn-submit:active {
            background-color: var(--btn-hover-color);
            color: var(--btn-text-color);
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Form animations */
        .form-group {
            margin-bottom: 1.5rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.2s; }
        .form-group:nth-child(2) { animation-delay: 0.4s; }
        .form-group:nth-child(3) { animation-delay: 0.6s; }
        .form-group:nth-child(4) { animation-delay: 0.8s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
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

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-container">
            <div class="section-title animate-fade-up">
                <h2>Contact Us</h2>
                <p>Get in touch with us for any questions or concerns</p>
            </div>

            <div class="contact-info animate-fade-up delay-2">
                <div class="contact-info-item animate-right delay-3">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h5>Our Location</h5>
                        <p>XYZ Street, Bhavnagar, India</p>
                    </div>
                </div>
                <div class="contact-info-item animate-right delay-4">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h5>Phone Number</h5>
                        <p>+91 1234567890</p>
                    </div>
                </div>
                <div class="contact-info-item animate-right delay-5">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h5>Email Address</h5>
                        <p>info@xrentals.com</p>
                    </div>
                </div>
            </div>

            <div class="contact-form animate-fade-up delay-3">
                <?php if(!empty($message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="animate-fade-up delay-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Your Name</label>
                                <input type="text" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                    id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                    id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" class="form-control <?php echo (!empty($subject_err)) ? 'is-invalid' : ''; ?>" 
                            id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea class="form-control <?php echo (!empty($message_err)) ? 'is-invalid' : ''; ?>" 
                            id="message" name="message" rows="5" required><?php echo isset($message_text) ? htmlspecialchars($message_text) : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-submit animate-fade-up delay-5">
                        <i class="fas fa-paper-plane mr-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 