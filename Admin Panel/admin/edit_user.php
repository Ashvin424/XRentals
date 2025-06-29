<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Check if admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("location: admin_login.php");
    exit;
}

require_once __DIR__ . '/config/database.php';

$user = null;
$error_message = '';
$success_message = '';

// Check if user ID is provided in the URL
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $user_id = trim($_GET['id']);

    // Fetch user details
    $sql = "SELECT id, username, email FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $user_id;
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
            } else {
                $error_message = "User not found.";
            }
        } else {
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}

// Handle form submission for updating user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    // Basic validation
    if (empty($username) || empty($email)) {
        $error_message = 'Please fill all required fields.';
    } else {
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $param_username, $param_email, $param_id);
            $param_username = $username;
            $param_email = $email;
            $param_id = $user_id;

            if ($stmt->execute()) {
                $success_message = "User updated successfully!";
                // Refresh user data after update
                $sql = "SELECT id, username, email FROM users WHERE id = ?";
                if ($stmt_refresh = $conn->prepare($sql)) {
                    $stmt_refresh->bind_param("i", $param_id);
                    $stmt_refresh->execute();
                    $result_refresh = $stmt_refresh->get_result();
                    $user = $result_refresh->fetch_assoc();
                    $stmt_refresh->close();
                }
            } else {
                $error_message = "Error updating user: " . $stmt->error;
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
    <title><?php echo SITE_NAME; ?> - Edit User</title>
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
            <li class="nav-item mb-2"><a href="manage_rooms.php" class="nav-link text-white">Manage Rooms</a></li>
            <li class="nav-item mb-2"><a href="manage_contact_messages.php" class="nav-link text-white">Contact Messages</a></li>
        </ul>
    </div>
    <div class="content flex-grow-1 p-4">
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h1 class="h3 mb-0">Edit User</h1>
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
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                User ID not specified or user not found.
            </div>
        <?php endif; ?>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>