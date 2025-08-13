<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a farmer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'farmer') {
    header("location: ../login.php");
    exit;
}

$requests = [];

// Query to get a count of each crop request
$sql = "SELECT crop_name, COUNT(*) as request_count FROM crop_requests GROUP BY crop_name ORDER BY request_count DESC";

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
</head>
<body>
    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <h3 class="mb-4">Buyer Crop Requests 🥕</h3>
        <p class="text-muted">Buyers are looking for these crops! Use this data to help decide what to plant next season.</p>

        <?php if (!empty($requests)): ?>
            <div class="list-group">
                <?php foreach ($requests as $request): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <h5 class="mb-1"><?php echo htmlspecialchars($request['crop_name']); ?></h5>
                        <span class="badge bg-success rounded-pill"><?php echo htmlspecialchars($request['request_count']); ?> requests</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No crop requests have been made yet.</div>
        <?php endif; ?>
    </div>
</body>
</html>