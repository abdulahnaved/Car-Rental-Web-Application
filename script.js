const popup = document.createElement("div");
popup.style.position = "fixed";
popup.style.top = "5%";
popup.style.left = "50%";
popup.style.transform = "translate(-50%, -50%)";
popup.style.padding = "20px";
popup.style.backgroundColor = "orange";
popup.style.color = "#fff";
popup.style.borderRadius = "8px";
popup.style.boxShadow = "0px 0px 10px rgba(0, 0, 0, 0.5)";
popup.style.display = "none";
popup.style.zIndex = "1000";
document.body.appendChild(popup);
function showPopup(message) {
  popup.innerText = message;
  popup.style.display = "block";
  setTimeout(() => {
    popup.style.display = "none";
  }, 3000); 
}


let datbase = "Car_Rental";
let databaseVersion = 1;
let carRental1 = indexedDB.open(datbase, databaseVersion);
carRental1.onupgradeneeded = function (event) {
  let db = event.target.result;
  let objStoreName = "users";
  let objStoreName1 = "cars";
  let objStoreSetting = {
    keyPath: "primaryKey",
    autoIncrement: true,
  };
  if (db.objectStoreNames.contains(objStoreName)) {
    db.deleteObjectStore(objStoreName);
  } else {
    let userStore = db.createObjectStore(objStoreName, objStoreSetting);
    userStore.createIndex("nameIndex", "name");
  }
  if (db.objectStoreNames.contains(objStoreName1)) {
    db.deleteObjectStore(objStoreName1);
  } else {
    let userStore = db.createObjectStore(objStoreName1, objStoreSetting);
    userStore.createIndex("nameIndex", "name");
  }
};

try {
  
  let num = true;
  let register_submit = document.getElementById("register_submit");
  register_submit.addEventListener("click", function () {
  event.preventDefault();
  let full_name_register = document.getElementById("full_name_register");
  let Email_register = document.getElementById("Email_register");
  let password_register = document.getElementById("password_register");
  let password_register_confirm = document.getElementById(
    "password_register_confirm"
  );
  if (
    full_name_register.value === "" ||
    Email_register.value === "" ||
    password_register.value === "" ||
    password_register_confirm.value === ""
  ) {
    showPopup("Please fill in all fields");
  } else if (password_register.value !== password_register_confirm.value) {
    showPopup("Passwords do not match");
  } else {
    let carRental = indexedDB.open(datbase, databaseVersion);
    carRental.onsuccess = function (event) {
      let db = event.target.result;
      let userStoreName = "users";
      let userStoreOperationMode = "readwrite";
      let objStore = [userStoreName];
      let transaction = db.transaction(objStore, userStoreOperationMode);
      let userObjectStore = transaction.objectStore(userStoreName);
      let request = userObjectStore.getAll();
      request.onsuccess = function (event) {

        let data = event.target.result;
        for (let i = 0; i < data.length; i++) {
          if (
            data[i].Email_register === Email_register.value &&
            data[i].password_register === password_register.value
          ) {
            showPopup("Email already exists");
            num = false;
            break;
          } else {
            num = true;
          }
          if (
            data[i].Email_register === Email_register.value
          ) {
            showPopup("Email already exists");
            num = false;
            break;
          } else {
            num = true;
          }
        }
        if (num) {
          let dataEntry = {
            full_name_register: full_name_register.value,
            Email_register: Email_register.value,
            password_register: password_register.value,
            password_register_confirm: password_register_confirm.value,
          };
          let addDAta = userObjectStore.add(dataEntry);
          addDAta.onsuccess = function (event) {
            document.getElementById("full_name_register").value = "";
            document.getElementById("Email_register").value = "";
            document.getElementById("password_register").value = "";
            document.getElementById("password_register_confirm").value = "";
            console.log("Data added successfully");
            showPopup("Registration successful");
            window.location.href = "./login.html";
          };
          addDAta.onerror = function (event) {
            console.log("Data not added");
          };
        }
      };
    };
  }
});
} catch (error) {
  
}
try {
  
  let bool = false;
  let Login_submit = document.getElementById("Login_submit");
Login_submit.addEventListener("click", function () {
  event.preventDefault();
  let Email_login = document.getElementById("Email_login");
  let password_login = document.getElementById("password_login");
  let Email_login_value = Email_login.value;
  let password_login_value = password_login.value;
  if (Email_login_value === "" || password_login_value === "") {
    showPopup("Please fill in all fields");
  } else {
    let carRental = indexedDB.open(datbase, databaseVersion);
    carRental.onsuccess = function (event) {
      let db = event.target.result;
      if (
        Email_login_value == "Admin" ||
        (Email_login_value == "admin" && password_login_value == "12345")
      ) {
        bool = false;
        window.location.href = "./Admin_Customer/Admin.html";
        localStorage.setItem("userName", "Admin");
        localStorage.setItem("Page", 1);
      } else {
        let userStoreName = "users";
        let userStoreOperationMode = "readwrite";
        let objStore = [userStoreName];
        let transaction = db.transaction(objStore, userStoreOperationMode);
        let userObjectStore = transaction.objectStore(userStoreName);
        let request = userObjectStore.openCursor();
        request.onsuccess = function (event) {
          let cursor = event.target.result;
          if (cursor) {
            if (
              cursor.value.Email_register === Email_login_value &&
              cursor.value.password_register === password_login_value
            ) {
              localStorage.setItem("userName", cursor.value.full_name_register);
              window.location.href = "./Admin_Customer/Customer.html";
              localStorage.setItem("Page", 2);
              bool = false;
            }
            cursor.continue();
          } else {
            bool = true;
          }
        };
        if (bool) {
          showPopup("Invalid Email or Password");
        }
      }
    };
  }
});

} catch (error) {
  
}
try {
  
  
  const createCarCard = (carData) => {
    try {
      if (carData.status === "available") {
 
        document
        .getElementById("add_cars_container1_1")
        .appendChild(
          createCarCard1(
            carData.image,
            carData.name,
            carData.seats,
            carData.price,
            carData.primaryKey,
            carData.transmission,
"carId"
          )
        );
      }
    } catch (error) {}
    try {
      document
        .getElementById("add_cars")
        .appendChild(
          createCarCard2(
            carData.image,
            carData.name,
            carData.seats,
            carData.price
          )
        );
    } catch (error) {}
  };
  
  const createCarCard1 = (imageSrc, carName, seats, price, id, transmission , a) => {
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
      
      if (a === "carId1") {
        localStorage.setItem('new' , 1);
      }
      if (a === "carId") {
        localStorage.setItem('new' , 2);
      }
      localStorage.setItem(a, id);
      
      window.location.href = "./booking.html";
    });
  
    bottomDiv1.appendChild(carDetails);
    bottomDiv.appendChild(bottomDiv1);
    bottomDiv.appendChild(BookButton);
    carCard.appendChild(bottomDiv);
  
    return carCard;
  };

  let objectArray = [
    {
        "primaryKey":1,
        "name": "Honda Civic",
        "year": 2019,
        "transmission": "Manual",
        "fuel_type": "Petrol",
        "seats": 5,
        "price": 16250,
        "image": "https://media.ed.edmunds-media.com/honda/civic/2019/oem/2019_honda_civic_sedan_touring_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":2,
        "name": "Nissan Altima",
        "year": 2016,
        "transmission": "Automatic",
        "fuel_type": "Petrol",
        "seats": 5,
        "price": 14400,
        "image": "https://media.ed.edmunds-media.com/nissan/altima/2016/oem/2016_nissan_altima_sedan_25-sr_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":3,
        "name": "Volkswagen Jetta",
        "year": 2014,
        "transmission": "Manual",
        "fuel_type": "Diesel",
        "seats": 5,
        "price": 13000,
        "image": "https://media.ed.edmunds-media.com/volkswagen/jetta/2025/oem/2025_volkswagen_jetta_sedan_sel_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":4,
        "name": "Subaru Impreza",
        "year": 2012,
        "transmission": "Automatic",
        "fuel_type": "Petrol",
        "seats": 5,
        "price": 12000,
        "image": "https://media.ed.edmunds-media.com/subaru/impreza/2022/oem/2022_subaru_impreza_4dr-hatchback_limited_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":5,
        "name": "Ford Focus",
        "year": 2018,
        "transmission": "Automatic",
        "fuel_type": "Petrol",
        "seats": 5,
        "price": 13500,
        "image": "https://media.ed.edmunds-media.com/ford/focus/2013/oem/2013_ford_focus_4dr-hatchback_bev_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":6,
        "name": "Tesla Model S",
        "year": 2021,
        "transmission": "Automatic",
        "fuel_type": "Electric",
        "seats": 5,
        "price": 45000,
        "image": "https://media.ed.edmunds-media.com/tesla/model-s/2021/oem/2021_tesla_model-s_sedan_plaid_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":7,
        "name": "Toyota Highlander",
        "year": 2020,
        "transmission": "Automatic",
        "fuel_type": "Petrol",
        "seats": 7,
        "price": 20000,
        "image": "https://media.ed.edmunds-media.com/toyota/highlander/2020/oem/2020_toyota_highlander_4dr-suv_platinum_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":8,
        "name": "Chevrolet Suburban",
        "year": 2021,
        "transmission": "Automatic",
        "fuel_type": "Petrol",
        "seats": 8,
        "price": 24000,
        "image": "https://media.ed.edmunds-media.com/chevrolet/suburban/2021/oem/2021_chevrolet_suburban_4dr-suv_high-country_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":9,
        "name": "Rivian R1T",
        "year": 2022,
        "transmission": "Automatic",
        "fuel_type": "Electric",
        "seats": 5,
        "price": 40000,
        "image": "https://media.ed.edmunds-media.com/rivian/r1t/2022/oem/2022_rivian_r1t_crew-cab-pickup_launch-edition_fq_oem_1_815.jpg"
    },
    {
        "primaryKey":10,
        "name": "Tesla Model X",
        "year": 2022,
        "transmission": "Automatic",
        "fuel_type": "Electric",
        "seats": 7,
        "price": 48000,
        "image": "https://media.ed.edmunds-media.com/tesla/model-x/2024/oem/2024_tesla_model-x_4dr-suv_plaid_fq_oem_1_815.jpg"
    }
  ]
  
  objectArray.forEach(element => {
    document
    .getElementById("add_cars_container1_1")
    .appendChild(
      createCarCard1(
        element.image,
        element.name + " " + element.seats,
        element.seats,
        element.price,
        element.primaryKey,
        element.transmission,
"carId1"
      )
    );
  });
  
  
  
  let datbase = "Car_Rental";
  let databaseVersion = 1;
  let carRental = indexedDB.open(datbase, databaseVersion);
  carRental.onsuccess = function (event) {
    let db = event.target.result;
    let transaction = db.transaction(["cars"], "readonly");
    let store = transaction.objectStore("cars");
    let request = store.openCursor();
    request.onsuccess = function (event) {
      let cursor = event.target.result;
      if (cursor) {
        createCarCard(cursor.value);
        cursor.continue();
      }
    };
  };
  
  



  
  
} catch (error) {
  
}

let Page = localStorage.getItem("Page");
if (Page) {
  if (Page == 1) {
    window.location.href = "./Admin_Customer/Admin.html";
  } else if (Page == 2) {
    window.location.href = "./Admin_Customer/Customer.html";
  } else if (Page == 3) {
    window.location.href = "./Admin_Customer/profile.html";
  } 
  else if (Page == 4) {
    window.location.href = "./Admin_Customer/add_cars.html";
  }else if (Page == 0) {
    window.location.href = "./index.html";
    localStorage.clear();
  }
}


