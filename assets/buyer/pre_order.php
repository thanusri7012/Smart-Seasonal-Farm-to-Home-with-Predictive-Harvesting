<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'buyer') {
    header("location: ../login.php");
    exit;
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product_details = null;
$error = "";
$success_message = "";

if ($product_id > 0) {
    // Fetch product details
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
    
    // Check if product details were found
    if ($product_details) {
        $price_per_unit = $product_details['base_price'];
        $days_until_harvest = floor((strtotime($product_details['estimated_harvest_start']) - strtotime('now')) / (60 * 60 * 24));
        if ($days_until_harvest > 30) {
            $price_per_unit *= 0.90; // Apply early-bird discount
        }

        // Insert the pre-order into the database
        $sql = "INSERT INTO pre_orders (buyer_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iidi", $buyer_id, $product_id, $quantity, $price_per_unit);
            if ($stmt->execute()) {
                $success_message = "Your pre-order for **" . htmlspecialchars($product_details['crop_name']) . "** has been placed successfully! 🎉";
            } else {
                $error = "Error placing order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Error preparing statement: " . $conn->error;
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
<body>
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
                <li class="list-group-item">**Pre-order Price:** $<?php echo number_format($product_details['base_price'] * (floor((strtotime($product_details['estimated_harvest_start']) - strtotime('now')) / (60 * 60 * 24)) > 30 ? 0.9 : 1), 2); ?> per unit</li>
                <li class="list-group-item">**Available Quantity:** <?php echo htmlspecialchars($product_details['stock_quantity']); ?> units</li>
            </ul>

            <form action="" method="post">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity to Order</label>
                    <input type="number" name="quantity" class="form-control" min="1" max="<?php echo htmlspecialchars($product_details['stock_quantity']); ?>" required>
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