<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'buyer') {
    header("location: ../login.php");
    exit;
}

$farmer_id = $_SESSION["user_id"];

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
<body class="bg-light">
    <div class="container mt-5">
        <h3 class="mb-4">Available & Upcoming Produce 🛒</h3>
        <p class="text-muted">Explore fresh produce and pre-order to get the best prices and help farmers with planning.</p>

        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $current_price = $row['base_price'];
                    $discount_text = "";
                    if ($row['discount_percentage'] > 0) {
                        $current_price = $current_price * (1 - $row['discount_percentage'] / 100);
                        $discount_text = "<span class='badge bg-warning text-dark ms-2'>".htmlspecialchars($row['discount_percentage'])."% Discount</span>";
                    }

                    echo '<div class="col-md-4 mb-4">';
                    echo '<div class="card shadow-sm h-100">';
                    echo '<div class="card-body d-flex flex-column">';
                    echo '<h5 class="card-title d-flex align-items-center">'.htmlspecialchars($row['crop_name']).$discount_text.'</h5>';
                    echo '<p class="card-text text-muted mb-2">by '.htmlspecialchars($row['farmer_name']).'</p>';
                    echo '<p class="card-text mb-1">Harvest: **'.htmlspecialchars($row['estimated_harvest_start']).'**</p>';
                    echo '<p class="card-text mb-1">Price: **$'.number_format($current_price, 2).'** per unit</p>';
                    echo '<p class="card-text mb-3">Available Stock: **'.htmlspecialchars($row['stock_quantity']).'** units</p>';
                    echo '<a href="pre_order.php?id='.$row['id'].'" class="btn btn-success mt-auto">Pre-Order Now</a>';
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