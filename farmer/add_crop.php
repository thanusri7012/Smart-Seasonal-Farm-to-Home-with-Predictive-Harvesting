<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a farmer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'farmer') {
    header("location: ../login.php");
    exit;
}

$crop_name = $planting_date = $base_price = $stock_quantity = "";
$error = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $farmer_id = $_SESSION["user_id"];
    $crop_name = trim($_POST['crop_name']);
    $planting_date = trim($_POST['planting_date']);
    $base_price = trim($_POST['base_price']);
    $stock_quantity = trim($_POST['stock_quantity']);

    // Basic validation
    if (empty($crop_name) || empty($planting_date) || empty($base_price) || empty($stock_quantity)) {
        $error = "All fields are required.";
    } else {
        // Simple harvest prediction logic (e.g., assuming a 90-day growth cycle for this example)
        $days_to_harvest = 90;
        $estimated_harvest_start = date('Y-m-d', strtotime($planting_date . ' + ' . $days_to_harvest . ' days'));
        $estimated_harvest_end = date('Y-m-d', strtotime($estimated_harvest_start . ' + 15 days')); // 15-day harvest window

        $sql = "INSERT INTO products (farmer_id, crop_name, planting_date, estimated_harvest_start, estimated_harvest_end, base_price, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issssdi", $farmer_id, $crop_name, $planting_date, $estimated_harvest_start, $estimated_harvest_end, $base_price, $stock_quantity);
            if ($stmt->execute()) {
                $success_message = "Crop added successfully! Harvest predicted for **$estimated_harvest_start** to **$estimated_harvest_end**.";
                $crop_name = $planting_date = $base_price = $stock_quantity = ""; // Clear form fields
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Error preparing statement: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Crop - Smart Farm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3 class="mb-4">Add a New Crop 🥕</h3>
        <p class="text-muted">Enter the details for your new crop. The system will predict the harvest date.</p>

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

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="crop_name" class="form-label">Crop Name</label>
                <input type="text" class="form-control" name="crop_name" value="<?php echo htmlspecialchars($crop_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="planting_date" class="form-label">Planting Date</label>
                <input type="date" class="form-control" name="planting_date" value="<?php echo htmlspecialchars($planting_date); ?>" required>
            </div>
            <div class="mb-3">
                <label for="base_price" class="form-label">Base Price per Unit ($)</label>
                <input type="number" step="0.01" class="form-control" name="base_price" value="<?php echo htmlspecialchars($base_price); ?>" required>
            </div>
            <div class="mb-3">
                <label for="stock_quantity" class="form-label">Estimated Quantity (Units)</label>
                <input type="number" class="form-control" name="stock_quantity" value="<?php echo htmlspecialchars($stock_quantity); ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Add Crop & Predict Harvest</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>