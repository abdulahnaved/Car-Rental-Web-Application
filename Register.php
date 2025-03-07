<?php
$jsonFile = 'users.json';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = htmlspecialchars($_POST['full_name_register']);
    $email = filter_var($_POST['Email_register'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password_register'];
    $confirm_password = $_POST['password_register_confirm'];

    if (!empty($full_name) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            if (file_exists($jsonFile)) {
                $jsonData = file_get_contents($jsonFile);
                $users = json_decode($jsonData, true);
            } else {
                $users = [];
            }

            foreach ($users as $user) {
                if ($user['email'] === $email) {
                    $error = "Email is already taken.";
                    break;
                }
            }

            if (!isset($error)) {
                $newUser = [
                    'full_name' => $full_name,
                    'email' => $email,
                    'password' => $password 
                ];

                $users[] = $newUser;

                file_put_contents($jsonFile, json_encode($users, JSON_PRETTY_PRINT));

                header("Location: Login.php");
                exit;
            }
        } else {
            $error = "Passwords do not match.";
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
    <title>Car Rental - Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="./assets/logo.png" type="image/x-icon">
</head>
<body class="select-none bg-[#1b1b1b]">
    <nav class="flex px-6 relative top-0 left-0 bg-[#2c2c2c] w-full p-4 items-center justify-between">
        <div id="main_page" class="text-white font-medium cursor-pointer">iKarRental</div>
        <div id="login_button" class="text-white font-medium hover:opacity-75 cursor-pointer">Login</div>
    </nav>
    <main class="w-full flex flex-col pt-24 gap-12 justify-center items-center">
        <div class="text-white font-bold text-5xl">Registration</div>
        <div class="w-[70%] 2xl:w-[35%]">
            <form class="flex flex-col gap-6" method="POST">
                <div class="flex flex-col gap-3">
                    <label for="full_name_register" class="text-white">Full name</label>
                    <input type="text" name="full_name_register" value="<?php echo isset($full_name) ? $full_name : ''; ?>" placeholder="Enter your full name" id="full_name_register" class="p-2 text-black border border-white rounded-md">
                </div>
                <div class="flex flex-col gap-3">
                    <label for="Email_register" class="text-white">Email address</label>
                    <input type="email" name="Email_register" value="<?php echo isset($email) ? $email : ''; ?>" placeholder="Enter your email" id="Email_register" class="p-2 text-black border border-white rounded-md">
                </div>
                <div class="flex flex-col gap-3">
                    <label for="password_register" class="text-white">Password</label>
                    <input type="password" name="password_register" placeholder="..............." id="password_register" class="p-2 text-black border border-white rounded-md">
                </div>
                <div class="flex flex-col gap-3">
                    <label for="password_register_confirm" class="text-white">Confirm password</label>
                    <input type="password" name="password_register_confirm" placeholder="..............." id="password_register_confirm" class="p-2 text-black border border-white rounded-md">
                </div>
                <div class="w-full flex justify-end">
                    <input type="submit" id="register_submit" value="Register" class="bg-[#f7c748] px-10 w-fit text-black font-bold p-2 rounded-full cursor-pointer hover:opacity-75">
                </div>
            </form>
            <?php if (isset($error)) { echo "<p class='text-red-500'>$error</p>"; } ?>
        </div>
    </main>
</body>
</html>
