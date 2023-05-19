  <?php

  include('conn.php');
  // Set the default city
  $city = "Houston";
  // Check if the form is submitted
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the submitted city from the form
    $searchedCity = $_POST["city"];
    // Use the submitted city if it's not empty
    if (!empty($searchedCity)) {
      $city = $searchedCity;
      $apiKey = "546952912a6c4918965100050231905";

      // Delete data from the database
      $sql = "DELETE FROM WeatherData";
      if ($conn->query($sql) === false) {
        echo "Error deleting data: " . $conn->error;
      }
      // Insert data into the database
      for ($i = 1; $i <= 7; $i++) {
        $date = date('Y-m-d', strtotime('-' . $i . ' days'));
        $url = "http://api.weatherapi.com/v1/history.json?key=" . $apiKey . "&q=" . $city . "&dt=" . $date;

        $response = json_decode(file_get_contents($url), true);

        $date = date('Y-m-d', strtotime($response['forecast']['forecastday'][0]['date']));
        $temp = $response['forecast']['forecastday'][0]['hour'][0]['temp_c'];
        $humidity = $response['forecast']['forecastday'][0]['hour'][0]['humidity'];
        $wind_speed = $response['forecast']['forecastday'][0]['hour'][0]['wind_mph'];
        $weather_desc = $response['forecast']['forecastday'][0]['hour'][0]['condition']['text'];
        $icon_url = 'https:' . $response['forecast']['forecastday'][0]['hour'][0]['condition']['icon'];

        $sql = "INSERT INTO WeatherData (date, city, temp, humidity, wind_speed, weather_description)
              VALUES ('$date', '$city', $temp, $humidity, $wind_speed, '$weather_desc')";

        if ($conn->query($sql) === false) {
          echo "Error: " . $sql . "<br>" . $conn->error;
        }
      }
    }

    // Retrieve data from the database
  $sql = "SELECT * FROM WeatherData ORDER BY date='$date' DESC LIMIT 6";

    $result = $conn->query($sql);

    // Display the data in an HTML table
    if ($result->num_rows > 0) {
      echo "<table>";
      echo "<tr><th class='table-header'>Date</th><th class='table-header'>City</th><th class='table-header'>Temperature</th><th class='table-header'>Humidity</th><th class='table-header'>Wind Speed</th><th class='table-header'>Weather Description</th></tr>";
      while ($row = $result->fetch_assoc()) {

        echo "<tr><td>" . $row["date"] . "</td><td>" . $row["city"] . "</td><td>" . $row["temp"] . "</td><td>" . $row["humidity"] . "</td><td>" . $row["wind_speed"] . "</td><td>" . $row["weather_description"] . "</td></tr>";
      }
      echo "</table>";
    } else {
      echo "0 results";
    }
  }
  ?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeatherApp</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
  </head>

  <body>
    <div class="card">
      <div class="search">
      <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="text" class="search-bar" placeholder="Search" name="city">
          <button><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="1.5em" width="1.5em" xmlns="http://www.w3.org/2000/svg">
              <path d="M909.6 854.5L649.9 594.8C690.2 542.7 712 479 712 412c0-80.2-31.3-155.4-87.9-212.1-56.6-56.7-132-87.9-212.1-87.9s-155.5 31.3-212.1 87.9C143.2 256.5 112 331.8 112 412c0 80.1 31.3 155.5 87.9 212.1C256.5 680.8 331.8 712 412 712c67 0 130.6-21.8 182.7-62l259.7 259.6a8.2 8.2 0 0 0 11.6 0l43.6-43.5a8.2 8.2 0 0 0 0-11.6zM570.4 570.4C528 612.7 471.8 636 412 636s-116-23.3-158.4-65.6C211.3 528 188 471.8 188 412s23.3-116.1 65.6-158.4C296 211.3 352.2 188 412 188s116.1 23.2 158.4 65.6S636 352.2 636 412s-23.3 116.1-65.6 158.4z">
              </path>
            </svg></button>
      </div>
      <div class="weather">
      <h2 class="city"><?php echo $city; ?></h2>
      <h1 class="temp"><?php echo $temp; ?>°C</h1>
      <div class="flex">
        <img src="<?php echo $icon_url; ?>" alt="" class="icon" />
        <div class="description"><?php echo $weather_desc; ?></div>
      </div>
      <div class="humidity">Humidity: <?php echo $humidity; ?> %</div>
      <div class="wind">Wind speed: <?php echo $wind_speed; ?> km/h</div>
    </div>
</div>
    </div>
    </form>
    <!-- <script src="script.js" defer></script> -->
  </body>

  </html>