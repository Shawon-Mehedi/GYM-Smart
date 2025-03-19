<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and store the query
    $query = htmlspecialchars(trim($_POST['query']));

    // Example: Establish database connection (adjust your credentials)
    include('database_connection/db_connect.php');
    
    // Use prepared statements for security
    $stmt = $conn->prepare("SELECT title, description FROM your_table WHERE column_name LIKE ?");
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='results'>";
        while ($row = $result->fetch_assoc()) {
            echo "<div class='result-item'>";
            echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>No results found for: <strong>" . $query . "</strong></p>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>