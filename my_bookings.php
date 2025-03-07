<?php
session_start();

$carsFile = 'cars.json';
$bookingsFile = 'bookings.json';

if (!file_exists($carsFile) || !file_exists($bookingsFile)) {
    echo "Error loading car or booking data!";
    exit;
}

$carsData = json_decode(file_get_contents($carsFile), true);
$bookingsData = json_decode(file_get_contents($bookingsFile), true);

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo "You must be logged in to view your bookings.";
    exit;
}

$userBookings = array_filter($bookingsData, function ($booking) use ($userId) {
    return $booking['user_id'] == $userId;
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Bookings - iKarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="../assets/logo.png" type="image/x-icon" />
</head>

<body class="select-none bg-[#1b1b1b]">
    <div class="black">
        <!-- Navbar -->
        <nav class="flex px-6 relative top-0 left-0 bg-[#2c2c2c] w-full p-4 items-center justify-between">
            <div class="text-white font-medium">iKarRental</div>
            <div class="flex gap-7 items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- User is logged in -->
                    <div class="relative">
                        <div class="text-white font-medium cursor-pointer" id="dropdown-button"><?= $_SESSION['user_name'] ?></div>
                        <div class="absolute right-0 mt-2 bg-[#2c2c2c] p-3 rounded-lg shadow-lg hidden" id="dropdown-menu">
                            <a href="my_bookings.php" class="text-white block px-4 py-2">My Bookings</a>
                            <a href="index.php" class="text-white block px-4 py-2">Log Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- User is not logged in -->
                    <div id="Login_Page" class="text-white font-medium cursor-pointer">Login</div>
                    <div id="Registration_Page" class="bg-[#f5c747] p-3 rounded-3xl font-bold cursor-pointer px-7">Registration</div>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Welcome Section -->
        <main class="w-full flex flex-col pt-16 gap-20 justify-center px-20">
            <div class="flex gap-7 items-center">
                <div class="flex flex-col gap-1">
                    <div class="text-[#d2d2d2]">Welcome,</div>
                    <div id="userName111" class="text-white font-bold text-3xl">
                        <?= $_SESSION['user_name'] ?>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-5 w-full">
                <div class="text-white font-medium text-lg">My Reservations</div>
                <div id="add_cars" class="flex items-center gap-4 flex-wrap w-full text-white">
                    <?php if (empty($userBookings)): ?>
                        <p class="text-white">You have no bookings yet.</p>
                    <?php else: ?>
                        <?php foreach ($userBookings as $booking): ?>
                            <?php
                            $car = null;
                            foreach ($carsData as $c) {
                                if ($c['id'] == $booking['car_id']) {
                                    $car = $c;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($car): ?>
                                <div class="w-full md:w-1/2 lg:w-1/3 xl:w-1/4 p-4">
                                    <div class="bg-[#2c2c2c] rounded-lg shadow-lg p-4">
                                        <div class="flex gap-4">
                                            <img src="<?= $car['image'] ?>" alt="<?= $car['brand'] . ' ' . $car['model'] ?>" class="w-32 h-32 rounded-lg shadow-md" />
                                            <div class="flex flex-col justify-between">
                                                <p class="text-xl text-white"><?= $car['brand'] ?> <?= $car['model'] ?></p>
                                                <p class="text-sm text-gray-400">Start Date: <?= $booking['start_date'] ?></p>
                                                <p class="text-sm text-gray-400">End Date: <?= $booking['end_date'] ?></p>
                                                <p class="text-sm text-gray-400">Total Price: <?= (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24) * $car['daily_price_huf'] ?> HUF</p>
                                            </div>
                                        </div>
                                        <a href="profile.php" class="mt-4 text-yellow-300 hover:text-yellow-500 p-2 rounded bg-black block text-center">Back to Cars</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        // JavaScript to toggle the dropdown
        document.getElementById('dropdown-button').addEventListener('click', function () {
            var dropdownMenu = document.getElementById('dropdown-menu');
            dropdownMenu.classList.toggle('hidden');
        });
    </script>
</body>

</html>
