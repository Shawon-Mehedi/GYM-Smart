<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input values
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Set up the email
    $to = "nhasan221473@bscse.uiu.ac.bd"; // Change this to your email
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $fullMessage = "You received a message from $name ($email):\n\n$message";

    // Send email
    if (mail($to, $subject, $fullMessage, $headers)) {
        // Success message
        echo "<p>Your message has been sent successfully.</p>";
    } else {
        // Error message
        echo "<p>Sorry, something went wrong. Please try again later.</p>";
    }
} else {
    // If the form isn't submitted
    header("Location: pages-contact.php");
    exit();
}
?>
