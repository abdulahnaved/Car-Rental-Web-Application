<?php
// Include the cars data
$carsData = json_decode(file_get_contents('cars.json'), true); 
$bookingsData = json_decode(file_get_contents('bookings.json'), true);

// Check for GET parameters
$seatCount = isset($_GET['seatCount']) ? (int)$_GET['seatCount'] : 0;
$fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
$toDate = isset($_GET['toDate']) ? $_GET['toDate'] : '';
$carType = isset($_GET['carType']) ? $_GET['carType'] : '';
$minPrice = isset($_GET['minPrice']) ? (int)$_GET['minPrice'] : 0;
$maxPrice = isset($_GET['maxPrice']) ? (int)$_GET['maxPrice'] : PHP_INT_MAX;

// Check if the car and booking data are valid
if (!$carsData || !$bookingsData) {
    echo "Error loading car or booking data!";
    exit;
}

// Function to check if a car is available for the selected dates
function isCarAvailable($carId, $fromDate, $toDate, $bookingsData) {
    foreach ($bookingsData as $booking) {
        if ($booking['car_id'] == $carId) {
            $bookingStart = $booking['start_date'];
            $bookingEnd = $booking['end_date'];

            if (($fromDate >= $bookingStart && $fromDate <= $bookingEnd) || ($toDate >= $bookingStart && $toDate <= $bookingEnd)) {
                return false;
            }
        }
    }
    return true;
}

// Filter the cars based on selected filter values
$filteredCars = array_filter($carsData, function($car) use ($seatCount, $carType, $minPrice, $maxPrice, $fromDate, $toDate, $bookingsData) {
    $seats = (int)$car['passengers'];
    $transmission = $car['transmission'];
    $price = (int)$car['daily_price_huf'];
    $carId = $car['id'];

    // Check if the car is available for the selected date range
    $isAvailable = isCarAvailable($carId, $fromDate, $toDate, $bookingsData);

    return 
        ($seatCount === 0 || $seats === $seatCount) &&
        ($carType === '' || $transmission === $carType) &&
        ($price >= $minPrice && $price <= $maxPrice) &&
        $isAvailable;
});

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="../assets/logo.png" type="image/x-icon">
</head>

<body class="select-none bg-[#1b1b1b]">
    <div class="black">
        <nav class="flex px-6 relative top-0 left-0 bg-[#2c2c2c] w-full p-4 items-center justify-between">
            <div class="text-white font-medium">iKarRental</div>
            <div class="flex gap-7 items-center">
                <a href="./Login.php" class="text-white font-medium">Login</a>
                <a href="./Register.php" class="bg-[#f5c747] p-3 rounded-3xl font-bold cursor-pointer px-7">Registration</a>
            </div>
        </nav>

        <main class="w-full flex flex-col pt-10 px-5 gap-20">
            <div class="flex flex-col gap-7">
                <div class="text-5xl font-bold text-white w-52">Rent cars easily!</div>
            </div>
            <div class="w-full flex justify-end flex-wrap gap-8 items-center">
                <div class="flex gap-3 flex-wrap items-center">
                <form method="GET" action="" class="flex gap-3 flex-wrap items-center">
    <div class="flex flex-col gap-3">
        <div class="flex gap-5 flex-wrap items-center">
            <div class="flex items-center gap-2">
                <button type="submit" name="seatCount" value="<?= max(0, $seatCount - 1) ?>" class="flex items-center justify-center h-10 w-10 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">-</button>
                <div class="flex items-center justify-center h-10 w-28 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg"><?= $seatCount ?></div>
                <button type="submit" name="seatCount" value="<?= $seatCount + 1 ?>" class="flex items-center justify-center h-10 w-10 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">+</button>
                <span class="text-gray-400">Seats</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-gray-400">From</span>
                <input name="fromDate" type="date" value="<?= $fromDate ?>" class="flex items-center justify-center h-10 bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                <span class="text-gray-400">To</span>
                <input name="toDate" type="date" value="<?= $toDate ?>" class="flex items-center justify-center h-10 bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
            </div>
        </div>
        <div class="flex gap-5 items-center flex-wrap justify-end">
            <select name="carType" class="flex items-center justify-center h-10 w-28 border border-[#2c2c2e] bg-transparent rounded-lg text-[#3f3e46] font-bold text-lg">
                <option value="">Gear type</option>
                <option value="Manual" <?= $carType === 'Manual' ? 'selected' : '' ?>>Manual</option>
                <option value="Automatic" <?= $carType === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
            </select>

            <div class="flex items-center gap-2">
                <input name="minPrice" type="number" value="<?= $minPrice ?>" placeholder="Min Price" class="flex items-center justify-center h-10 w-40 px-4 border bg-transparent border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                <span class="text-gray-400">-</span>
                <input name="maxPrice" type="number" value="<?= $maxPrice ?>" placeholder="Max Price" class="flex items-center justify-center h-10 w-40 px-4 border bg-transparent border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
            </div>
        </div>
    </div>
    <!-- Filter button that keeps the seatCount and other values -->
        <button type="submit" class="bg-[#f9c73e] rounded-2xl p-2 px-10 hover:opacity-75 font-bold text-lg hover:text-white">Filter</button>
            <button type="button" onclick="window.location.href='index.php';" class="bg-[#f5c747] rounded-2xl p-2 px-10 hover:opacity-75 font-bold text-lg hover:text-white">
            Reset
        </button>
        </form>

    </div>
</div>

            <div id="add_cars_container1_1" class="flex flex-wrap gap-5 items-center">
                <!-- Filtered cars are displayed here -->
                <?php foreach ($filteredCars as $car): ?>
                    <div class="car-card flex flex-col h-64 w-72 group relative overflow-hidden bg-cover bg-no-repeat bg-top rounded-xl" 
                        data-seats="<?= $car['passengers'] ?>" 
                        data-transmission="<?= $car['transmission'] ?>" 
                        data-price="<?= $car['daily_price_huf'] ?>" 
                        data-image="<?= $car['image'] ?>" 
                        data-brand="<?= $car['brand'] ?>" 
                        data-model="<?= $car['model'] ?>">
                        <img class="image-box peer-[a]" src="<?= $car['image'] ?>" alt="Car Image">
                        <div class="text-white font-extrabold text-3xl absolute right-3 bottom-16"><?= $car['daily_price_huf'] ?> <span>Ft</span></div>
                        <div class="h-64 bg-transparent w-full absolute"></div>
                        <div class="h-16 w-full bg-[#423f4f] absolute bottom-0 flex justify-between px-4 p-2">
                            <div class="flex flex-col">
                                <div class="text-xl text-white"><?= $car['brand'] . " " . $car['model'] ?></div>
                                <div class="text-lg -mt-1 text-[#7f7d89]"><?= $car['passengers'] ?> seats - <?= $car['transmission'] ?></div>
                            </div>
                            <!-- Link to car details page -->
                            <a href="Login.php" class="book-btn bg-[#f1c850] px-4 rounded-xl cursor-pointer font-bold text-xl">Book</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </main>
    </div>
</body>
</html>
