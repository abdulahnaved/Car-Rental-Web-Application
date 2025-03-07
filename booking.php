<?php
session_start();

$carsFile = 'cars.json';
$bookingsFile = 'bookings.json';

if (!file_exists($carsFile)) {
    echo "Cars data file not found!";
    exit;
}

if (!file_exists($bookingsFile)) {
    echo "Bookings file does not exist. Creating a new file...";
    file_put_contents($bookingsFile, json_encode([])); 
    $bookingsData = [];
} else {
    
    $bookingsData = json_decode(file_get_contents($bookingsFile), true);
}

if ($bookingsData === null) {
    echo "Error loading booking data! Please check the bookings.json file format.";
    exit;
}

$carsData = json_decode(file_get_contents($carsFile), true);

if (!$carsData) {
    echo "Error loading car data!";
    exit;
}

$carId = isset($_GET['id']) ? $_GET['id'] : null;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

if (!$carId || !$startDate || !$endDate) {
    echo "Missing car ID or dates!";
    exit;
}

$selectedCar = null;
foreach ($carsData as $car) {
    if ($car['id'] == $carId) {
        $selectedCar = $car;
        break;
    }
}

if (!$selectedCar) {
    echo "Car not found!";
    exit;
}

$bookingConflict = false;
foreach ($bookingsData as $booking) {
    if ($booking['car_id'] == $carId) {
        if (($startDate >= $booking['start_date'] && $startDate <= $booking['end_date']) ||
            ($endDate >= $booking['start_date'] && $endDate <= $booking['end_date']) ||
            ($startDate <= $booking['start_date'] && $endDate >= $booking['end_date'])) {
            $bookingConflict = true;
            break;
        }
    }
}

if ($bookingConflict) {
    echo "This car is already booked for the selected dates!";
    echo '<br><a href="profile.php" class="text-blue-500">Go back to the car selection page</a>';
    exit;
}

$booking = [
    'car_id' => $carId,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'user_id' => $_SESSION['user_id'],
];

$bookingsData[] = $booking;

if (file_put_contents($bookingsFile, json_encode($bookingsData, JSON_PRETTY_PRINT))) {
    echo "Booking successful!";
    echo "<br>Car: " . $selectedCar['brand'] . " " . $selectedCar['model'];
    echo "<br>Start Date: " . $startDate;
    echo "<br>End Date: " . $endDate;
    echo "<br>Total Price: " . ((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24)) * $selectedCar['daily_price_huf'] . " HUF";
    echo '<br><a href="my_bookings.php" class="text-blue-500">View My Bookings</a>';
} else {
    echo "Error saving the booking!";
}
?>
