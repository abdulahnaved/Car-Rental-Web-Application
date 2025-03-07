<?php
session_start();

$jsonFile = 'users.json';
$email = $password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['Email_login'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password_login'];

    if (!empty($email) && !empty($password)) {
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $users = json_decode($jsonData, true);

            foreach ($users as $user) {
                if ($user['email'] === $email && $password === $user['password']) {
                    $_SESSION['user_id'] = $user['email'];
                    $_SESSION['user_name'] = $user['full_name'];

                    if ($user['email'] === 'admin@ikarrental.hu') {
                        header("Location: admin_dashboard.php"); 
                    } else {
                        header("Location: profile.php"); 
                    }
                    exit;
                }
            }

            $error = "Invalid email or password.";
        } else {
            $error = "No users found.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="./assets/logo.png" type="image/x-icon">
</head>
<body class="select-none bg-[#1b1b1b]">
    <nav class="flex px-6 relative top-0 left-0 bg-[#2c2c2c] w-full p-4 items-center justify-between">
        <a href="index.php" id="main_page" class="text-white font-medium cursor-pointer">iKarRental</a>
        <a href="Register.php" class="text-white font-medium hover:opacity-75 cursor-pointer">Registration</a>
    </nav>
    <main class="w-full flex flex-col pt-16 gap-52 justify-center items-center">
        <div class="text-white font-bold text-5xl">Login</div>
        <div class="w-[70%] 2xl:w-[35%]">
            <form class="flex flex-col gap-6" method="POST">
                <div class="flex flex-col gap-3">
                    <label for="Email_login" class="text-white">Email Address</label>
                    <input type="email" name="Email_login" placeholder="Enter your Email Address" id="Email_login" value="<?php echo htmlspecialchars($email); ?>" class="p-2 text-black border border-white rounded-md">
                </div>
                <div class="flex flex-col gap-3">
                    <label for="password_login" class="text-white">Password</label>
                    <input type="password" name="password_login" placeholder=".............." id="password_login" value="<?php echo htmlspecialchars($password); ?>" class="p-2 text-black border border-white rounded-md">
                </div>
                <div class="w-full flex justify-end">
                    <input type="submit" id="Login_submit" value="Login" class="bg-[#f7c748] px-10 w-fit text-black font-bold p-2 rounded-full cursor-pointer hover:opacity-75">
                </div>
            </form>
            <?php if (!empty($error)) { echo "<p class='text-red-500'>$error</p>"; } ?>
        </div>
    </main>
</body>
</html>
