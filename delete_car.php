<?php
if (isset($_GET['id'])) {
    $carIdToDelete = $_GET['id'];

    $carsData = json_decode(file_get_contents('cars.json'), true);

    if ($carsData === null) {
        echo "Error loading car data!";
        exit;
    }
    $carFound = false;
    foreach ($carsData as $key => $car) {
        if ($car['id'] == $carIdToDelete) {
            unset($carsData[$key]);
            $carFound = true;
            break;
        }
    }

    if ($carFound) {
        $carsData = array_values($carsData);

        if (file_put_contents('cars.json', json_encode($carsData, JSON_PRETTY_PRINT))) {
            header("Location: admin_dashboard.php?deleted=true");
        } else {
            echo "Error saving updated car data!";
        }
    } else {
        echo "Car not found!";
    }
} else {
    echo "No car ID provided!";
}
?>
