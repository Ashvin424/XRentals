<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if room ID is provided
if(!isset($_GET['id'])) {
    header("location: profile.php");
    exit;
}

$room_id = $_GET['id'];

// Fetch room details
$sql = "SELECT * FROM rooms WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $room_id, $_SESSION["id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    header("location: profile.php");
    exit;
}

$room = mysqli_fetch_assoc($result);

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $location = trim($_POST["location"]);
    $room_type = trim($_POST["room_type"]);
    $furnishing = trim($_POST["furnishing"]);
    $bathroom_type = trim($_POST["bathroom_type"]);
    $parking = isset($_POST["parking"]) ? 1 : 0;
    $wifi = isset($_POST["wifi"]) ? 1 : 0;
    $air_conditioning = isset($_POST["air_conditioning"]) ? 1 : 0;
    $balcony = isset($_POST["balcony"]) ? 1 : 0;
    $water_supply = isset($_POST["water_supply"]) ? 1 : 0;
    $contact_phone = trim($_POST["contact_phone"]);
    $contact_email = trim($_POST["contact_email"]);
    $contact_whatsapp = trim($_POST["contact_whatsapp"]);
    
    // Handle image upload
    $image_path = $room['image_path']; // Keep existing image by default
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Allow certain file formats
            if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg") {
                if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                }
            }
        }
    }
    
    // Update room in database
    $sql = "UPDATE rooms SET 
            title = ?, 
            description = ?, 
            price = ?, 
            location = ?, 
            room_type = ?,
            furnishing = ?,
            bathroom_type = ?,
            parking = ?,
            wifi = ?,
            air_conditioning = ?,
            balcony = ?,
            water_supply = ?,
            contact_phone = ?, 
            contact_email = ?, 
            contact_whatsapp = ?, 
            image_path = ? 
            WHERE id = ? AND user_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssdssssiiiiissssii", 
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
        $contact_phone, 
        $contact_email, 
        $contact_whatsapp, 
        $image_path, 
        $room_id, 
        $_SESSION["id"]
    );
    
    if(mysqli_stmt_execute($stmt)) {
        header("location: profile.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room - XRentals</title>
    <link rel="icon" type="image/x-icon" href="logo/head_logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
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
            padding: 1rem 2rem;
        }

        .navbar-brand img {
            height: 60px;
            width: auto;
            margin-right: 1rem;
        }

        .nav-link {
            color: var(--primary-color) !important;
            font-weight: 500;
            margin: 0 1rem;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--accent-color) !important;
        }

        .navbar-toggler {
            border: none;
        }

        /* Edit Form Styles */
        .edit-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 15px;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: none;
        }

        .current-image {
            max-width: 300px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .btn-submit {
            background: var(--accent-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 2rem;
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

    <div class="container">
        <div class="edit-container">
            <h1 class="section-title">Edit Room</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $room_id); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Room Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($room['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($room['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (per month)</label>
                    <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($room['price']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($room['location']); ?>" required>
                </div>

                <h3 class="mt-4 mb-3">Room Features</h3>
                <div class="form-group">
                    <label for="room_type">Room Type</label>
                    <select class="form-control" id="room_type" name="room_type" required>
                        <option value="">Select Room Type</option>
                        <option value="Single Room" <?php echo $room['room_type'] == 'Single Room' ? 'selected' : ''; ?>>Single Room</option>
                        <option value="Double Room" <?php echo $room['room_type'] == 'Double Room' ? 'selected' : ''; ?>>Double Room</option>
                        <option value="Triple Room" <?php echo $room['room_type'] == 'Triple Room' ? 'selected' : ''; ?>>Triple Room</option>
                        <option value="1BHK" <?php echo $room['room_type'] == '1BHK' ? 'selected' : ''; ?>>1BHK</option>
                        <option value="2BHK" <?php echo $room['room_type'] == '2BHK' ? 'selected' : ''; ?>>2BHK</option>
                        <option value="3BHK" <?php echo $room['room_type'] == '3BHK' ? 'selected' : ''; ?>>3BHK</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="furnishing">Furnishing Status</label>
                    <select class="form-control" id="furnishing" name="furnishing" required>
                        <option value="">Select Furnishing Status</option>
                        <option value="Fully Furnished" <?php echo $room['furnishing'] == 'Fully Furnished' ? 'selected' : ''; ?>>Fully Furnished</option>
                        <option value="Semi Furnished" <?php echo $room['furnishing'] == 'Semi Furnished' ? 'selected' : ''; ?>>Semi Furnished</option>
                        <option value="Unfurnished" <?php echo $room['furnishing'] == 'Unfurnished' ? 'selected' : ''; ?>>Unfurnished</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bathroom_type">Bathroom Type</label>
                    <select class="form-control" id="bathroom_type" name="bathroom_type" required>
                        <option value="">Select Bathroom Type</option>
                        <option value="Attached" <?php echo $room['bathroom_type'] == 'Attached' ? 'selected' : ''; ?>>Attached Bathroom</option>
                        <option value="Common" <?php echo $room['bathroom_type'] == 'Common' ? 'selected' : ''; ?>>Common Bathroom</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Amenities</label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="parking" name="parking" <?php echo $room['parking'] ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="parking">Parking Available</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="wifi" name="wifi" <?php echo $room['wifi'] ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="wifi">WiFi Available</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="air_conditioning" name="air_conditioning" <?php echo $room['air_conditioning'] ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="air_conditioning">Air Conditioning</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="balcony" name="balcony" <?php echo $room['balcony'] ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="balcony">Balcony</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="water_supply" name="water_supply" <?php echo $room['water_supply'] ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="water_supply">24/7 Water Supply</label>
                    </div>
                </div>

                <h3 class="mt-4 mb-3">Contact Information</h3>
                <div class="form-group">
                    <label for="contact_phone">Phone Number</label>
                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($room['contact_phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Email Address</label>
                    <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($room['contact_email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_whatsapp">WhatsApp Number (optional)</label>
                    <input type="tel" class="form-control" id="contact_whatsapp" name="contact_whatsapp" value="<?php echo htmlspecialchars($room['contact_whatsapp']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="image">Room Image</label>
                    <?php if(!empty($room['image_path'])): ?>
                        <div class="mb-3">
                            <img src="<?php echo htmlspecialchars($room['image_path']); ?>" alt="Current room image" class="current-image">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Leave empty to keep current image</small>
                </div>
                
                <button type="submit" class="btn btn-submit">Update Room</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 