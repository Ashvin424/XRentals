<?php
// Initialize the session
session_start();

// Include config file
require_once "config/database.php";

// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
$token = $email = "";
$error_msg = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(empty($_GET["token"]) || empty($_GET["email"])) {
        header("location: login.php");
        exit();
    }
    $token = $_GET["token"];
    $email = $_GET["email"];
    
    // Verify token and email
    $sql = "SELECT id, reset_token, reset_token_expires FROM users WHERE email = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $reset_token_hash, $reset_token_expires);
                if(mysqli_stmt_fetch($stmt)) {
                    if(strtotime($reset_token_expires) < time()) {
                        $error_msg = "Password reset link has expired. Please request a new one.";
                    } else if(!password_verify($token, $reset_token_hash)) {
                        $error_msg = "Invalid password reset link.";
                    }
                }
            } else {
                $error_msg = "Invalid password reset link.";
            }
        } else {
            $error_msg = "Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
} else if($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = trim($_POST["token"]);
    $email = trim($_POST["email"]);
    
    // Validate new password
    if(empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before updating the database
    if(empty($new_password_err) && empty($confirm_password_err)) {
        // Verify token and update password
        $sql = "SELECT id, reset_token, reset_token_expires FROM users WHERE email = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $reset_token_hash, $reset_token_expires);
                    if(mysqli_stmt_fetch($stmt)) {
                        if(strtotime($reset_token_expires) < time()) {
                            $error_msg = "Password reset link has expired. Please request a new one.";
                        } else if(!password_verify($token, $reset_token_hash)) {
                            $error_msg = "Invalid password reset link.";
                        } else {
                            // Prepare an update statement
                            $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?";
                            if($stmt2 = mysqli_prepare($conn, $sql)) {
                                // Set parameters
                                $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                                mysqli_stmt_bind_param($stmt2, "si", $param_password, $id);
                                
                                // Attempt to execute
                                if(mysqli_stmt_execute($stmt2)) {
                                    // Password updated successfully. Destroy the session and redirect to login page
                                    session_destroy();
                                    header("location: login.php");
                                    exit();
                                } else {
                                    $error_msg = "Something went wrong. Please try again later.";
                                }
                                mysqli_stmt_close($stmt2);
                            }
                        }
                    }
                } else {
                    $error_msg = "Invalid password reset link.";
                }
            } else {
                $error_msg = "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - XRentals</title>
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

        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            margin: 0 1rem;
            position: relative;
            transition: color 0.3s ease;
            font-size: 1rem;
            padding: 0.5rem 0.8rem;
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

        @media (max-width: 991px) {
            .navbar {
                padding: 0.8rem;
                min-height: 65px;
            }
            
            .navbar-brand img {
                height: 45px;
            }

            .nav-link {
                padding: 0.6rem 0;
                margin: 0;
            }
        }

        .reset-container {
            max-width: 450px;
            margin: 120px auto 50px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            animation: fadeInUp 0.6s ease-out forwards;
        }

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

        .reset-container h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            height: auto;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .invalid-feedback {
            color: var(--secondary-color);
            font-size: 0.875rem;
        }

        .btn-reset {
            background-color: var(--accent-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
        }

        .alert-danger {
            background-color: #fde8e8;
            color: #e53e3e;
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
                    <a class="nav-link" href="rooms.php">Listed Rooms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contactUs.php">Contact</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="reset-container">
            <h2>Create New Password</h2>
            
            <?php 
            if(!empty($error_msg)){
                echo '<div class="alert alert-danger">' . $error_msg . '</div>';
            }
            ?>

            <?php if(empty($error_msg)): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>">
                        <?php if(!empty($new_password_err)): ?>
                            <div class="invalid-feedback"><?php echo $new_password_err; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                        <?php if(!empty($confirm_password_err)): ?>
                            <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-reset">Reset Password</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center mt-4">
                    <a href="reset-password.php" class="btn btn-reset">Request New Reset Link</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 