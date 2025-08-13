<?php
session_start();
include 'includes/db_connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $email = $phone_number = $user_type = "";
$update_success = "";
$update_error = "";

// Fetch user data
$sql = "SELECT name, email, phone_number, user_type FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name, $email, $phone_number, $user_type);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission for updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']);
    $new_phone = trim($_POST['phone_number']);

    $sql_update = "UPDATE users SET name = ?, phone_number = ? WHERE id = ?";
    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("ssi", $new_name, $new_phone, $user_id);
        if ($stmt_update->execute()) {
            $_SESSION['name'] = $new_name;
            $update_success = "Profile updated successfully!";
            header("Refresh:0"); // Refresh the page to show new data
        } else {
            $update_error = "Error updating profile: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="<?php echo htmlspecialchars($user_type == 'farmer' ? 'farmer/dashboard.php' : 'buyer/dashboard.php'); ?>" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <h3 class="mb-4">My Profile 👤</h3>

        <?php if (!empty($update_success)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $update_success; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($update_error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $update_error; ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm p-4">
            <h5 class="card-title mb-3">Profile Details</h5>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
                    <div class="form-text">Email cannot be changed.</div>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>">
                </div>
                <div class="mb-3">
                    <label for="user_type" class="form-label">Account Type</label>
                    <input type="text" name="user_type" class="form-control" value="<?php echo htmlspecialchars($user_type); ?>" disabled>
                </div>
                <button type="submit" name="update_profile" class="btn btn-success">Update Profile</button>
            </form>
        </div>
    </div>
</body>
</html>