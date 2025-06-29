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

// Define variables and initialize with empty values
$full_name = $username = $email = $phone_number = "";
$full_name_err = $username_err = $email_err = $phone_err = $profile_image_err = "";
$success_msg = $error_msg = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate full name
    if(empty(trim($_POST["full_name"]))){
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }
    
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_username, $_SESSION["id"]);
            $param_username = trim($_POST["username"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                $error_msg = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    } else {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_email, $_SESSION["id"]);
            $param_email = trim($_POST["email"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                $error_msg = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate phone number
    if(!empty(trim($_POST["phone_number"]))){
        if(!preg_match("/^[0-9]{10}$/", trim($_POST["phone_number"]))){
            $phone_err = "Please enter a valid 10-digit phone number.";
        } else {
            $phone_number = trim($_POST["phone_number"]);
        }
    }

    // Handle profile image upload
    $profile_image = "";
    if(isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0){
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["profile_image"]["name"];
        $filetype = $_FILES["profile_image"]["type"];
        $filesize = $_FILES["profile_image"]["size"];
    
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)){
            $profile_image_err = "Please select a valid image format (JPG, JPEG, PNG).";
        }
    
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize){
            $profile_image_err = "Image size must be less than 5MB.";
        }
    
        // Verify MYME type of the file
        if(in_array($filetype, $allowed)){
            // Create upload directory if it doesn't exist
            if(!file_exists("uploads/profile_images")){
                mkdir("uploads/profile_images", 0777, true);
            }
            
            // Generate unique filename
            $new_filename = uniqid() . "." . $ext;
            $target = "uploads/profile_images/" . $new_filename;
            
            if(move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target)){
                $profile_image = $new_filename;
                
                // Delete old profile image if exists
                if(isset($_SESSION["profile_image"]) && $_SESSION["profile_image"] != "default.png"){
                    $old_image = "uploads/profile_images/" . $_SESSION["profile_image"];
                    if(file_exists($old_image)){
                        unlink($old_image);
                    }
                }
            } else {
                $profile_image_err = "Failed to upload image.";
            }
        } else {
            $profile_image_err = "Invalid file type.";
        }
    }
    
    // Check input errors before updating the database
    if(empty($username_err) && empty($email_err) && empty($phone_err) && empty($profile_image_err)){
        // Prepare an update statement
        $sql = "UPDATE users SET username=?, email=?, full_name=?, phone_number=?";
        $params = "ssss";
        $values = array($username, $email, $full_name, $phone_number);
        
        if(!empty($profile_image)){
            $sql .= ", profile_image=?";
            $params .= "s";
            $values[] = $profile_image;
        }
        
        $sql .= " WHERE id=?";
        $params .= "i";
        $values[] = $_SESSION["id"];
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, $params, ...$values);
            
            if(mysqli_stmt_execute($stmt)){
                // Update session variables
                $_SESSION["username"] = $username;
                if(!empty($profile_image)){
                    $_SESSION["profile_image"] = $profile_image;
                }
                $success_msg = "Profile updated successfully!";
            } else {
                $error_msg = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch current user data
$sql = "SELECT * FROM users WHERE id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if($user = mysqli_fetch_assoc($result)){
            if(empty($full_name)) $full_name = $user["full_name"];
            if(empty($username)) $username = $user["username"];
            if(empty($email)) $email = $user["email"];
            if(empty($phone_number)) $phone_number = $user["phone_number"];
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
    <title>Update Profile - XRentals</title>
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
            margin: 0 0.8rem;
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

        .update-profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .profile-form h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            text-align: center;
        }

        .profile-image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1.5rem;
            display: block;
            border: 3px solid var(--accent-color);
        }

        .form-group label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.8rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .custom-file {
            margin-bottom: 1rem;
        }

        .custom-file-label {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.8rem;
        }

        .btn-update {
            background-color: var(--accent-color);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .invalid-feedback {
            color: var(--secondary-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        @media (max-width: 991px) {
            .navbar {
                padding: 0.6rem 1.5rem;
                min-height: 65px;
            }
            
            .navbar-brand img {
                height: 45px;
            }

            .profile-form {
                padding: 1.5rem;
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

    <!-- Update Profile Form -->
    <div class="update-profile-container">
        <div class="profile-form">
            <h2>Update Profile</h2>
            
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <img src="<?php echo isset($_SESSION['profile_image']) ? 'uploads/profile_images/' . $_SESSION['profile_image'] : 'uploads/profile_images/default.png'; ?>" 
                     alt="Profile Picture" 
                     class="profile-image-preview" 
                     id="profileImagePreview">
                
                <div class="form-group">
                    <label>Profile Image</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="profileImage" name="profile_image" accept="image/*" onchange="previewImage(this)">
                        <label class="custom-file-label" for="profileImage">Choose file</label>
                    </div>
                    <?php if(!empty($profile_image_err)): ?>
                        <div class="invalid-feedback d-block"><?php echo $profile_image_err; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($full_name); ?>">
                    <?php if(!empty($full_name_err)): ?>
                        <div class="invalid-feedback"><?php echo $full_name_err; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
                    <?php if(!empty($username_err)): ?>
                        <div class="invalid-feedback"><?php echo $username_err; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                    <?php if(!empty($email_err)): ?>
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone_number" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($phone_number); ?>" placeholder="10-digit number">
                    <?php if(!empty($phone_err)): ?>
                        <div class="invalid-feedback"><?php echo $phone_err; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary btn-update">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Update custom file input label
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });

        // Preview image before upload
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#profileImagePreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html> 