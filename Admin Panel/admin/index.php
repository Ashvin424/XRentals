<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Check if admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">

</head>
<body class="d-flex">
    <div class="sidebar bg-dark text-white p-3" style="width: 280px;">
        <h2 class="mb-4 text-center">Admin Panel</h2>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="index.php" class="nav-link text-white active">Dashboard</a></li>
            <li class="nav-item mb-2"><a href="manage_users.php" class="nav-link text-white">Manage Users</a></li>
            <li class="nav-item mb-2"><a href="manage_rooms.php" class="nav-link text-white">Manage Rooms</a></li>
            <li class="nav-item mb-2"><a href="manage_contact_messages.php" class="nav-link text-white">Contact Messages</a></li>
        </ul>
        <hr>
            </div>
    <div class="content flex-grow-1 p-4">
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h1 class="h3 mb-0">Dashboard</h1>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="admin_logout.php">Sign Out</a></li>
                </ul>
            </div>
        </header>
        <p>This is your admin dashboard. Use the sidebar to navigate.</p>
    </div>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>