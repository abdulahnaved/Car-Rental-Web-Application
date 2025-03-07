<?php
session_start();

$carsFile = 'cars.json';
$bookingsFile = 'bookings.json';
$usersFile = 'users.json'; 

if (!file_exists($carsFile) || !file_exists($bookingsFile) || !file_exists($usersFile)) {
    echo "Error loading car, booking, or user data!";
    exit;
}

$carsData = json_decode(file_get_contents($carsFile), true);
$bookingsData = json_decode(file_get_contents($bookingsFile), true);
$usersData = json_decode(file_get_contents($usersFile), true); // Load users data

$isAdmin = isset($_SESSION['user_name']) && $_SESSION['user_name'] === 'admin';

if (!$isAdmin) {
    echo "You must be logged in as an admin to view this page.";
    exit;
}

if (isset($_GET['delete_booking_id'])) {
    $bookingId = $_GET['delete_booking_id'];

    $bookingsData = array_filter($bookingsData, function($booking) use ($bookingId) {
        return $booking['car_id'] . '-' . $booking['user_id'] . '-' . $booking['start_date'] . '-' . $booking['end_date'] != $bookingId;
    });

    $bookingsData = array_values($bookingsData);

    file_put_contents($bookingsFile, json_encode($bookingsData, JSON_PRETTY_PRINT));

    echo "Booking has been deleted.";
    header("Location: admin_bookings.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Bookings - iKarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="../assets/logo.png" type="image/x-icon" />
</head>

<body class="bg-[#1b1b1b] text-white">
    <div>
        <nav class="flex px-6 bg-[#2c2c2c] w-full p-4 items-center justify-between">
            <div class="text-white font-medium">iKarRental - Admin</div>
            <div class="flex gap-7 items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- User is logged in -->
                    <div class="relative">
                        <div class="text-white font-medium cursor-pointer" id="dropdown-button"><?= $_SESSION['user_name'] ?></div>
                        <div class="absolute right-0 mt-2 bg-[#2c2c2c] p-3 rounded-lg shadow-lg hidden" id="dropdown-menu">
                            <a href="admin_dashboard.php" class="text-white block px-4 py-2">Admin Dashboard</a>
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

        <!-- Main Content -->
        <main class="w-full flex flex-col pt-16 gap-10 justify-center px-10">
            <div class="flex gap-7 items-center">
                <div class="flex flex-col gap-1">
                    <div class="text-[#d2d2d2]">Welcome,</div>
                    <div class="text-white font-bold text-3xl"><?= $_SESSION['user_name'] ?></div>
                </div>
            </div>

            <!-- Bookings Section -->
            <div class="w-full">
                <div class="text-white font-medium text-lg mb-4">All Reservations</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php if (empty($bookingsData)): ?>
                        <p class="text-white">No bookings found.</p>
                    <?php else: ?>
                        <?php foreach ($bookingsData as $booking): ?>
                            <?php
                            $car = null;
                            foreach ($carsData as $c) {
                                if ($c['id'] == $booking['car_id']) {
                                    $car = $c;
                                    break;
                                }
                            }

                            $userName = null;
                            foreach ($usersData as $user) {
                                if ($user['email'] == $booking['user_id']) {
                                    $userName = $user['full_name'];
                                    break;
                                }
                            }

                            $bookingId = $booking['car_id'] . '-' . $booking['user_id'] . '-' . $booking['start_date'] . '-' . $booking['end_date'];
                            ?>
                            <?php if ($car && $userName): ?>
                                <div class="bg-[#2c2c2c] p-6 rounded-lg shadow-xl">
                                    <div class="flex gap-4">
                                        <img src="<?= $car['image'] ?>" alt="<?= $car['brand'] . ' ' . $car['model'] ?>" class="w-32 h-32 rounded-lg shadow-md" />
                                        <div class="flex flex-col justify-between">
                                            <p class="text-xl font-bold"><?= $car['brand'] ?> <?= $car['model'] ?></p>
                                            <p class="text-sm text-gray-400">User: <?= $userName ?></p>
                                            <p class="text-sm text-gray-400">Start Date: <?= $booking['start_date'] ?></p>
                                            <p class="text-sm text-gray-400">End Date: <?= $booking['end_date'] ?></p>
                                            <p class="text-sm text-gray-400">Total Price: <?= (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24) * $car['daily_price_huf'] ?> HUF</p>
                                        </div>
                                    </div>

                                    <!-- Delete button -->
                                    <form action="admin_bookings.php" method="get">
                                        <input type="hidden" name="delete_booking_id" value="<?= $bookingId ?>" />
                                        <button type="submit" class="mt-4 text-white bg-red-500 hover:bg-red-700 p-2 rounded block w-full text-center">
                                            Delete Booking
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript to toggle the dropdown -->
    <script>
        document.getElementById('dropdown-button').addEventListener('click', function () {
            var dropdownMenu = document.getElementById('dropdown-menu');
            dropdownMenu.classList.toggle('hidden');
        });
    </script>
</body>

</html>
