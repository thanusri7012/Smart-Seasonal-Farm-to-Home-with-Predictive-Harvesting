<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'farmer'){
    header("location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - Smart Farm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Farmer Dashboard 🧑‍🌾</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="add_crop.php">Add New Crop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">My Crops</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">View Pre-Orders</a>
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
                <div class="alert alert-success" role="alert">
                    Welcome, **<?php echo htmlspecialchars($_SESSION["name"]); ?>**! This is your dashboard.
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">My Crops Overview</h5>
                        <p class="card-text">Manage your crops, add new ones, and track pre-orders.</p>
                        <a href="add_crop.php" class="btn btn-success">Add a New Crop</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>