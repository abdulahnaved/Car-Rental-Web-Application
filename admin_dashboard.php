<?php
session_start();

$carsData = json_decode(file_get_contents('cars.json'), true); 

if (!$carsData) {
    echo "Error loading car data!";
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$user_name = $isLoggedIn ? $_SESSION['user_name'] : null;

$filteredCars = $carsData;

// Filter Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['filter'])) {
    // Collect filter values from the form
    $seats = isset($_POST['seats']) ? $_POST['seats'] : null;
    $carType = isset($_POST['carType']) ? $_POST['carType'] : null;
    $minPrice = isset($_POST['minPrice']) ? $_POST['minPrice'] : null;
    $maxPrice = isset($_POST['maxPrice']) ? $_POST['maxPrice'] : null;

    // Filter by Seats
    if ($seats) {
        $filteredCars = array_filter($filteredCars, function($car) use ($seats) {
            return $car['passengers'] == $seats;
        });
    }

    // Filter by Gear Type
    if ($carType) {
        $filteredCars = array_filter($filteredCars, function($car) use ($carType) {
            return $car['transmission'] == $carType;
        });
    }

    // Filter by Price Range
    if ($minPrice) {
        $filteredCars = array_filter($filteredCars, function($car) use ($minPrice) {
            return $car['daily_price_huf'] >= $minPrice;
        });
    }

    if ($maxPrice) {
        $filteredCars = array_filter($filteredCars, function($car) use ($maxPrice) {
            return $car['daily_price_huf'] <= $maxPrice;
        });
    }
}
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
                <?php if ($isLoggedIn): ?>
                    <div class="relative group">
                        <div class="text-white font-medium cursor-pointer"><?= $user_name ?></div>
                        <div class="dropdown-content absolute right-0 mt-2 bg-[#2c2c2c] p-3 rounded-lg shadow-lg hidden group-hover:block">
                            <a href="admin_bookings.php" class="text-white block px-4 py-2">My Bookings</a>
                            <a href="index.php" class="text-white block px-4 py-2">Log Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <button type="submit" name="login" class="text-white font-medium cursor-pointer">Login</button>
                    </form>
                    <form method="POST">
                        <button type="submit" name="register" class="bg-[#f5c747] p-3 rounded-3xl font-bold cursor-pointer px-7">Registration</button>
                    </form>
                <?php endif; ?>
            </div>
        </nav>

        <style>
            .relative:hover .dropdown-content {
                display: block;
            }
        </style>

        <main class="w-full flex flex-col pt-10 px-5 gap-20">
            <div class="flex gap-7">
                <a href="add_car.php" id="addCarButton" class="bg-[#f9c73e] text-white font-bold py-3 px-10 rounded-xl mt-4">Add Car</a>
            </div>

            <!-- Filter Form -->
            <form method="POST" class="w-full flex justify-end flex-wrap gap-8 items-center">
                <div class="flex gap-3 flex-wrap items-center">
                    <div class="flex flex-col gap-3">
                        <div class="flex gap-5 flex-wrap items-center">
                            <div class="flex items-center gap-2">
                                <button type="button" id="minusBtn" class="flex items-center justify-center h-10 w-10 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">-</button>
                                <div id="valueDisplay" class="flex items-center justify-center h-10 w-28 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">0</div>
                                <button type="button" id="plusBtn" class="flex items-center justify-center h-10 w-10 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">+</button>
                                <span class="text-gray-400">Seats</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">From</span>
                                <input name="fromDate" type="date" class="flex items-center justify-center h-10 bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                                <span class="text-gray-400">To</span>
                                <input name="toDate" type="date" class="flex items-center justify-center h-10 bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                            </div>
                        </div>
                        <div class="flex gap-5 items-center flex-wrap justify-end">
                            <select name="carType" class="flex items-center justify-center h-10 w-28 border border-[#2c2c2e] bg-transparent rounded-lg text-[#3f3e46] font-bold text-lg">
                                <option value="">Gear type</option>
                                <option value="Manual">Manual</option>
                                <option value="Automatic">Automatic</option>
                            </select>

                            <div class="flex items-center gap-2">
                                <input name="minPrice" type="number" placeholder="Min Price" class="flex items-center justify-center h-10 w-40 px-4 border bg-transparent border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                                <span class="text-gray-400">-</span>
                                <input name="maxPrice" type="number" placeholder="Max Price" class="flex items-center justify-center h-10 w-40 px-4 border bg-transparent border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Hidden input to hold the seat value -->
                    <input type="hidden" name="seats" id="seatsValue" value="0">

                    <button type="submit" name="filter" class="bg-[#f9c73e] rounded-2xl p-2 px-10 hover:opacity-75 font-bold text-lg hover:text-white">Filter</button>
                    <button id="resetBtn" class="bg-[#e74c3c] rounded-2xl p-2 px-10 hover:opacity-75 font-bold text-lg hover:text-white" onclick="location.href='admin_dashboard.php'">
        Reset
    </button>
                </div>
            </form>

            <div id="add_cars_container1_1" class="flex flex-wrap gap-5 items-center">
                <!-- Display filtered cars -->
                <?php foreach ($filteredCars as $car): ?>
                    <div class="flex flex-col h-64 w-72 group relative overflow-hidden bg-cover bg-no-repeat bg-top rounded-xl">
                        <img class="image-box peer-[a]" src="<?= $car['image'] ?>" alt="Car Image">
                        <div class="text-white font-extrabold text-3xl absolute right-3 bottom-16"><?= $car['daily_price_huf'] ?> <span>Ft</span></div>
                        <div class="h-64 bg-transparent w-full absolute"></div>
                        <div class="h-16 w-full bg-[#423f4f] absolute bottom-0 flex justify-between px-4 p-2">
                            <div class="text-xl text-white car-card-text"><?= $car['brand'] . " " . $car['model'] ?></div>
                            <div class="text-lg -mt-1 text-[#7f7d89] car-card-text"><?= $car['passengers'] ?> seats - <?= $car['transmission'] ?></div>
                            <div class="flex gap-3">
                                <a href="edit_car.php?id=<?= $car['id'] ?>" class="bg-[#f9c73e] text-white p-2 rounded-lg font-bold">Edit</a>
                                <a href="delete_car.php?id=<?= $car['id'] ?>" class="bg-[#e74c3c] text-white p-2 rounded-lg font-bold">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        const plusBtn = document.getElementById('plusBtn');
        const minusBtn = document.getElementById('minusBtn');
        const valueDisplay = document.getElementById('valueDisplay');
        const seatsValue = document.getElementById('seatsValue');

        plusBtn.addEventListener('click', () => {
            let currentValue = parseInt(valueDisplay.innerText);
            currentValue++;
            valueDisplay.innerText = currentValue;
            seatsValue.value = currentValue; 
        });

        minusBtn.addEventListener('click', () => {
            let currentValue = parseInt(valueDisplay.innerText);
            if (currentValue > 0) {
                currentValue--;
                valueDisplay.innerText = currentValue;
                seatsValue.value = currentValue; 
            }
        });
    </script>
</body>

</html>
