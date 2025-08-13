<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'buyer'){
    header("location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Buyer Dashboard 🛒</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="browse_crops.php">Browse Produce</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_preorders.php">My Pre-Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="request_crop.php">Request a Crop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../eco_dashboard.php">Eco-Metrics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pickup_map.php">Pickup Map</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-success" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    Welcome, **<?php echo htmlspecialchars($_SESSION["name"]); ?>**! Check out the latest produce.
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Available Produce</h5>
                        <p class="card-text">Explore and pre-order fresh, seasonal produce directly from local farmers.</p>
                        <a href="browse_crops.php" class="btn btn-success">Browse Crops</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>