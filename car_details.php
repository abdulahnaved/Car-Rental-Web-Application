<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carId = $_POST['car_id'] ?? null;
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;
    $userId = $_SESSION['user_id'] ?? null; 

    if ($carId && $startDate && $endDate && $userId) {
        $newStartDate = new DateTime($startDate);
        $newEndDate = new DateTime($endDate);
        if ($newStartDate > $newEndDate) {
            echo json_encode(['success' => false, 'message' => 'Invalid date range!']);
            exit;
        }

        $bookingsFile = 'bookings.json';

        if (file_exists($bookingsFile) && filesize($bookingsFile) > 0) {
            $bookingsData = json_decode(file_get_contents($bookingsFile), true) ?: [];
        } else {
            $bookingsData = [];
        }

        foreach ($bookingsData as $booking) {
            if ($booking['car_id'] == $carId) {
                $existingStart = new DateTime($booking['start_date']);
                $existingEnd = new DateTime($booking['end_date']);
                if ($newStartDate <= $existingEnd && $newEndDate >= $existingStart) {
                    echo json_encode(['success' => false, 'message' => 'The selected dates are unavailable.']);
                    exit;
                }
            }
        }

        $bookingsData[] = [
            'car_id' => $carId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => $userId,
        ];

        if (file_put_contents($bookingsFile, json_encode($bookingsData, JSON_PRETTY_PRINT))) {
            $newUnavailableDates = [];
            while ($newStartDate <= $newEndDate) {
                $newUnavailableDates[] = $newStartDate->format('Y-m-d');
                $newStartDate->modify('+1 day');
            }

            $carsData = json_decode(file_get_contents('cars.json'), true);
            $selectedCar = null;
            foreach ($carsData as $car) {
                if ($car['id'] == $carId) {
                    $selectedCar = $car;
                    break;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Booking confirmed!',
                'newUnavailableDates' => $newUnavailableDates,
                'carDetails' => $selectedCar,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save booking.']);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }
}

$carsData = json_decode(file_get_contents('cars.json'), true);
$bookingsData = json_decode(file_get_contents('bookings.json'), true);

if (!$carsData || !$bookingsData) {
    die("Error loading data!");
}

$carId = $_GET['id'] ?? null;
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
    die("Car not found!");
}

$unavailableDates = [];
foreach ($bookingsData as $booking) {
    if ($booking['car_id'] == $carId) {
        $startDate = new DateTime($booking['start_date']);
        $endDate = new DateTime($booking['end_date']);
        while ($startDate <= $endDate) {
            $unavailableDates[] = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');
        }
    }
}

$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($selectedCar['brand'] . " " . $selectedCar['model']) ?> - Car Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body class="bg-[#1b1b1b] select-none flex justify-center items-center min-h-screen">
    <div id="back_Admin" class="text-white absolute top-20 left-0 cursor-pointer">
        <a href="profile.php" class="p-2 bg-[#f5c747] text-white text-center rounded-2xl mt-6 inline-block">
            Back
        </a>
    </div>

    <main class="flex gap-3 w-full lg:w-fit flex-col items-center justify-center text-white rounded-lg shadow-lg p-6">
        <div id="CarName" class="w-full lg:text-end text-center text-6xl text-[#e5e5e5]">
            <?= htmlspecialchars($selectedCar['brand'] . " " . $selectedCar['model']) ?>
        </div>

        <div class="flex items-center gap-3 flex-wrap lg:flex-nowrap w-[45%] lg:w-full">
            <img id="img" class="w-[30rem] h-[20rem] shadow-xl border border-[#ffffff34] rounded-3xl" 
                 src="<?= htmlspecialchars($selectedCar['image']) ?>" alt="Car Image" />
            <div class="flex flex-col w-full h-[20rem] gap-4 justify-between">
                <div class="p-3 w-full bg-[#2c2c2c] h-[16rem] flex flex-col justify-between rounded-3xl">
                    <div class="w-full flex justify-between gap-2">
                        <div class="flex flex-col gap-1">
                            <p>Fuel: <?= htmlspecialchars($selectedCar['fuel_type']) ?></p>
                            <p>Shifter: <?= htmlspecialchars($selectedCar['transmission']) ?></p>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p>Seats: <?= htmlspecialchars($selectedCar['passengers']) ?></p>
                            <p>Price per day: <?= htmlspecialchars($selectedCar['daily_price_huf']) ?> HUF</p>
                        </div>
                    </div>

                    <!-- Date Picker Section -->
                    <form id="bookingForm" class="flex flex-col gap-3 mt-4">
                        <input type="hidden" name="car_id" value="<?= htmlspecialchars($selectedCar['id']) ?>">
                        <label for="start_date" class="text-white">Start Date:</label>
                        <input type="text" name="start_date" id="start_date" class="p-2 rounded-lg bg-[#f5c747] text-black" required>

                        <label for="end_date" class="text-white">End Date:</label>
                        <input type="text" name="end_date" id="end_date" class="p-2 rounded-lg bg-[#f5c747] text-black" required>

                        <button type="submit" class="p-2 bg-[#f5c747] text-white text-center rounded-2xl mt-6 block">
                            Book Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for Feedback -->
    <div id="feedbackModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white text-black p-6 rounded-lg w-[90%] max-w-md">
            <div id="modalMessage" class="text-lg"></div>
            <div id="bookingDetails" class="mt-4"></div>
            <button id="closeModal" class="mt-4 p-2 bg-[#f5c747] rounded-lg w-full">Close</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const unavailableDates = <?= json_encode($unavailableDates) ?>;
        
        // Initialize the date pickers
        flatpickr("#start_date", {
            minDate: "today",
            disable: unavailableDates,
        });
        flatpickr("#end_date", {
            minDate: "today",
            disable: unavailableDates,
        });

        $("#bookingForm").on("submit", function(event) {
            event.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: window.location.href, 
                type: "POST",
                data: formData,
                success: function(response) {
                    try {
                        if (typeof response === "string") {
                            response = JSON.parse(response);
                        }

                        // Show booking details in modal
                        $("#modalMessage").text(response.message);
                        $("#bookingDetails").html(`
                            <p><strong>Car:</strong> ${response.carDetails.brand} ${response.carDetails.model} (${response.carDetails.year})</p>
                            <p><strong>Price per day:</strong> ${response.carDetails.daily_price_huf} HUF</p>
                            <p><strong>Booking Period:</strong> ${response.startDate} to ${response.endDate}</p>
                        `);

                        // Handle modal close
                        if (response.success) {
                            $("#closeModal").text("Congratulations").off("click").on("click", function() {
                                window.location.href = "profile.php";
                            });
                        } else {
                            $("#closeModal").text("Failed Booking").off("click").on("click", function() {
                                window.location.href = "profile.php";
                            });
                        }

                        // Show the modal
                        $("#feedbackModal").removeClass("hidden");
                    } catch (e) {
                        console.error("Error parsing response as JSON:", e);
                        $("#modalMessage").text("An unexpected error occurred. Please try again.");
                        $("#closeModal").text("Failed Booking").off("click").on("click", function() {
                            window.location.href = "profile.php";
                        });

                        $("#feedbackModal").removeClass("hidden");
                    }
                },
                error: function() {
                    $("#modalMessage").text("An error occurred. Please try again.");
                    $("#closeModal").text("Failed Booking").off("click").on("click", function() {
                        window.location.href = "profile.php";
                    });

                    $("#feedbackModal").removeClass("hidden");
                }
            });
        });

        $("#closeModal").on("click", function() {
            $("#feedbackModal").addClass("hidden");
        });
    </script>
</body>
</html>
