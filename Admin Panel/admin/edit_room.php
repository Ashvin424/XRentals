<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Check if admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("location: admin_login.php");
    exit;
}

require_once __DIR__ . '/config/database.php';

$room = null;
$error_message = '';
$success_message = '';

// Check if room ID is provided in the URL (for GET) or POST (for form submission)
$room_id = null;
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $room_id = trim($_GET['id']);
} elseif (isset($_POST['room_id']) && !empty(trim($_POST['room_id']))) {
    $room_id = trim($_POST['room_id']);
}

if ($room_id) {
    // Fetch room details
    $sql = "SELECT id, title, description, price, location, room_type, furnishing, bathroom_type, parking, wifi, air_conditioning, balcony, water_supply, image_path, contact_phone, contact_email, contact_whatsapp, status, user_id FROM rooms WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $room_id;
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $room = $result->fetch_assoc();
            } else {
                $error_message = "Room not found.";
            }
        } else {
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}

// Handle form submission for updating room
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $location = $_POST['location'] ?? '';
    $room_type = $_POST['room_type'] ?? '';
    $furnishing = $_POST['furnishing'] ?? '';
    $bathroom_type = $_POST['bathroom_type'] ?? '';
    $parking = isset($_POST['parking']) ? 1 : 0;
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $air_conditioning = isset($_POST['air_conditioning']) ? 1 : 0;
    $balcony = isset($_POST['balcony']) ? 1 : 0;
    $water_supply = isset($_POST['water_supply']) ? 1 : 0;
    $contact_phone = $_POST['contact_phone'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';
    $contact_whatsapp = $_POST['contact_whatsapp'] ?? '';
    $status = $_POST['status'] ?? '';

    // Basic validation
    if (empty($title) || empty($price) || empty($location)) {
        $error_message = 'Please fill all required fields.';
    } else {
        $sql = "UPDATE rooms SET title = ?, description = ?, price = ?, location = ?, room_type = ?, furnishing = ?, bathroom_type = ?, parking = ?, wifi = ?, air_conditioning = ?, balcony = ?, water_supply = ?, contact_phone = ?, contact_email = ?, contact_whatsapp = ?, status = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssdssssiiiiissssi", $param_title, $param_description, $param_price, $param_location, $param_room_type, $param_furnishing, $param_bathroom_type, $param_parking, $param_wifi, $param_air_conditioning, $param_balcony, $param_water_supply, $param_contact_phone, $param_contact_email, $param_contact_whatsapp, $param_status, $param_id);
            $param_title = $title;
            $param_description = $description;
            $param_price = $price;
            $param_location = $location;
            $param_room_type = $room_type;
            $param_furnishing = $furnishing;
            $param_bathroom_type = $bathroom_type;
            $param_parking = $parking;
            $param_wifi = $wifi;
            $param_air_conditioning = $air_conditioning;
            $param_balcony = $balcony;
            $param_water_supply = $water_supply;
            $param_contact_phone = $contact_phone;
            $param_contact_email = $contact_email;
            $param_contact_whatsapp = $contact_whatsapp;
            $param_status = $status;
            $param_id = $room_id;

            if ($stmt->execute()) {
                $success_message = "Room updated successfully!";
                // Refresh room data after update
                $sql = "SELECT id, title, description, price, location, room_type, furnishing, bathroom_type, parking, wifi, air_conditioning, balcony, water_supply, image_path, contact_phone, contact_email, contact_whatsapp, status, user_id FROM rooms WHERE id = ?";
                if ($stmt_refresh = $conn->prepare($sql)) {
                    $stmt_refresh->bind_param("i", $param_id);
                    $stmt_refresh->execute();
                    $result_refresh = $stmt_refresh->get_result();
                    $room = $result_refresh->fetch_assoc();
                    $stmt_refresh->close();
                }
            } else {
                $error_message = "Error updating room: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Edit Room</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">

</head>
<body class="d-flex">
    <div class="sidebar bg-dark text-white p-3" style="width: 280px;">
        <h2 class="mb-4 text-center">Admin Panel</h2>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="index.php" class="nav-link text-white">Dashboard</a></li>
            <li class="nav-item mb-2"><a href="manage_users.php" class="nav-link text-white">Manage Users</a></li>
            <li class="nav-item mb-2"><a href="manage_rooms.php" class="nav-link active">Manage Rooms</a></li>
            <li class="nav-item mb-2"><a href="manage_contact_messages.php" class="nav-link text-white">Contact Messages</a></li>
        </ul>
    </div>
    <div class="content flex-grow-1 p-4">
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h1 class="h3 mb-0">Edit Room</h1>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="admin_logout.php">Sign Out</a></li>
                </ul>
            </div>
        </header>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <?php if ($room): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['id']); ?>">
                <div class="mb-3">
                    <label for="title" class="form-label">Title:</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($room['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($room['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price:</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($room['price']); ?>" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Location:</label>
                    <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($room['location']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="room_type" class="form-label">Room Type:</label>
                    <select class="form-control" id="room_type" name="room_type" required>
                        <option value="Single Room" <?php echo ($room['room_type'] == 'Single Room') ? 'selected' : ''; ?>>Single Room</option>
                        <option value="Double Room" <?php echo ($room['room_type'] == 'Double Room') ? 'selected' : ''; ?>>Double Room</option>
                        <option value="Triple Room" <?php echo ($room['room_type'] == 'Triple Room') ? 'selected' : ''; ?>>Triple Room</option>
                        <option value="1BHK" <?php echo ($room['room_type'] == '1BHK') ? 'selected' : ''; ?>>1BHK</option>
                        <option value="2BHK" <?php echo ($room['room_type'] == '2BHK') ? 'selected' : ''; ?>>2BHK</option>
                        <option value="3BHK" <?php echo ($room['room_type'] == '3BHK') ? 'selected' : ''; ?>>3BHK</option>
                        
                    </select>
                </div>
                <div class="mb-3">
                    <label for="furnishing" class="form-label">Furnishing:</label>
                    <select class="form-control" id="furnishing" name="furnishing" required>
                        <option value="Furnished" <?php echo ($room['furnishing'] == 'Furnished') ? 'selected' : ''; ?>>Furnished</option>
                        <option value="Semi-Furnished" <?php echo ($room['furnishing'] == 'Semi-Furnished') ? 'selected' : ''; ?>>Semi-Furnished</option>
                        <option value="Unfurnished" <?php echo ($room['furnishing'] == 'Unfurnished') ? 'selected' : ''; ?>>Unfurnished</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="bathroom_type" class="form-label">Bathroom Type:</label>
                    <select class="form-control" id="bathroom_type" name="bathroom_type" required>
                        <option value="Attached" <?php echo ($room['bathroom_type'] == 'Attached') ? 'selected' : ''; ?>>Attached</option>
                        <option value="Common" <?php echo ($room['bathroom_type'] == 'Common') ? 'selected' : ''; ?>>Common</option>
                    </select>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="parking" name="parking" value="1" <?php echo $room['parking'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="parking">Parking</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="wifi" name="wifi" value="1" <?php echo $room['wifi'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="wifi">WiFi</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="air_conditioning" name="air_conditioning" value="1" <?php echo $room['air_conditioning'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="air_conditioning">Air Conditioning</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="balcony" name="balcony" value="1" <?php echo $room['balcony'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="balcony">Balcony</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="water_supply" name="water_supply" value="1" <?php echo $room['water_supply'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="water_supply">Water Supply</label>
                </div>
                <div class="mb-3">
                    <label for="contact_phone" class="form-label">Contact Phone:</label>
                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($room['contact_phone']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="contact_email" class="form-label">Contact Email:</label>
                    <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($room['contact_email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Room</button>
            </form>
        <?php else: ?>
            <p>Room details could not be loaded.</p>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>