<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Progress</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            color: #333;
        }

        h1 {
            color: #28a745;
            margin-bottom: 30px;
            font-size: 2.5em;
        }

        .progress-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .progress-container h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .btn {
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            font-size: 16px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }

        td {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <h1>Track Your Progress</h1>

    <div class="progress-container">
        <h2>Input Your Progress</h2>

        <form id="progress-form" action="save_progress.php" method="POST">
    <div class="form-group">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>
    </div>
    <div class="form-group">
        <label for="weight">Weight (kg):</label>
        <input type="number" step="0.1" id="weight" name="weight" required>
    </div>
    <div class="form-group">
        <label for="reps">Reps Completed:</label>
        <input type="number" id="reps" name="reps" required>
    </div>
    <div class="form-group">
        <label for="exercise">Exercise:</label>
        <input type="text" id="exercise" name="exercise" required>
    </div>
    <button type="submit" class="btn">Add Progress</button>
</form>


        <h2>Your Progress</h2>
        <table id="progress-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Weight (kg)</th>
                    <th>Reps Completed</th>
                    <th>Exercise</th>
                </tr>
                </thead>
            <tbody id="progress-tbody">
                <?php include 'show_progress.php'; ?>
            </tbody>
        </table>
    </div>

    <script>
document.getElementById('progress-form').addEventListener('submit', (event) => {
    event.preventDefault();

    const date = document.getElementById('date').value;
    const weight = document.getElementById('weight').value;
    const reps = document.getElementById('reps').value;
    const exercise = document.getElementById('exercise').value;

    const data = { date, weight, reps, exercise };

    fetch('save_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (response.ok) {
            return response.json(); // Get the response JSON
        } else {
            throw new Error('Failed to save progress');
        }
    })
    .then(newEntry => {
        if (newEntry.success) {
            // Update the table with the newEntry.data
            console.log("New entry added:", newEntry.data);
            // Implement code to update the UI (e.g., append to a table)
        } else {
            console.error("Error from server:", newEntry.message);
        }
    })
    .catch(error => {
        console.error('Error saving progress:', error);
    });
});
</script>

</body>
</html>