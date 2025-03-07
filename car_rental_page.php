<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
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
<style>
    *{
        transition: all 1s;
    }
</style>
<body class="select-none bg-[#1b1b1b]">
    <div class="black">
        <nav class="flex px-6 relative top-0 left-0 bg-[#2c2c2c] w-full p-4 items-center justify-between">
            <div class="text-white font-medium">iKarRental</div>
            <div id="profile_Click1" class="hover:opacity-75 bg-white rounded-full border border-[#000000] cursor-pointer">
                <img src="../assets/profile.png" class="h-10 w-10 rounded-full" alt="">
            </div>
        </nav>
        <main class="w-full flex flex-col pt-10 px-5 gap-20">
            <div class="text-5xl font-bold text-white w-52">Rent cars easily!</div>
            <div class="w-full flex justify-end flex-wrap gap-8 items-center">
                <div class="flex gap-3 flex-wrap items-center">
                    <div class="flex flex-col gap-3">
                        <div class="flex gap-5 flex-wrap items-center">
                            <div class="flex items-center gap-2">
                                <button id="minusBtn" class="flex items-center justify-center h-10 w-10 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">-</button>
                                <div id="valueDisplay" class="flex items-center justify-center h-10 w-28 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">0</div>
                                <button id="plusBtn" class="flex items-center justify-center h-10 w-10 border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">+</button>
                                <span class="text-gray-400">Seats</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">From</span>
                                <input id="fromDate" type="date" class="flex items-center justify-center h-10 bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                                <span class="text-gray-400">To</span>
                                <input id="toDate" type="date" class="flex items-center justify-center h-10 bg-transparent border border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                            </div>
                        </div>

                        <div class="flex gap-5 items-center flex-wrap justify-end">
                            <select id="carType" class="flex items-center justify-center h-10 w-28 border border-[#2c2c2e] bg-transparent rounded-lg text-[#3f3e46] font-bold text-lg">
                                <option value>Gear type</option>
                                <option value="Manual">Manual</option>
                                <option value="Automatic">Automatic</option>
                            </select>

                            <div class="flex items-center gap-2">
                                <input id="minPrice" type="number" placeholder="Min Price" class="flex items-center justify-center h-10 w-40 px-4 border bg-transparent border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                                <span class="text-gray-400">-</span>
                                <input id="maxPrice" type="number" placeholder="Max Price" class="flex items-center justify-center h-10 w-40 px-4 border bg-transparent border-[#2c2c2e] rounded-lg text-[#3f3e46] font-bold text-lg">
                            </div>
                        </div>
                    </div>
                    <button id="filterBtn" class="bg-[#f9c73e] rounded-2xl p-2 px-10 hover:opacity-75 font-bold text-lg hover:text-white">Filter</button>
                </div>
            </div>
            <div id="add_cars_container1" class="flex flex-wrap gap-5 items-center"></div>
        </main>
    </div>

    <script>
        const minusBtn = document.getElementById("minusBtn");
        const plusBtn = document.getElementById("plusBtn");
        const valueDisplay = document.getElementById("valueDisplay");
        const filterBtn = document.getElementById("filterBtn");
        const carsContainer = document.getElementById("add_cars_container1");

        let seatCount = 0;

        plusBtn.addEventListener("click", () => {
            seatCount++;
            valueDisplay.textContent = seatCount;
        });

        minusBtn.addEventListener("click", () => {
            if (seatCount > 0) {
                seatCount--;
                valueDisplay.textContent = seatCount;
            }
        });

        filterBtn.addEventListener("click", () => {
            const fromDate = document.getElementById("fromDate").value;
            const toDate = document.getElementById("toDate").value;
            const carType = document.getElementById("carType").value;
            const minPrice = parseFloat(document.getElementById("minPrice").value) || 0;
            const maxPrice = parseFloat(document.getElementById("maxPrice").value) || Infinity;

            const request = indexedDB.open("Car_Rental", 1);

            request.onsuccess = function (event) {
                const db = event.target.result;
                const transaction = db.transaction(["cars"], "readonly");
                const store = transaction.objectStore("cars");

                const allCars = store.getAll();

                allCars.onsuccess = function () {
                    const cars = allCars.result;

                    const filteredCars = cars.filter(car => {
                        const carPrice = parseFloat(car.price);
                        const carSeats = parseInt(car.seats);
                        const isSeatsMatch = seatCount === 0 || carSeats === seatCount;
                        const isTypeMatch = !carType || car.gearType === carType;
                        const isPriceMatch = carPrice >= minPrice && carPrice <= maxPrice;
                        const isDateMatch =
                            (!fromDate || new Date(car.startDate) >= new Date(fromDate)) &&
                            (!toDate || new Date(car.endDate) <= new Date(toDate));

                        return isSeatsMatch && isTypeMatch && isPriceMatch && isDateMatch;
                    });

                    displayCars(filteredCars);
                };
            };
        });

        function displayCars(cars) {
            carsContainer.innerHTML = "";

            if (cars.length === 0) {
                carsContainer.innerHTML = `<p class="text-white font-bold text-xl">No cars found matching the criteria.</p>`;
                return;
            }

            const createCarCard16 = (imageSrc, carName, seats, price, id, transmission , a) => {
                const carCard = document.createElement("div");
                carCard.classList.add(
                  "flex",
                  "flex-col",
                  "h-64",
                  "w-72",
                  "group",
                  "relative",
                  "overflow-hidden",
                  "bg-cover",
                  "bg-no-repeat",
                  "bg-top",
                  "rounded-xl"
                );
            
                const image = document.createElement("img");
                image.classList.add("image-box", "peer-[a]");
                image.src = imageSrc;
                image.alt = "Car Image";
                carCard.appendChild(image);
            
                const priceDiv = document.createElement("div");
                priceDiv.classList.add(
                  "text-white",
                  "font-extrabold",
                  "text-3xl",
                  "absolute",
                  "right-3",
                  "bottom-16"
                );
                priceDiv.innerHTML = `${price} <span>Ft</span>`;
                carCard.appendChild(priceDiv);
            
                const transparentDiv = document.createElement("div");
                transparentDiv.classList.add("h-64", "bg-transparent", "w-full", "absolute");
                carCard.appendChild(transparentDiv);
            
                const bottomDiv = document.createElement("div");
                bottomDiv.classList.add(
                  "h-16",
                  "w-full",
                  "bg-[#423f4f]",
                  "absolute",
                  "bottom-0",
                  "flex",
                  "justify-between",
                  "px-4",
                  "p-2"
                );
                const bottomDiv1 = document.createElement("div");
                bottomDiv1.classList.add("flex", "flex-col");
            
                const carTitle = document.createElement("div");
                carTitle.classList.add("text-xl", "text-white");
                carTitle.textContent = carName;
                bottomDiv1.appendChild(carTitle);
            
                const carDetails = document.createElement("div");
                carDetails.classList.add("text-lg", "-mt-1", "text-[#7f7d89]");
                carDetails.textContent = `${seats} seats-${transmission}`;
                const BookButton = document.createElement("button");
                BookButton.classList.add(
                  "bg-[#f1c850]",
                  "px-4",
                  "rounded-xl",
                  "cursor-pointer",
                  "font-bold",
                  "text-xl"
                );
                BookButton.textContent = "Book";
                BookButton.addEventListener("click", () => {
                  localStorage.setItem(a, id);
                  window.location.href = "./booking.html";
                });
            
                bottomDiv1.appendChild(carDetails);
                bottomDiv.appendChild(bottomDiv1);
                bottomDiv.appendChild(BookButton);
                carCard.appendChild(bottomDiv);
            
                return carCard;
            };
            
            cars.forEach(car => {
                carsContainer.appendChild(createCarCard16(
                    car.image, car.name, car.seats, car.price, car.primaryKey, car.transmission, "carId1"
                ));
            });
        }
    </script>
</body>
</html>
