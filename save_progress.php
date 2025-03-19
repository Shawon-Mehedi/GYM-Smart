<?php
include('database_connection/db_connect.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    // Decode JSON data to PHP associative array
    $data = json_decode($json, true);
    
    // Check if the required fields are set
    if (isset($data['date'], $data['weight'], $data['reps'], $data['exercise'])) {
        // Retrieve individual fields
        $date = $data['date'];
        $weight = $data['weight'];
        $reps = $data['reps'];
        $exercise = $data['exercise'];

        // Prepare the SQL statement
        $sql = "INSERT INTO progress (date, weight, reps, exercise) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bind_param("ssis", $date, $weight, $reps, $exercise); // Adjust types as needed (s=string, i=integer)

        // Execute the statement
        if ($stmt->execute()) {
            // Return a success response
            echo json_encode(['success' => true, 'message' => 'Progress added successfully']);
        } else {
            // Return an error response
            echo json_encode(['success' => false, 'message' => 'Error adding progress: ' . $stmt->error]);
        }

        // Close statement
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Please fill all fields.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Close the database connection
$conn->close();
?>
