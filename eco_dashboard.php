<?php
include 'includes/db_connection.php';

// Fetch eco-metrics data
$data = [];
$sql = "SELECT SUM(miles_saved) AS total_miles_saved, DATE(calculated_at) as date FROM eco_metrics GROUP BY DATE(calculated_at) ORDER BY date ASC";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$conn->close();

$dates = json_encode(array_column($data, 'date'));
$miles_saved = json_encode(array_column($data, 'total_miles_saved'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco-Metrics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            margin-top: 5rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <a href="buyer/dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <h3 class="mb-4">Eco-Metrics Dashboard 🌍</h3>
        <p class="text-muted">Track the positive impact our community is making by saving food miles.</p>

        <div class="card shadow-sm p-4 mb-4">
            <h5 class="card-title">Total Food Miles Saved Over Time</h5>
            <canvas id="milesSavedChart"></canvas>
        </div>
        
        <?php if (empty($data)): ?>
            <div class="alert alert-info">No eco-metrics data available yet. Place an order to see the impact!</div>
        <?php endif; ?>
    </div>

    <script>
        const ctx = document.getElementById('milesSavedChart').getContext('2d');
        const labels = <?php echo $dates; ?>;
        const data = {
            labels: labels,
            datasets: [{
                label: 'Food Miles Saved',
                data: <?php echo $miles_saved; ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                fill: true
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Miles'
                        }
                    }
                }
            }
        };

        new Chart(ctx, config);
    </script>
</body>
</html>