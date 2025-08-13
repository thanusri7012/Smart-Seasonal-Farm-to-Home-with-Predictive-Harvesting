<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'buyer') {
    header("location: ../login.php");
    exit;
}

$buyer_id = $_SESSION["user_id"];
$pre_orders = [];

$sql = "SELECT p.crop_name, p.estimated_harvest_start, p.farmer_id, u.name AS farmer_name, po.quantity, po.price_per_unit, po.order_date 
        FROM pre_orders po
        JOIN products p ON po.product_id = p.id
        JOIN users u ON p.farmer_id = u.id
        WHERE po.buyer_id = ?
        ORDER BY po.order_date DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pre_orders[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pre-Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <h3 class="mb-4">My Pre-Orders 🛍️</h3>
        <p class="text-muted">Here are all the crops you've pre-ordered. We'll notify you when they are ready for harvest!</p>

        <?php if (count($pre_orders) > 0): ?>
            <div class="list-group">
                <?php foreach ($pre_orders as $order): ?>
                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($order['crop_name']); ?></h5>
                            <small class="text-muted">Ordered: <?php echo date("F j, Y", strtotime($order['order_date'])); ?></small>
                        </div>
                        <p class="mb-1">**Quantity:** <?php echo htmlspecialchars($order['quantity']); ?> units</p>
                        <p class="mb-1">**Price per unit:** $<?php echo number_format($order['price_per_unit'], 2); ?></p>
                        <small>Farmer: **<?php echo htmlspecialchars($order['farmer_name']); ?>** | Estimated Harvest: **<?php echo htmlspecialchars($order['estimated_harvest_start']); ?>**</small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">You have not placed any pre-orders yet. <a href="browse_crops.php">Start Browse now!</a></div>
        <?php endif; ?>
    </div>
</body>
</html>