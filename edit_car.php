<?php
session_start();

$carsData = json_decode(file_get_contents('cars.json'), true); 

if (!$carsData) {
    echo "Error loading car data!";
    exit;
}

if (isset($_GET['id'])) {
    $carId = $_GET['id'];
    $carToEdit = null;

    foreach ($carsData as $car) {
        if ($car['id'] == $carId) {
            $carToEdit = $car;
            break;
        }
    }

    if (!$carToEdit) {
        echo "Car not found!";
        exit;
    }
} else {
    echo "No car ID provided!";
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];  
    $passengers = $_POST['passengers'];
    $daily_price_huf = $_POST['daily_price_huf'];
    $image = trim($_POST['image']);

    if (empty($brand)) {
        $errors['brand'] = "Brand is required.";
    }
    if (empty($model)) {
        $errors['model'] = "Model is required.";
    }
    if (empty($transmission)) {
        $errors['transmission'] = "Transmission is required.";
    }
    if (empty($fuel_type)) {
        $errors['fuel_type'] = "Fuel type is required."; 
    }
    if (empty($passengers) || !is_numeric($passengers) || $passengers <= 0) {
        $errors['passengers'] = "Valid number of passengers is required.";
    }
    if (empty($daily_price_huf) || !is_numeric($daily_price_huf) || $daily_price_huf <= 0) {
        $errors['daily_price_huf'] = "Valid daily price is required.";
    }
    if (empty($image) || !filter_var($image, FILTER_VALIDATE_URL)) {
        $errors['image'] = "Valid image URL is required.";
    }

    if (empty($errors)) {
        $updatedCar = [
            'id' => $carToEdit['id'],
            'brand' => $brand,
            'model' => $model,
            'transmission' => $transmission,
            'fuel_type' => $fuel_type, 
            'passengers' => $passengers,
            'daily_price_huf' => $daily_price_huf,
            'image' => $image
        ];

        foreach ($carsData as &$car) {
            if ($car['id'] == $carId) {
                $car = $updatedCar;
                break;
            }
        }

        if (file_put_contents('cars.json', json_encode($carsData, JSON_PRETTY_PRINT))) {
            header("Location: admin_dashboard.php?updated=true");
            exit;
        } else {
            echo "Error saving updated car data!";
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="select-none bg-[#1b1b1b]">
    <div class="flex flex-col items-center pt-20">
        <h1 class="text-white text-3xl font-bold mb-10">Edit Car</h1>

        <!-- Display Validation Errors -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-500 text-white p-4 rounded-lg mb-4">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-[#2c2c2c] p-8 rounded-lg w-full max-w-lg">
            <div class="mb-4">
                <label for="brand" class="text-white text-lg">Brand</label>
                <input type="text" name="brand" id="brand" value="<?= htmlspecialchars($carToEdit['brand']) ?>"
                    class="mt-2 p-2 w-full bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
            </div>

            <div class="mb-4">
                <label for="model" class="text-white text-lg">Model</label>
                <input type="text" name="model" id="model" value="<?= htmlspecialchars($carToEdit['model']) ?>"
                    class="mt-2 p-2 w-full bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
            </div>

            <div class="mb-4">
                <label for="transmission" class="text-white text-lg">Transmission</label>
                <select name="transmission" id="transmission"
                    class="mt-2 p-2 w-full bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                    <option value="Manual" <?= $carToEdit['transmission'] == 'Manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="Automatic" <?= $carToEdit['transmission'] == 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                </select>
            </div>

            <!-- New Fuel Type Field -->
            <div class="mb-4">
                <label for="fuel_type" class="text-white text-lg">Fuel Type</label>
                <select name="fuel_type" id="fuel_type"
                    class="mt-2 p-2 w-full bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                    <option value="Petrol" <?= isset($carToEdit['fuel_type']) && $carToEdit['fuel_type'] == 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                    <option value="Diesel" <?= isset($carToEdit['fuel_type']) && $carToEdit['fuel_type'] == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                    <option value="Electric" <?= isset($carToEdit['fuel_type']) && $carToEdit['fuel_type'] == 'Electric' ? 'selected' : '' ?>>Electric</option>
                    <option value="Hybrid" <?= isset($carToEdit['fuel_type']) && $carToEdit['fuel_type'] == 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>

                </select>
            </div>

            <div class="mb-4">
                <label for="passengers" class="text-white text-lg">Number of Seats</label>
                <input type="number" name="passengers" id="passengers" value="<?= htmlspecialchars($carToEdit['passengers']) ?>"
                    class="mt-2 p-2 w-full bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
            </div>

            <div class="mb-4">
                <label for="daily_price_huf" class="text-white text-lg">Daily Price (Ft)</label>
                <input type="number" name="daily_price_huf" id="daily_price_huf" value="<?= htmlspecialchars($carToEdit['daily_price_huf']) ?>"
                    class="mt-2 p-2 w-full bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
            </div>

            <div class="mb-4">
                <label for="image" class="text-white text-lg">Image URL</label>
                <input type="url" name="image" id="image" value="<?= htmlspecialchars($carToEdit['image']) ?>"
                    class="mt-2 p-2 w-full bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
            </div>

            <button type="submit" class="bg-[#f9c73e] p-3 rounded-lg text-white font-bold w-full">Update Car</button> 
        </form>
    </div>
</body>
</html>
