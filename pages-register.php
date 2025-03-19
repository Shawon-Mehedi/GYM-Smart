<?php
// Include database connection
include('database_connection/db_connect.php');
session_start();
$errors = [];
$name = $email = $username = $password = '';
$userType = ''; // Store the selected user type ('Admin' or 'User')

// Error variables for each field
$nameError = $emailError = $usernameError = $passwordError = $userTypeError = '';

// Generate a random verification code
function generateVerificationCode() {
    return rand(100000, 999999);  // 6-digit code
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $userType = isset($_POST['user_type']) ? $_POST['user_type'] : '';  // Retrieve selected user type (Admin/User)

    // Validate each field and store the error message if invalid
    if (empty($name)) {
        $nameError = 'Name is required';
    }
    if (empty($email)) {
        $emailError = 'Email is required';
    }
    if (empty($username)) {
        $usernameError = 'Username is required';
    }
    if (empty($password)) {
        $passwordError = 'Password is required';
    }
    if (empty($userType)) {
        $userTypeError = 'Please select whether you are an Admin or a User';
    }

    if (empty($nameError) && empty($emailError) && empty($usernameError) && empty($passwordError) && empty($userTypeError)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $verification_code = generateVerificationCode();

        // Insert into the correct table based on the selected user type
        if ($userType == 'User') {
            $sql = "INSERT INTO users (name, email, username, password, verification_code, verified) 
                    VALUES ('$name', '$email', '$username', '$hashed_password', '$verification_code', 0)";
        } elseif ($userType == 'Admin') {
            $sql = "INSERT INTO admin (name, email, username, password, verification_code, verified) 
                    VALUES ('$name', '$email', '$username', '$hashed_password', '$verification_code', 0)";
        }

        if (mysqli_query($conn, $sql)) {
            // Send the verification code to the user's email
            $subject = "Email Verification Code";
            $message = "Your verification code is: $verification_code";
            $headers = "From: mehedihasanshawon418@gmail.com";

            if (mail($email, $subject, $message, $headers)) {
                // Store session data for verification
                $_SESSION['email'] = $email;
                $_SESSION['verification_code'] = $verification_code;
                $_SESSION['user_type'] = $userType;  // Store the user type for verification
                
                header("Location: verify.php");  // Redirect to verification page
                exit();
            } else {
                $errors[] = "Failed to send verification email.";
            }
        } else {
            $errors[] = "Database Error: " . mysqli_error($conn);
        }
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Register - GymSmart</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

<main>
    <div class="container">
        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                        <div class="d-flex justify-content-center py-4">
                            <a href="index.php" class="logo d-flex align-items-center w-auto">
                                <img src="assets/img/logo.png" alt="">
                                <span class="d-none d-lg-block">GymSmart</span>
                            </a>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="pt-2 pb-1 text-center">
                                    <h5 class="card-title pb-0 fs-4">Create an Account</h5>
                                </div>

                                <!-- Registration form -->
                                <form class="row g-3 needs-validation" method="POST" novalidate>
                                    <!-- Register as (Admin/User) -->
                                    <div class="col-12 text-center mb-3">
                                        <label class="form-label">Register as</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="user_type" id="userCheckbox" value="User" required>
                                            <label class="form-check-label" for="userCheckbox">User</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="user_type" id="adminCheckbox" value="Admin" required>
                                            <label class="form-check-label" for="adminCheckbox">Admin</label>
                                        </div>
                                        <?php if (!empty($userTypeError)) : ?>
                                            <div class="text-danger small"><?= $userTypeError ?></div>
                                        <?php endif; ?>
                                        <p class="small">Enter your personal details to create an account</p>
                                    </div>

                                    <!-- Your Name -->
                                    <div class="col-12">
                                        <label for="yourName" class="form-label">Your Name</label>
                                        <input type="text" name="name" class="form-control" id="yourName" required value="<?= htmlspecialchars($name) ?>">
                                        <?php if (!empty($nameError)) : ?>
                                            <div class="text-danger small"><?= $nameError ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Your Email -->
                                    <div class="col-12">
                                        <label for="yourEmail" class="form-label">Your Email</label>
                                        <input type="email" name="email" class="form-control" id="yourEmail" required value="<?= htmlspecialchars($email) ?>">
                                        <?php if (!empty($emailError)) : ?>
                                            <div class="text-danger small"><?= $emailError ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Username -->
                                    <div class="col-12">
                                        <label for="yourUsername" class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" id="yourUsername" required value="<?= htmlspecialchars($username) ?>">
                                        <?php if (!empty($usernameError)) : ?>
                                            <div class="text-danger small"><?= $usernameError ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Password -->
                                    <div class="col-12">
                                        <label for="yourPassword" class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" id="yourPassword" required>
                                        <?php if (!empty($passwordError)) : ?>
                                            <div class="text-danger small"><?= $passwordError ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Create Account Button -->
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit">Create Account</button>
                                    </div>

                                    <!-- Login Link -->
                                    <div class="col-12">
                                        <p class="small mb-0">Already have an account? <a href="pages-login.php">Log in</a></p>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

</body>
</html>
