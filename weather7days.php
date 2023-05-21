<?php
include('conn.php');

// Retrieve the submitted city from the form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $city = $_POST["city"];
} else {
    // If no city is submitted, redirect to the default city
    header("Location: weather.php");
    exit();
}

$sql = "SELECT * FROM WeatherData WHERE city='$city' ORDER BY date DESC LIMIT 7";
echo '<script>console.log("Showing weather from database")</script>';
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<!-- <head> -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WeatherApp - 7 Days</title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Weather for 7 Days - <?php echo $city; ?></h1>

    <?php
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
        <th class='table-header'>Date</th>
        <th class='table-header'>Temperature</th>
        <th class='table-header'>Humidity</th>
        <th class='table-header'>Wind Speed</th>
        <th class='table-header'>Weather Description</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["date"] . "</td><td>"
                . $row["temp"] . " °C" . "</td><td>"
                . $row["humidity"] . " %" . "</td><td>" 
                . $row["wind_speed"] . " km/hr" . "</td><td>"
                . $row["weather_description"] . "</td></tr>";
        }
        echo "</table>";
    } else {
        // displayAlert('');
        echo "No results";
    }
    ?>

    <a href="weather.php">Back to Home</a>
</body>

</html>