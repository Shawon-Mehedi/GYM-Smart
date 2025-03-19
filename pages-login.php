<?php
// Include database connection
include('database_connection/db_connect.php');
session_start();

// Initialize variables and error arrays for each field
$usernameError = $passwordError = $userTypeError = '';
$username = $password = $userType = '';

// Process the form when it is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input values
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $userType = isset($_POST['user_type']) ? $_POST['user_type'] : '';

    // Validate inputs
    if (empty($username)) {
        $usernameError = 'Username is required';
    }
    if (empty($password)) {
        $passwordError = 'Password is required';
    }
    if (empty($userType)) {
        $userTypeError = 'Please select whether you are an Admin or a User';
    }

    // Proceed with authentication if there are no errors
    if (empty($usernameError) && empty($passwordError) && empty($userTypeError)) {
        // Check the table based on user type
        if ($userType == 'User') {
            $query = "SELECT * FROM users WHERE username='$username'";
        } elseif ($userType == 'Admin') {
            $query = "SELECT * FROM admin WHERE username='$username'";
        }

        // Execute the query
        $result = mysqli_query($conn, $query);

        // Check if user exists
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);

            // Verify the password
            if (password_verify($password, $row['password'])) {
                // Check if the user is verified
                if ($row['verified'] == 1) {

                    // Set session and redirect based on user type
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['user_type'] = $userType;
                    $_SESSION['user_id'] = $row['id']; // Store the user ID

                     // Redirect based on user type
                     if ($userType == 'User') {
                        header("Location: users-profile.php"); // Redirect user
                    } elseif ($userType == 'Admin') {
                        header("Location: admin-profile.php"); // Redirect admin
                    }
                    //     // Set session and redirect to the index page
                    // $_SESSION['username'] = $row['username'];
                    // $_SESSION['user_type'] = $userType;
                    // header("Location: index.php");
                    
                    exit();
                } else {
                    $userTypeError = 'Your account is not verified. Please check your email for the verification code.';
                }
            } else {
                $passwordError = 'Invalid password.';
            }
        } else {
            $usernameError = 'No user found with that username.';
        }
    }
}

// Close the connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Login - GymSmart</title>
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
                                <div class="pt-4 pb-2">
                                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                                </div>

                                <!-- Login Form -->
                                <form class="row g-3 needs-validation" method="POST" novalidate>
                                    <!-- Select Admin or User -->
                                    <div class="col-12 text-center">
                                        <label class="form-label">Login as</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="user_type" id="userCheckbox" value="User" <?= (isset($_POST['user_type']) && $_POST['user_type'] == 'User') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="userCheckbox">User</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="user_type" id="adminCheckbox" value="Admin" <?= (isset($_POST['user_type']) && $_POST['user_type'] == 'Admin') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="adminCheckbox">Admin</label>
                                        </div>
                                        <?php if (!empty($userTypeError)) { ?>
                                            <div class="text-danger"><?= $userTypeError; ?></div>
                                        <?php } ?>
                                    </div>

                                    <!-- Username input -->
                                    <div class="col-12">
                                        <label for="yourUsername" class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" id="yourUsername" value="<?= htmlspecialchars($username) ?>" required>
                                        <?php if (!empty($usernameError)) { ?>
                                            <div class="text-danger"><?= $usernameError; ?></div>
                                        <?php } ?>
                                    </div>

                                    <!-- Password input -->
                                    <div class="col-12">
                                        <label for="yourPassword" class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" id="yourPassword" required>
                                        <?php if (!empty($passwordError)) { ?>
                                            <div class="text-danger"><?= $passwordError; ?></div>
                                        <?php } ?>
                                    </div>

                                    <!-- Submit button -->
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit">Login</button>
                                    </div>
                                    <div class="col-12">
                                        <p class="small mb-0">Don't have an account? <a href="pages-register.php">Create an account</a></p>
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

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Template Main JS File -->
<script src="assets/js/main.js"></script>
</body>

</html>
