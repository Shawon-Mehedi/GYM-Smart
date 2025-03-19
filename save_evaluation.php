<?php
// Assuming you have a database connection set up in a separate file
include('database_connection/db_connect.php');

// Get the raw JSON input from the form submission
var_dump(file_get_contents('php://input'));
$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    // Extract form data
    $trainer_name = $data['trainer_name'];
    $session_date = $data['session_date'];
    $overall_rating = $data['overall_rating'];
    $communication = $data['communication'];
    $motivation = $data['motivation'];
    $professionalism = $data['professionalism'];
    $knowledge = $data['knowledge'];
    $punctuality = $data['punctuality'];
    $comments = $data['comments'];

    // Prepare and execute SQL to insert the data
    $stmt = $conn->prepare("INSERT INTO evaluations (trainer_name, session_date, overall_rating, communication, motivation, professionalism, knowledge, punctuality, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiiiiis", $trainer_name, $session_date, $overall_rating, $communication, $motivation, $professionalism, $knowledge, $punctuality, $comments);

    if ($stmt->execute()) {
        // Respond with a success message
        echo json_encode(['success' => true, 'message' => 'Evaluation saved successfully']);
    } else {
        // Respond with an error message
        echo json_encode(['success' => false, 'message' => 'Failed to save evaluation']);
    }

    $stmt->close();
    $conn->close();
} else {
    // Respond with an error if no data is received
    echo json_encode(['success' => false, 'message' => 'No data received']);
}
?>
