<?php
include 'includes/db_connection.php';

// Fetch pickup points from the database
$pickup_points = [];
$sql = "SELECT name, address, latitude, longitude FROM pickup_points";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $pickup_points[] = $row;
}
$conn->close();

$points_json = json_encode($pickup_points);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup Points Map</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #map {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <a href="buyer/dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
        <h3 class="mb-4">Find a Pickup Hub 🗺️</h3>
        <p class="text-muted">Locate the nearest hub to collect your fresh produce.</p>
        <div id="map" class="shadow-sm"></div>
    </div>

    <script>
        function initMap() {
            const myLatLng = { lat: 40.7128, lng: -74.0060 }; // Default map center (e.g., New York City)
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: myLatLng,
            });

            const pickup_points = <?php echo $points_json; ?>;
            pickup_points.forEach(point => {
                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(point.latitude), lng: parseFloat(point.longitude) },
                    map: map,
                    title: point.name
                });
                
                const infoWindow = new google.maps.InfoWindow({
                    content: `<h6>${point.name}</h6><p>${point.address}</p>`
                });
                
                marker.addListener("click", () => {
                    infoWindow.open(map, marker);
                });
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
</body>
</html>