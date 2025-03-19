<?php
// Start session and include database connection
session_start();
include('database_connection/db_connect.php'); // Ensure your database connection is correct

// Fetch users data from the database
// Check if the user is logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'User') {
  $user_id = $_SESSION['user_id'];

  // Fetch user info from the database
  $query = "SELECT * FROM users WHERE id = $user_id";
  $result = mysqli_query($conn, $query);
  $user = mysqli_fetch_assoc($result);

  // Display the user's information
  echo "Welcome, " . $user['username'];
  echo "<br>Email: " . $user['email'];
  echo "<br>Phone: " . $user['phone'];
} else {
    // Redirect to login if not logged in
    header("Location: pages-login.php");
    exit();
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
  $fullName = $_POST['fullName'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];

  // Initialize imagePath to store the image path
  $imagePath = '';

  // Handle profile picture upload
  if (!empty($_FILES['profile_pic']['name'])) {
      $profile_pic = $_FILES['profile_pic'];

      // Ensure the file is an image
      $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
      if (in_array($profile_pic['type'], $allowedTypes) && $profile_pic['error'] == 0) {
          // Move the uploaded file to the uploads/profile_picture/ directory
          $targetDir = "uploads/profile_picture"; // Ensure this directory exists and is writable
          $fileName = uniqid() . '-' . basename($profile_pic['name']); // Generate a unique file name
          $targetFile = $targetDir . $fileName;
          
          // Check if the file already exists
          if (file_exists($targetFile)) {
              echo "Sorry, file already exists.";
          } else {
              // Try to move the uploaded file
              if (move_uploaded_file($profile_pic['tmp_name'], $targetFile)) {
                  $imagePath = $targetFile; // Save this path to the database
              } else {
                  echo "Error uploading the file.";
                  // Log the error for debugging
                  error_log("Failed to move uploaded file: " . print_r($profile_pic, true));
              }
          }
      } else {
          echo "Invalid image type or error in upload. Error Code: " . $profile_pic['error'];
          // Log the error for debugging
          error_log("File upload error: " . print_r($profile_pic, true));
      }
  }

  

  // Update user information in the database
  $updateQuery = "UPDATE users SET name = ?, email = ?, phone = ?";

  // Check if there is an image path to update
  if (!empty($imagePath)) {
      $updateQuery .= ", profile_pic = ?";
  }
  $updateQuery .= " WHERE id = ?";

  if ($stmt = $conn->prepare($updateQuery)) {
      if (!empty($imagePath)) {
          $stmt->bind_param('ssssi', $fullName, $email, $phone, $imagePath, $user_id);
      } else {
          $stmt->bind_param('sssi', $fullName, $email, $phone, $user_id);
      }
      if ($stmt->execute()) {
          echo "Profile updated successfully!";
      } else {
          echo "Error updating profile: " . $stmt->error;
      }
  } else {
      echo "Profile update query preparation failed: " . $conn->error;
  }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
  $currentPassword = $_POST['currentPassword'];
  $newPassword = $_POST['newPassword'];

  // Fetch current password from the database
  $query = "SELECT password FROM users WHERE id = ?";
  if ($stmt = $conn->prepare($query)) {
      $stmt->bind_param('i', $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $users_data = $result->fetch_assoc();

      // Verify current password
      if (password_verify($currentPassword, $users_data['password'])) {
          // Update new password
          $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
          $updatePasswordQuery = "UPDATE users SET password = ? WHERE id = ?";
          if ($stmt = $conn->prepare($updatePasswordQuery)) {
              $stmt->bind_param('si', $hashedPassword, $user_id);
              if ($stmt->execute()) {
                  echo "Password updated successfully!";
              } else {
                  echo "Error updating password: " . $stmt->error;
              }
          } else {
              echo "Password update query preparation failed: " . $conn->error;
          }
      } else {
          // Handle incorrect current password
          echo "Current password is incorrect!";
      }
  } else {
      echo "Password verification query preparation failed: " . $conn->error;
  }
}

// Handle payments
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_payment'])) {
  $user_id = $_SESSION['user_id']; // Assuming user is logged in and their ID is stored in the session

  // Set default total fee
  $total_fee = 5000;

  // Fetch current paid amount from the database for the user
  $query = "SELECT COALESCE(SUM(paid_amount), 0) AS total_paid FROM payments WHERE user_id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $paymentData = $result->fetch_assoc();
  $stmt->close();

  // Initialize paid amount and balance
  $paid_amount = $paymentData['total_paid']; // This is the sum of all paid amounts
  $balance = $total_fee - $paid_amount; // Calculate balance based on previous payments

  // Get the current payment inputs
  $payment_date = $_POST['paymentDate'];
  $amount_paid = $_POST['amount_paid'];
  $full_name = $_POST['fullName'];

  // Debug to check if the full name is passed
  if (empty($full_name)) {
      echo "Full name is missing!";
      exit();
  }

  // Update the paid amount and balance after user enters a new payment
  $paid_amount += $amount_paid;
  $balance = $total_fee - $paid_amount;

  // Insert the current payment into the database
  $paymentQuery = "INSERT INTO payments (user_id, full_name, total_fee, paid_amount, balance, payment_date, amount_paid) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";

  if ($stmt = $conn->prepare($paymentQuery)) {
      $stmt->bind_param('isdddsd', $user_id, $full_name, $total_fee, $paid_amount, $balance, $payment_date, $amount_paid);
      if ($stmt->execute()) {
          echo "Payment successfully made!";
      } else {
          echo "Error processing payment: " . $stmt->error;
      }
      $stmt->close();
  } else {
      echo "Payment query preparation failed: " . $conn->error;
  }
}


?>





<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>users / Profile - NiceUsers Bootstrap Template</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">GymSmart</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              You have 4 new notifications
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>Task-1</h4>
                <p>You complete your first task</p>
                <p>30 min. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-x-circle text-danger"></i>
              <div>
                <h4>Atque rerum nesciunt</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>
            <li class="dropdown-footer">
              <a href="#">Show all notifications</a>
            </li>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-text"></i>
            <span class="badge bg-success badge-number">3</span>
          </a><!-- End Messages Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
            <li class="dropdown-header">
              You have 3 new messages
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-1.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Maria Hudson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>4 hrs. ago</p>
                </div>
              </a>
            </li>
          </ul><!-- End Messages Dropdown Items -->

        </li><!-- End Messages Nav -->

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                  <!-- Profile picture dynamically loaded -->
                <img src="<?= !empty($user['profile_pic']) ? $user['profile_pic'] : 'assets/img/default_images.png' ?>" alt="Profile" class="rounded-circle profile-icon">
                <span class="d-none d-md-block dropdown-toggle ps-2">
                    <?php 
                    // Display the user's name from the session
                    echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; 
                    ?>
                </span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
                <h6>
                    <?php 
                    // Assuming session variables store the name and user type
                    echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
                    ?>
                </h6>
                <span>
                    <?php 
                    // Assuming session variable 'user_type' holds the value 'User' or 'User'
                    echo isset($_SESSION['user_type']) ? htmlspecialchars($_SESSION['user_type']) : 'User';
                    ?>
                </span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.php">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-faq.php">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-login.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="index.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Achievements & Updates</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="components-alerts.php">
              <i class="bi bi-circle"></i><span>Alerts</span>
            </a>
          </li>
          <li>
            <a href="components-badges.php">
              <i class="bi bi-circle"></i><span>Badges</span>
            </a>
          </li>
          <li>
            <a href="progress.php">
              <i class="bi bi-circle"></i><span>Progress</span>
            </a>
          </li>
          <li>
            <a href="components-progress.php">
              <i class="bi bi-circle"></i><span>Workout Plan</span>
            </a>
          </li>
        </ul>
      </li><!-- End Components Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="forms-elements.php">
          <i class="bi bi-journal-text"></i><span>Evaluation</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="forms-elements.php">
              <i class="bi bi-circle"></i><span>Evaluate</span>
            </a>
          </li>
        </ul>
      </li><!-- End Forms Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-layout-text-window-reverse"></i><span>Ranking</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="tables-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="tables-general.php">
              <i class="bi bi-circle"></i><span>Your Rank</span>
            </a>
          </li>
        </ul>
      </li><!-- End Tables Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i><span>Charts</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="charts-chartjs.php">
              <i class="bi bi-circle"></i><span>Chart.js</span>
            </a>
          </li>
        </ul>
      </li><!-- End Charts Nav -->

      <li class="nav-heading">Pages</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="users-profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>  
        </a>
      </li><!-- End Profile Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-faq.php">
          <i class="bi bi-question-circle"></i>
          <span>F.A.Q</span>
        </a>
      </li><!-- End F.A.Q Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-contact.php">
          <i class="bi bi-envelope"></i>
          <span>Contact</span>
        </a>
      </li><!-- End Contact Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-register.php">
          <i class="bi bi-card-list"></i>
          <span>Register</span>
        </a>
      </li><!-- End Register Page Nav --> 

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-login.php">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Login</span>
        </a>
      </li><!-- End Login Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-error-404.html">
          <i class="bi bi-dash-circle"></i>
          <span>Error 404</span>
        </a>
      </li><!-- End Error 404 Page Nav -->

    </ul>

  </aside><!-- End Sidebar-->

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Profile</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item">users</li>
        <li class="breadcrumb-item active">Profile</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section profile">
    <div class="row">
      <div class="col-xl-4">
        <div class="card">
          <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
            <img src="<?= !empty($user['profile_pic']) ? $user['profile_pic'] : 'assets/img/default_images.png' ?>" alt="Profile" class="rounded-circle">
            <h2><?= htmlspecialchars($user['name']) ?></h2>
          </div>
        </div>
      </div>
    <!-- End Profile picture -->

      <div class="col-xl-8">
        <div class="card">
          <div class="card-body pt-3">
            <!-- Bordered Tabs -->
            <ul class="nav nav-tabs nav-tabs-bordered">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Change Password</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-payment">Payment</button>
              </li>
            </ul>
            <div class="tab-content pt-2">
              <!-- Overview Tab -->
              <div class="tab-pane fade show active profile-overview" id="profile-overview">
                <h5 class="card-title">Profile Details</h5>
                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Full Name</div>
                  <div class="col-lg-9 col-md-8"><?= htmlspecialchars($user['name']) ?></div>
                </div>
                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Email</div>
                  <div class="col-lg-9 col-md-8"><?= htmlspecialchars($user['email']) ?></div>                
                </div>
                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Phone</div>
                  <div class="col-lg-9 col-md-8"><?= htmlspecialchars($user['phone']) ?></div>
                </div>
              </div>

              <!-- Edit Profile Tab -->
              <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                  <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                      <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="fullName" type="text" class="form-control" id="fullName" value="<?= htmlspecialchars($user['name']) ?>">
                      </div>
                    </div>
                    <div class="row mb-3">
                      <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="email" type="email" class="form-control" id="Email" value="<?= htmlspecialchars($user['email']) ?>">
                      </div>
                    </div>
                    <div class="row mb-3">
                      <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="phone" type="text" class="form-control" id="Phone" value="<?= htmlspecialchars($user['phone']) ?>">
                      </div>
                    </div>
                    <div class="row mb-3">
                        <label for="profile_pic" class="col-md-4 col-lg-3 col-form-label">Profile Picture</label>
                        <div class="col-md-8 col-lg-9">
                            <input name="profile_pic" type="file" class="form-control" id="profile_pic" accept="image/*">
                            <small class="text-muted">Upload a profile picture (optional)</small>
                        </div>
                    </div>
                    <div class="text-center">
                      <input type="hidden" name="update_profile" value="1">
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form>
                </div>

              <!-- Change Password Tab -->
              <div class="tab-pane fade pt-3" id="profile-change-password">
                  <form action="" method="POST">
                    <div class="row mb-3">
                      <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="currentPassword" type="password" class="form-control" id="currentPassword" required>
                      </div>
                    </div>
                    <div class="row mb-3">
                      <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="newPassword" type="password" class="form-control" id="newPassword">
                      </div>
                    </div>
                    <div class="text-center">
                      <input type="hidden" name="change_password" value="1">
                      <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                  </form>
              </div>
              <!-- Payment Tab -->
              <div class="tab-pane fade pt-3" id="profile-payment">
                    <form action="" method="POST">
                        <!-- Hidden field to trigger make_payment -->
                        <input type="hidden" name="make_payment" value="1">
                        
                        <!-- Display User/Admin Name -->
                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">User Name</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                            </div>
                        </div>
                        
                        <!-- Full Name -->
                        <div class="row mb-3">
                            <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="fullName" type="text" class="form-control" id="fullName" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="row mb-3">
                            <label for="paymentDate" class="col-md-4 col-lg-3 col-form-label">Date</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="paymentDate" type="date" class="form-control" id="paymentDate" required>
                            </div>
                        </div>
                        <script>
                            // Automatically set the date to today
                            document.getElementById('paymentDate').value = new Date().toISOString().split('T')[0];
                        </script>

                        <!-- Amount to Pay -->
                        <div class="row mb-3">
                            <label for="amount" class="col-md-4 col-lg-3 col-form-label">Amount to Pay</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="amount_paid" type="number" class="form-control" id="amount" placeholder="Enter amount" required>
                            </div>
                        </div>

                        <!-- Total Fee -->
                        <div class="row mb-3">
                            <label for="totalFee" class="col-md-4 col-lg-3 col-form-label">Total Fee</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="total_fee" type="number" class="form-control" id="totalFee" value="<?= htmlspecialchars($totalFee) ?>" readonly>
                            </div>
                        </div>

                        <!-- Paid Amount -->
                        <div class="row mb-3">
                            <label for="paidAmount" class="col-md-4 col-lg-3 col-form-label">Paid Amount</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="paid_amount" type="number" class="form-control" id="paidAmount" value="<?= htmlspecialchars($paidAmount) ?>" readonly>
                            </div>
                        </div>

                        <!-- Balance -->
                        <div class="row mb-3">
                            <label for="balance" class="col-md-4 col-lg-3 col-form-label">Balance</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="balance" type="number" class="form-control" id="balance" value="<?= htmlspecialchars($balance) ?>" readonly>
                            </div>
                        </div>

                        <!-- Pay Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Pay Online</button>
                        </div>
                    </form>
              </div>
            </div><!-- End Bordered Tabs -->
          </div>
        </div>
      </div>
    </div>
  </section>
</main><!-- End #main -->


  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>GymSmart</span></strong>. All Rights Reserved
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>