<?php
session_start();

$carsData = json_decode(file_get_contents('cars.json'), true);

if (!$carsData) {
    echo "Error loading car data!";
    exit;
}

$carId = isset($_GET['id']) ? $_GET['id'] : null;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$selectedCar = null;

if ($carId) {
    foreach ($carsData as $car) {
        if ($car['id'] == $carId) {
            $selectedCar = $car;
            break;
        }
    }
}

if (!$selectedCar) {
    echo "Car not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Failed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#1b1b1b] flex justify-center items-center min-h-screen text-white">
    <div class="w-full max-w-lg text-center">
        <header class="mb-8">
            <nav class="bg-[#2c2c2e] py-4 px-6 flex justify-between items-center rounded">
                <h1 class="text-xl font-semibold text-white">iKarRental</h1>
                <img src="path/to/avatar.jpg" alt="User Avatar" class="w-10 h-10 rounded-full">
            </nav>
        </header>

        <div class="bg-[#2c2c2e] p-8 rounded-lg shadow-lg">
            <div class="mb-6">
                <div class="w-20 h-20 mx-auto mb-6 bg-[#e53e3e] text-white rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold">Booking failed!</h1>
                <p class="mt-4 text-gray-400">
                    The <?= $selectedCar['brand'] . " " . $selectedCar['model'] ?> is not available in the specified interval from <?= $startDate ?> to <?= $endDate ?>.
                    Try entering a different interval or search for another vehicle.
                </p>
            </div>
            <a href="profile.php" class="block bg-[#f9c73e] py-3 px-6 rounded text-black font-bold mt-6">
                Back to the vehicle side
            </a>
        </div>
    </div>
</body>

</html>
