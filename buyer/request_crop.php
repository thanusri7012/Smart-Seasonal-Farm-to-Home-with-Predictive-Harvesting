<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'buyer') {
    header("location: ../login.php");
    exit;
}

$success_message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $buyer_id = $_SESSION["user_id"];
    $crop_name = trim($_POST['crop_name']);
    
    if (empty($crop_name)) {
        $error = "Please enter a crop name.";
    } else {
        $sql = "INSERT INTO crop_requests (buyer_id, crop_name) VALUES (?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $buyer_id, $crop_name);
            if ($stmt->execute()) {
                $success_message = "Your request for **" . htmlspecialchars($crop_name) . "** has been submitted! Farmers will be notified. 🧑‍🌾";
            } else {
                $error = "Error submitting request: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Error preparing statement: " . $conn->error;
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
    <title>Request a Crop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <h3 class="mb-4">Community Crop Requests 🗳️</h3>
        <p class="text-muted">Tell us what crops you would like to see in upcoming seasons. Farmers will use this information to plan their planting schedules.</p>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm p-4">
            <h5 class="card-title">Submit a New Crop Request</h5>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="crop_name" class="form-label">Crop Name</label>
                    <input type="text" name="crop_name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Submit Request</button>
            </form>
        </div>
    </div>
</body>
</html>