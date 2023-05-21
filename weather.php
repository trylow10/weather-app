<?php
include('conn.php');

// Function to check internet connectivity
function isInternetConnected()
{
  $connected = @fsockopen("www.weatherapi.com", 80);
  if ($connected) {
    fclose($connected);
    return true;
  }
  return false;
}

// Function to display alert message
function displayAlert($message)
{
  echo "<script>alert('$message');</script>";
}

// Function to store current weather in local storage
function storeCurrentWeatherInLocalStorage($city, $temp, $weatherDesc, $humidity, $wind_speed, $icon_url)
{
  echo "
    <script>
      localStorage.setItem('current_city', '$city');
      localStorage.setItem('current_temp', '$temp');
      localStorage.setItem('current_weather_desc', '$weatherDesc');
      localStorage.setItem('current_humidity', '$humidity');
      localStorage.setItem('current_wind_speed', '$wind_speed');
      localStorage.setItem('current_icon_url', '$icon_url');
    </script>
  ";
}

// Function to insert or update weather data in the database
function insertOrUpdateWeatherData($date, $city, $temp, $humidity, $wind_speed, $weather_desc, $icon_url, $conn)
{
  $sql = "SELECT * FROM WeatherData WHERE date='$date' AND city='$city'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    // City already exists for the given date, update the record
    $updateSql = "UPDATE WeatherData SET temp=$temp, humidity=$humidity, wind_speed=$wind_speed, weather_description='$weather_desc', icon_url='$icon_url' WHERE date='$date' AND city='$city'";
    if ($conn->query($updateSql) === false) {
      echo "Error updating record: " . $conn->error;
    }
  } else {
    // City doesn't exist for the given date, insert a new record
    $insertSql = "INSERT INTO WeatherData (date, city, temp, humidity, wind_speed, weather_description, icon_url)
                  VALUES ('$date', '$city', $temp, $humidity, $wind_speed, '$weather_desc', '$icon_url')";
    if ($conn->query($insertSql) === false) {
      echo "Error inserting record: " . $conn->error;
    }
  }
}

// Function to get weather data from API and store in database
function getCurrentWeatherFromAPI($city, $apiKey, $conn)
{
  $url = "http://api.weatherapi.com/v1/current.json?key=" . $apiKey . "&q=" . $city;

  $response = json_decode(file_get_contents($url), true);

  if (isset($response['current'])) {
    $temp = $response['current']['temp_c'];
    $humidity = $response['current']['humidity'];
    $wind_speed = $response['current']['wind_kph'];
    $weatherDesc = $response['current']['condition']['text'];
    $icon_url = $response['current']['condition']['icon'];

    storeCurrentWeatherInLocalStorage($city, $temp, $weatherDesc, $humidity, $wind_speed, $icon_url);

    insertOrUpdateWeatherData(date('Y-m-d'), $city, $temp, $humidity, $wind_speed, $weatherDesc, $icon_url, $conn);
  }

  // Insert data into the database for the past 7 days
  for ($i = 0; $i < 6; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $url = "http://api.weatherapi.com/v1/history.json?key=" . $apiKey . "&q=" . $city . "&dt=" . $date;

    $response = json_decode(file_get_contents($url), true);
    $date = date('Y-m-d', strtotime($response['forecast']['forecastday'][0]['date']));
    $temp = $response['forecast']['forecastday'][0]['hour'][0]['temp_c'];
    $humidity = $response['forecast']['forecastday'][0]['hour'][0]['humidity'];
    $wind_speed = $response['forecast']['forecastday'][0]['hour'][0]['wind_mph'];
    $weather_desc = $response['forecast']['forecastday'][0]['hour'][0]['condition']['text'];
    $icon_url = $response['forecast']['forecastday'][0]['hour'][0]['condition']['icon'];

    insertOrUpdateWeatherData($date, $city, $temp, $humidity, $wind_speed, $weather_desc, $icon_url, $conn);
  }
}

// Check internet connectivity
if (!isInternetConnected()) {
  displayAlert("No internet connection.");
  echo '<script>console.log("Showing weather info from local storage")</script>';

} else {

  $apiKey = "you_api_key_here";
  $defaultCity = "mckinney";
  $city = $defaultCity;

  // Check if the form is submitted or use the default city
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the submitted city from the form
    $searchedCity = $_POST["city"];
    // Use the submitted city if it's not empty
    if (!empty($searchedCity)) {
      $city = $searchedCity;
    }
  }

  // Check if the searched city is in the database
  $sql = "SELECT * FROM WeatherData WHERE city='$city' AND date=CURDATE()";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    // Retrieve current weather from the database
    $row = $result->fetch_assoc();
    $temp = $row['temp'];
    $humidity = $row['humidity'];
    $wind_speed = $row['wind_speed'];
    $weatherDesc = $row['weather_description'];
    $icon_url = $row['icon_url'];

    storeCurrentWeatherInLocalStorage($city, $temp, $weatherDesc, $humidity, $wind_speed, $icon_url);
  } else {
    // displayAlert("City not found in the database.");
    echo '<script>console.log("Retrieving weather data from API")</script>';
    getCurrentWeatherFromAPI($city, $apiKey, $conn);
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
        <button type="submit"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="1.5em" width="1.5em" xmlns="http://www.w3.org/2000/svg">
            <path d="M909.6 854.5L649.9 594.8C690.2 542.7 712 479 712 412c0-80.2-31.3-155.4-87.9-212.1-56.6-56.7-132-87.9-212.1-87.9s-155.5 31.3-212.1 87.9C143.2 256.5 112 331.8 112 412c0 80.1 31.3 155.5 87.9 212.1C256.5 680.8 331.8 712 412 712c67 0 130.6-21.8 182.7-62l259.7 259.6a8.2 8.2 0 0 0 11.6 0l43.6-43.5a8.2 8.2 0 0 0 0-11.6zM570.4 570.4C528 612.7 471.8 636 412 636s-116-23.3-158.4-65.6C211.3 528 188 471.8 188 412s23.3-116.1 65.6-158.4C296 211.3 352.2 188 412 188s116.1 23.2 158.4 65.6S636 352.2 636 412s-23.3 116.1-65.6 158.4z">
            </path>
          </svg></button>
      </form>
    </div>
    <div class="weather">
      <h2 class="city"><?php echo $city; ?></h2>
      <h1 class="temp"><?php echo $temp; ?>°C</h1>
      <div class="flex">
        <img src="<?php echo $icon_url; ?>" alt="" class="icon" />
        <div class="description"><?php echo $weather_desc; ?></div>
      </div>
      <div class="humidity">Humidity: <?php echo $humidity; ?>%</div>
      <div class="wind">Wind speed: <?php echo $wind_speed; ?> km/h</div>
    </div>
  </div>

  <div class="button-container">
    <form method="post" action="weather7days.php">
      <input type="hidden" name="city" value="<?php echo $city; ?>">
      <button type="submit" class="view-button">view last 7 days weather</button>
    </form>
  </div>

  <script>
    // Function to update weather data on the page
    function updateWeatherData(city, temp, weatherDesc, humidity, wind_speed) {
      const cityElement = document.querySelector('.city');
      const tempElement = document.querySelector('.temp');
      const descriptionElement = document.querySelector('.description');
      const humidityElement = document.querySelector('.humidity');
      const windElement = document.querySelector('.wind');

      cityElement.textContent = city;
      tempElement.textContent = temp + ' °C';
      descriptionElement.textContent = weatherDesc;
      humidityElement.textContent = 'Humidity: ' + humidity + '%';
      windElement.textContent = 'Wind speed: ' + wind_speed + ' km/h';
    }

    // Function to update weather icon on the page
    function updateWeatherIcon(iconUrl) {
      const iconElement = document.querySelector('.icon');
      iconElement.src = iconUrl;
    }

    // Check if weather data exists in local storage
    if (localStorage.getItem('current_city')) {
      const city = localStorage.getItem('current_city');
      const temp = localStorage.getItem('current_temp');
      const weatherDesc = localStorage.getItem('current_weather_desc');
      const humidity = localStorage.getItem('current_humidity');
      const wind_speed = localStorage.getItem('current_wind_speed');
      const iconUrl = localStorage.getItem('current_icon_url');
      console.log("Data accessed form local storage")
      updateWeatherData(city, temp, weatherDesc, humidity, wind_speed);
      updateWeatherIcon(iconUrl);
    }
  </script>
</body>

</html>
