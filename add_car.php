<?php
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user_name = $isLoggedIn ? $_SESSION['user_name'] : null;

// Initialize validation errors array
$errors = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read the existing car data from the JSON file
    $carsData = json_decode(file_get_contents('cars.json'), true);
    if (!$carsData) {
        $carsData = [];
    }

    // Get the highest ID and increment it
    $lastCar = end($carsData);
    $newId = $lastCar ? $lastCar['id'] + 1 : 1; 

    // Get the form data
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = $_POST['year'];
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $passengers = $_POST['passengers'];
    $daily_price_huf = $_POST['daily_price_huf'];
    $image = trim($_POST['image']);

    // Validation checks
    if (empty($brand)) {
        $errors['brand'] = "Brand is required.";
    }
    if (empty($model)) {
        $errors['model'] = "Model is required.";
    }
    if (empty($year) || !is_numeric($year)) {
        $errors['year'] = "Year is required and must be a valid number.";
    }
    if (empty($transmission)) {
        $errors['transmission'] = "Transmission is required.";
    }
    if (empty($fuel_type)) {
        $errors['fuel_type'] = "Fuel type is required.";
    }
    if (empty($passengers) || !is_numeric($passengers)) {
        $errors['passengers'] = "Passengers is required and must be a valid number.";
    }
    if (empty($daily_price_huf) || !is_numeric($daily_price_huf)) {
        $errors['daily_price_huf'] = "Daily price is required and must be a valid number.";
    }
    if (empty($image) || !filter_var($image, FILTER_VALIDATE_URL)) {
        $errors['image'] = "Image URL is required and must be a valid URL.";
    }

    // If no errors, save the new car data
    if (empty($errors)) {
        $carData = [
            "id" => $newId,
            "brand" => $brand,
            "model" => $model,
            "year" => $year,
            "transmission" => $transmission,
            "fuel_type" => $fuel_type,
            "passengers" => $passengers,
            "daily_price_huf" => $daily_price_huf,
            "image" => $image,
        ];

        $carsData[] = $carData;

        file_put_contents('cars.json', json_encode($carsData, JSON_PRETTY_PRINT));

        // Redirect to the admin dashboard
        header("Location: admin_dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Car - iKarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="../assets/logo.png" type="image/x-icon">
</head>
<body class="select-none bg-[#1b1b1b]">
    <div class="black">
        <nav class="flex px-6 relative top-0 left-0 bg-[#2c2c2c] w-full p-4 items-center justify-between">
            <div class="text-white font-medium">iKarRental</div>
            <div class="flex gap-7 items-center">
                <?php if ($isLoggedIn): ?>
                    <!-- User is logged in -->
                    <div class="relative group">
                        <div class="text-white font-medium cursor-pointer"><?= $user_name ?></div>
                        <div class="dropdown-content absolute right-0 mt-2 bg-[#2c2c2c] p-3 rounded-lg shadow-lg hidden group-hover:block">
                            <a href="my_bookings.php" class="text-white block px-4 py-2">My Bookings</a>
                            <!-- Logout button with redirection to homepage -->
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

        <main class="w-full flex flex-col pt-10 px-5 gap-20">
            <div class="flex flex-col gap-7">
                <div class="text-5xl font-bold text-white w-52">Add a New Car</div>
            </div>

            <!-- Display validation errors -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-500 text-white p-4 rounded-lg mb-4">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Form for adding a car -->
            <form method="POST" class="flex flex-col gap-6 bg-[#2c2c2c] p-8 rounded-xl shadow-lg">
                
                <div class="flex flex-col">
                    <label class="text-white text-lg font-bold">Brand</label>
                    <input type="text" name="brand" class="h-10 bg-transparent border border-white rounded-lg text-[#3f3e46] font-bold text-lg px-4" value="<?= isset($brand) ? htmlspecialchars($brand) : '' ?>">
                </div>

                <div class="flex flex-col">
                    <label class="text-white text-lg font-bold">Model</label>
                    <input type="text" name="model" class="h-10 bg-transparent border border-white rounded-lg text-[#3f3e46] font-bold text-lg px-4" value="<?= isset($model) ? htmlspecialchars($model) : '' ?>">
                </div>

                <div class="flex flex-col">
                    <label class="text-white text-lg font-bold">Year</label>
                    <input type="number" name="year" class="h-10 bg-transparent border border-white rounded-lg text-[#3f3e46] font-bold text-lg px-4" value="<?= isset($year) ? htmlspecialchars($year) : '' ?>">
                </div>

                <div class="flex flex-col">
                    <label class="text-white text-lg font-bold">Transmission</label>
                    <select name="transmission" class="h-10 bg-transparent border border-white rounded-lg text-[#3f3e46] font-bold text-lg px-4">
                        <option value="Manual" <?= isset($transmission) && $transmission == 'Manual' ? 'selected' : '' ?>>Manual</option>
                        <option value="Automatic" <?= isset($transmission) && $transmission == 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-white text-lg font-bold">Fuel Type</label>
                    <select name="fuel_type" class="h-10 bg-transparent border border-white rounded-lg text-[#3f3e46] font-bold text-lg px-4">
                        <option value="Petrol" <?= isset($fuel_type) && $fuel_type == 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                        <option value="Diesel" <?= isset($fuel_type) && $fuel_type == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                        <option value="Electric" <?= isset($fuel_type) && $fuel_type == 'Electric' ? 'selected' : '' ?>>Electric</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-white text-lg font-bold">Passengers</label>
                    <input type="number" name="passengers" class="h-10 bg-transparent border border-white rounded-lg text-[#3f3e46] font-bold text-lg px-4" value="<?= isset($passengers) ? htmlspecialchars($passengers) : '' ?>">
                </div>

                <div class="flex flex-col">
                    <label class="text-white text-lg font-bold">Daily Price (HUF)</label>
                    <input type="number" name="daily_price_huf" class="h-10 bg-transparent border border-white rounded-lg text-[#3f3e46] font-bold text-lg px-4" value="<?= isset($daily_price_huf) ? htmlspecialchars($daily_price_huf) : '' ?>">
                </div>

                <div class="flex flex-col">
                    <label class="text-white text-lg font-bold">Image URL</label>
                    <input type="text" name="image" class="h-10 bg-transparent border border-white rounded-lg text-[#3f3e46] font-bold text-lg px-4" value="<?= isset($image) ? htmlspecialchars($image) : '' ?>">
                </div>

                <button type="submit" class="bg-[#f9c73e] text-white font-bold py-3 rounded-xl mt-4">Add Car</button>
            </form>
        </main>
    </div>
</body>
</html>
