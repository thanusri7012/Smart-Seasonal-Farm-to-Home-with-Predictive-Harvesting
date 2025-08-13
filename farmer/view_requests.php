<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a farmer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'farmer') {
    header("location: ../login.php");
    exit;
}

$requests = [];

// Query to get crop requests along with buyer details
$sql = "SELECT cr.crop_name, u.name AS buyer_name, u.phone_number, cr.request_date
        FROM crop_requests cr
        JOIN users u ON cr.buyer_id = u.id
        ORDER BY cr.request_date DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Crop Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <h3 class="mb-4">Buyer Crop Requests 🥕</h3>
        <p class="text-muted">Buyers are looking for these crops! Use this data to help decide what to plant next season.</p>

        <?php if (!empty($requests)): ?>
            <div class="list-group">
                <?php foreach ($requests as $request): ?>
                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($request['crop_name']); ?></h5>
                            <small class="text-muted">Requested on: <?php echo date("F j, Y", strtotime($request['request_date'])); ?></small>
                        </div>
                        <p class="mb-1">**Buyer:** <?php echo htmlspecialchars($request['buyer_name']); ?></p>
                        <p class="mb-1">**Contact:** <?php echo htmlspecialchars($request['phone_number']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No crop requests have been made yet.</div>
        <?php endif; ?>
    </div>
</body>
</html>