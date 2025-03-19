<?php
include('database_connection/db_connect.php');

// Fetch all progress records
$sql = "SELECT * FROM progress ORDER BY date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['weight'] . "</td>";
        echo "<td>" . $row['reps'] . "</td>";
        echo "<td>" . $row['exercise'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No progress data found.</td></tr>";
}

// Close the connection
$conn->close();
?>
