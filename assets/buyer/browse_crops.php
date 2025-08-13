<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'buyer') {
    header("location: ../login.php");
    exit;
}

$farmer_id = $_SESSION["user_id"];

// Fetch available and upcoming products
$sql = "SELECT p.*, u.name as farmer_name FROM products p JOIN users u ON p.farmer_id = u.id WHERE p.estimated_harvest_start >= CURDATE() ORDER BY p.estimated_harvest_start ASC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Crops - Smart Farm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3 class="mb-4">Available & Upcoming Produce 🛒</h3>
        <p class="text-muted">Explore fresh produce and pre-order to get the best prices and help farmers with planning.</p>

        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $days_until_harvest = floor((strtotime($row['estimated_harvest_start']) - strtotime('now')) / (60 * 60 * 24));
                    
                    // Dynamic Pricing: apply a discount for early pre-orders
                    $current_price = $row['base_price'];
                    $discount_text = "";
                    if ($days_until_harvest > 30) {
                        $current_price *= 0.90; // 10% discount for pre-orders over 30 days in advance
                        $discount_text = "<span class='badge bg-warning'>10% Early Bird Discount</span>";
                    }

                    // Display each crop as a card
                    echo '<div class="col-md-4 mb-4">';
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row['crop_name']) . ' ' . $discount_text . '</h5>';
                    echo '<p class="card-text text-muted">by ' . htmlspecialchars($row['farmer_name']) . '</p>';
                    echo '<p class="card-text">Harvest: **' . htmlspecialchars($row['estimated_harvest_start']) . '**</p>';
                    echo '<p class="card-text">Price: **$' . number_format($current_price, 2) . '** per unit</p>';
                    echo '<p class="card-text">Available Stock: ' . htmlspecialchars($row['stock_quantity']) . ' units</p>';
                    echo '<a href="pre_order.php?id=' . $row['id'] . '" class="btn btn-success">Pre-Order Now</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No crops are available at this time. Check back later!</p>';
            }
            $conn->close();
            ?>
        </div>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>