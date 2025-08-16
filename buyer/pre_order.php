<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'buyer') {
    header("location: ../login.php");
    exit;
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product_details = null;
$error = "";
$success_message = "";
$pickup_points = [];

// Fetch all available pickup points
$sql_points = "SELECT id, name FROM pickup_points";
$result_points = $conn->query($sql_points);
while ($row = $result_points->fetch_assoc()) {
    $pickup_points[] = $row;
}

if ($product_id > 0) {
    $sql = "SELECT p.*, u.name as farmer_name FROM products p JOIN users u ON p.farmer_id = u.id WHERE p.id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product_details = $result->fetch_assoc();
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pre_order'])) {
    $quantity = intval($_POST['quantity']);
    $buyer_id = $_SESSION["user_id"];
    $pickup_point_id = intval($_POST['pickup_point_id']);
    
    if ($product_details) {
        if ($quantity > $product_details['stock_quantity']) {
            $error = "The quantity you requested is not available.";
        } else {
            $price_per_unit = $product_details['base_price'];
            if ($product_details['discount_percentage'] > 0) {
                 $price_per_unit = $price_per_unit * (1 - $product_details['discount_percentage'] / 100);
            }

            $sql = "INSERT INTO pre_orders (buyer_id, product_id, pickup_point_id, quantity, price_per_unit) VALUES (?, ?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("iiidi", $buyer_id, $product_id, $pickup_point_id, $quantity, $price_per_unit);
                if ($stmt->execute()) {
                    $sql_update_stock = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                    if ($stmt_update = $conn->prepare($sql_update_stock)) {
                        $stmt_update->bind_param("ii", $quantity, $product_id);
                        $stmt_update->execute();
                        $stmt_update->close();
                    }
                    $success_message = "Your pre-order for **" . htmlspecialchars($product_details['crop_name']) . "** has been placed successfully! 🎉";
                } else {
                    $error = "Error placing order: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error preparing statement: " . $conn->error;
            }
        }
    } else {
        $error = "Product not found.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Order Crop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="browse_crops.php" class="btn btn-secondary mb-3">← Back to Browse</a>
        <h3 class="mb-4">Pre-Order: <?php echo htmlspecialchars($product_details['crop_name'] ?? 'Crop'); ?></h3>

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

        <?php if ($product_details): ?>
        <div class="card shadow-sm p-4">
            <h5 class="card-title"><?php echo htmlspecialchars($product_details['crop_name']); ?></h5>
            <p class="card-text text-muted">by **<?php echo htmlspecialchars($product_details['farmer_name']); ?>**</p>
            <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item">**Estimated Harvest:** <?php echo htmlspecialchars($product_details['estimated_harvest_start']); ?> - <?php echo htmlspecialchars($product_details['estimated_harvest_end']); ?></li>
                <li class="list-group-item">**Base Price:** $<?php echo number_format($product_details['base_price'], 2); ?> per unit</li>
                <?php if ($product_details['discount_percentage'] > 0): ?>
                <li class="list-group-item">**Pre-order Price:** $<?php echo number_format($product_details['base_price'] * (1 - $product_details['discount_percentage'] / 100), 2); ?> per unit (<?php echo htmlspecialchars($product_details['discount_percentage']); ?>% off)</li>
                <?php endif; ?>
                <li class="list-group-item">**Available Quantity:** <?php echo htmlspecialchars($product_details['stock_quantity']); ?> units</li>
            </ul>

            <form action="" method="post">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity to Order</label>
                    <input type="number" name="quantity" class="form-control" min="1" max="<?php echo htmlspecialchars($product_details['stock_quantity']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="pickup_point_id" class="form-label">Select Pickup Point</label>
                    <select name="pickup_point_id" class="form-control" required>
                        <option value="">-- Choose a Pickup Point --</option>
                        <?php foreach ($pickup_points as $point): ?>
                            <option value="<?php echo htmlspecialchars($point['id']); ?>"><?php echo htmlspecialchars($point['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="pre_order" class="btn btn-success">Confirm Pre-Order</button>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">Product details not found.</div>
        <?php endif; ?>
    </div>
</body>
</html>