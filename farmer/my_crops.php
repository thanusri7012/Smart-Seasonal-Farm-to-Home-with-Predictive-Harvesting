<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in and is a farmer
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'farmer') {
    header("location: ../login.php");
    exit;
}

$farmer_id = $_SESSION['user_id'];
$success_message = "";
$error = "";

// Handle form submission for live updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_update'])) {
    $product_id = intval($_POST['product_id']);
    $update_text = trim($_POST['update_text']);
    $image_url = null;

    // Handle file upload
    if (isset($_FILES['update_image']) && $_FILES['update_image']['error'] == 0) {
        $target_dir = "../assets/images/updates/";
        $image_name = basename($_FILES["update_image"]["name"]);
        $target_file = $target_dir . uniqid() . '_' . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["update_image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["update_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error = "File is not an image.";
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO live_updates (product_id, update_text, image_url) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $product_id, $update_text, $image_url);
            if ($stmt->execute()) {
                $success_message = "Your live update has been posted successfully!";
            } else {
                $error = "Error posting update: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Error preparing statement: " . $conn->error;
        }
    }
}

// Fetch all crops for the logged-in farmer
$sql_crops = "SELECT id, crop_name, estimated_harvest_start FROM products WHERE farmer_id = ?";
$crops = [];
if ($stmt_crops = $conn->prepare($sql_crops)) {
    $stmt_crops->bind_param("i", $farmer_id);
    $stmt_crops->execute();
    $result_crops = $stmt_crops->get_result();
    while ($row = $result_crops->fetch_assoc()) {
        $crops[] = $row;
    }
    $stmt_crops->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Crops & Updates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3 class="mb-4">My Crops & Live Updates 🧑‍🌾</h3>

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

        <div class="card shadow-sm p-4 mb-4">
            <h5 class="card-title">Post a Live Update</h5>
            <p class="card-text text-muted">Keep your buyers engaged by sharing updates on crop growth!</p>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="product_id" class="form-label">Select Crop</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">-- Choose a crop --</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?php echo $crop['id']; ?>"><?php echo htmlspecialchars($crop['crop_name']); ?> (Harvest: <?php echo htmlspecialchars($crop['estimated_harvest_start']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="update_text" class="form-label">Update Text</label>
                    <textarea name="update_text" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="update_image" class="form-label">Add a Photo</label>
                    <input type="file" name="update_image" class="form-control">
                </div>
                <button type="submit" name="post_update" class="btn btn-success">Post Update</button>
            </form>
        </div>

        <div class="list-group">
            <?php foreach ($crops as $crop): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($crop['crop_name']); ?></h5>
                        <small class="text-muted">Harvest: <?php echo htmlspecialchars($crop['estimated_harvest_start']); ?></small>
                    </div>
                    <a href="#" class="btn btn-sm btn-info text-white">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>