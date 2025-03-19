<?php
// ranking.php
include('database_connection/db_connect.php');

// Query to fetch ranking data
$sql = "SELECT id, name, attendance_count, progress 
        FROM users 
        ORDER BY attendance_count DESC, progress DESC"; // Adjust the sorting logic as needed
$result = $conn->query($sql);

// Initialize rank counter
$rank = 1;

// Prepare the rows for the table
$rankingRows = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rankingRows .= "<tr>
                            <th scope='row'>{$rank}</th>
                            <td>{$row['name']}</td>
                            <td>{$row['attendance_count']}</td>
                            <td>{$row['progress']}</td>
                            </tr>";
        $rank++;
    }
} else {
    $rankingRows = "<tr><td colspan='4' class='text-center'>No users found.</td></tr>";
}

// Close connection
$conn->close();

// Output the rows
echo $rankingRows;
?>
