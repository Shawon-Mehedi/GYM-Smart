<?php
// Include database connection
include('database_connection/db_connect.php');
session_start();
$errors = [];

// Ensure that session variables are set
if (!isset($_SESSION['email']) || !isset($_SESSION['verification_code']) || !isset($_SESSION['user_type'])) {
    $errors[] = "Invalid session. Please register again.";
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $input_code = $_POST['verification_code'];
        $email = $_SESSION['email'];
        $stored_code = $_SESSION['verification_code'];
        $userType = $_SESSION['user_type'];  // Retrieve user type from session

        if ($input_code == $stored_code) {
            // Update verification status in the correct table
            if ($userType == 'User') {
                $sql = "UPDATE users SET verified = 1 WHERE email = '$email'";
            } elseif ($userType == 'Admin') {
                $sql = "UPDATE admin SET verified = 1 WHERE email = '$email'";
            }

            if (mysqli_query($conn, $sql)) {
                // Redirect to login page after successful verification
                header("Location: pages-login.php");
                exit();
            } else {
                $errors[] = "Error updating verification status: " . mysqli_error($conn);
            }
        } else {
            $errors[] = "Verification code is incorrect.";
        }
    }
}
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - GymSmart</title>
    <!-- Include Bootstrap for layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body, html {
            height: 80%;
            display: flex;
            justify-content: center;
            align-items: center;
            /* background-color: #f8f9fa; */
        }

        .verification-container {
            max-width: 400px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .verification-container img {
            width: 30px;
            display: block;
            margin: 0 auto;
        }

        .verification-container h5 {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }
    </style>
    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

</head>
<body>
    <div class="verification-container">
        <!-- GymSmart Logo -->
        <div class="d-flex justify-content-center py-4">
                <a href="index.php" class="logo d-flex align-items-center w-auto">
                        <img src="assets/img/logo.png" alt="">
                        <span class="d-none d-lg-block">GymSmart</span>
                </a>
        </div>
        <!-- Page Title -->
        <h5>Email Verification</h5>

        <form method="POST">
            <div class="form-group">
                <label for="verificationCode">Enter your verification Code</label>
                <input type="text" name="verification_code" class="form-control" placeholder="Enter Code" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Verify</button>
        </form>

        <!-- Display errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mt-3">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS (optional for interactive elements) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
