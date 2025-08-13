<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a farmer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'farmer') {
    header("location: ../login.php");
    exit;
}

$farmer_id = $_SESSION['user_id'];
$pre_orders = [];

$sql = "SELECT p.crop_name, po.quantity, po.order_date, po.price_per_unit, u.name AS buyer_name 
        FROM pre_orders po
        JOIN products p ON po.product_id = p.id
        JOIN users u ON po.buyer_id = u.id
        WHERE p.farmer_id = ?
        ORDER BY po.order_date DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $farmer_id);
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
    <title>My Crop Pre-Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <h3 class="mb-4">Pre-Orders for My Crops 📝</h3>
        <p class="text-muted">Here are the pre-orders placed by buyers for your crops.</p>

        <?php if (!empty($pre_orders)): ?>
            <div class="list-group">
                <?php foreach ($pre_orders as $order): ?>
                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($order['crop_name']); ?></h5>
                            <small class="text-muted">Ordered: <?php echo date("F j, Y", strtotime($order['order_date'])); ?></small>
                        </div>
                        <p class="mb-1">**Buyer:** <?php echo htmlspecialchars($order['buyer_name']); ?></p>
                        <p class="mb-1">**Quantity:** <?php echo htmlspecialchars($order['quantity']); ?> units</p>
                        <p class="mb-1">**Price per unit:** $<?php echo number_format($order['price_per_unit'], 2); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No pre-orders have been placed for your crops yet.</div>
        <?php endif; ?>
    </div>
</body>
</html>