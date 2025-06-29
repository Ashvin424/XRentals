<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Check if admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("location: admin_login.php");
    exit;
}

require_once __DIR__ . '/config/database.php';

$rooms = [];
$error_message = '';

// Fetch all rooms from the database, including user information
$sql = "SELECT r.id, r.title, r.price, r.created_at, u.username 
        FROM rooms r 
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }
    }
    $result->free();
} else {
    $error_message = "Error: Could not retrieve rooms. " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Manage Rooms</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">

</head>
<body class="d-flex">
    <div class="sidebar bg-dark text-white p-3" style="width: 280px;">
        <h2 class="mb-4 text-center">Admin Panel</h2>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="index.php" class="nav-link text-white">Dashboard</a></li>
            <li class="nav-item mb-2"><a href="manage_users.php" class="nav-link text-white">Manage Users</a></li>
            <li class="nav-item mb-2"><a href="manage_rooms.php" class="nav-link text-white active">Manage Rooms</a></li>
            <li class="nav-item mb-2"><a href="manage_contact_messages.php" class="nav-link text-white">Contact Messages</a></li>
        </ul>
    </div>
    <div class="content flex-grow-1 p-4">
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h1 class="h3 mb-0">Manage Rooms</h1>
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

        <?php if (empty($rooms)): ?>
            <p>No rooms found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Posted By</th>
                            <th>Posted On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['id']); ?></td>
                            <td><?php echo htmlspecialchars($room['title']); ?></td>
                            <td><?php echo htmlspecialchars($room['price']); ?></td>
                            <td><?php echo htmlspecialchars($room['username']); ?></td>
                            <td><?php echo htmlspecialchars($room['created_at']); ?></td>
                            <td class="action-links">
                                <a href="edit_room.php?id=<?php echo $room['id']; ?>">Edit</a>
                                <a href="delete_room.php?id=<?php echo $room['id']; ?>" onclick="return confirm('Are you sure you want to delete this room?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>