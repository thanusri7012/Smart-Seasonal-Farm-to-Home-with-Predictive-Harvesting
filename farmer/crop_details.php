<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'farmer') {
    header("location: ../login.php");
    exit;
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$crop_details = null;
$live_updates = [];

if ($product_id > 0) {
    // Fetch crop details
    $sql_details = "SELECT * FROM products WHERE id = ?";
    if ($stmt_details = $conn->prepare($sql_details)) {
        $stmt_details->bind_param("i", $product_id);
        $stmt_details->execute();
        $result_details = $stmt_details->get_result();
        $crop_details = $result_details->fetch_assoc();
        $stmt_details->close();
    }

    // Fetch live updates for this crop
    $sql_updates = "SELECT * FROM live_updates WHERE product_id = ? ORDER BY update_date DESC";
    if ($stmt_updates = $conn->prepare($sql_updates)) {
        $stmt_updates->bind_param("i", $product_id);
        $stmt_updates->execute();
        $result_updates = $stmt_updates->get_result();
        while ($row = $result_updates->fetch_assoc()) {
            $live_updates[] = $row;
        }
        $stmt_updates->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="my_crops.php" class="btn btn-secondary mb-3">← Back to My Crops</a>
        <?php if ($crop_details): ?>
            <h3 class="mb-4">Details for <?php echo htmlspecialchars($crop_details['crop_name']); ?> 🌾</h3>
            
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="card-title">Crop Information</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">**Planting Date:** <?php echo htmlspecialchars($crop_details['planting_date']); ?></li>
                    <li class="list-group-item">**Estimated Harvest:** <?php echo htmlspecialchars($crop_details['estimated_harvest_start']); ?> to <?php echo htmlspecialchars($crop_details['estimated_harvest_end']); ?></li>
                    <li class="list-group-item">**Base Price:** $<?php echo number_format($crop_details['base_price'], 2); ?> per unit</li>
                    <li class="list-group-item">**Stock Quantity:** <?php echo htmlspecialchars($crop_details['stock_quantity']); ?> units</li>
                </ul>
            </div>

            <h4 class="mb-3">Live Updates</h4>
            <?php if (!empty($live_updates)): ?>
                <div class="list-group mb-4">
                    <?php foreach ($live_updates as $update): ?>
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Update on: <?php echo date("F j, Y", strtotime($update['update_date'])); ?></h6>
                            </div>
                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($update['update_text'])); ?></p>
                            <?php if ($update['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($update['image_url']); ?>" class="img-fluid rounded mt-2" style="max-height: 200px; object-fit: cover;" alt="Crop update image">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No live updates for this crop yet.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-danger">Crop details not found.</div>
        <?php endif; ?>
    </div>
</body>
</html>