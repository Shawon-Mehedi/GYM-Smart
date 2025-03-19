<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemini API Quickstart</title>
    <style>
        .workout-plan {
            padding: 2em;
            background-color: #f9f9f9;
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5em;
        }

        label {
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 0.5em;
            margin-top: 0.5em;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .btn {
            padding: 0.75em 1.5em;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #218838;
        }

        #result {
            margin-top: 2em;
            padding: 1em;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <h1>Gemini API Text Generation</h1>

    <section class="workout-plan">
        <div class="container">
            <h2>Get Your Personalized Workout and Diet Plan</h2>
            <form id="workout-plan-form">
                <div class="form-group">
                    <label for="age">Age:</label>
                    <input type="number" id="age" name="age" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="weight">Weight (kg):</label>
                    <input type="number" id="weight" name="weight" required>
                </div>

                <div class="form-group">
                    <label for="height">Height (cm):</label>
                    <input type="number" id="height" name="height" required>
                </div>

                <div class="form-group">
                    <label for="fitness-goal">Fitness Goal:</label>
                    <select id="fitness-goal" name="fitness-goal" required>
                        <option value="lose weight">Lose Weight</option>
                        <option value="gain muscle">Gain Muscle</option>
                        <option value="maintain fitness">Maintain Fitness</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="activity-level">Activity Level:</label>
                    <select id="activity-level" name="activity-level" required>
                        <option value="sedentary">Sedentary</option>
                        <option value="light">Light Exercise</option>
                        <option value="moderate">Moderate Exercise</option>
                        <option value="active">Active</option>
                        <option value="very-active">Very Active</option>
                    </select>
                </div>

                <button type="submit" class="btn">Generate Plan</button>
            </form>

            <div id="result">
                <!-- The AI-generated workout and diet plan will be displayed here -->
            </div>
        </div>
    </section>

    <div id="response"></div>

    <!-- Import @google/generative-ai SDK -->
    <script type="importmap">
      {
        "imports": {
          "@google/generative-ai": "https://esm.run/@google/generative-ai"
        }
      }
    </script>

    <!-- Script to interact with the API -->
    <script type="module">
        import { GoogleGenerativeAI } from "@google/generative-ai";

        // Your API key
        const API_KEY = "AIzaSyBDGmk3AG676cZdh-VsKxXqQrt2sn_pUiQ"; 

        // Initialize the API client
        const genAI = new GoogleGenerativeAI(API_KEY);

        document.getElementById('workout-plan-form').addEventListener('submit', async function (event) {
            event.preventDefault(); // Prevent the form from submitting

            const age = document.getElementById('age').value;
            const gender = document.getElementById('gender').value;
            const weight = document.getElementById('weight').value;
            const height = document.getElementById('height').value;
            const fitnessGoal = document.getElementById('fitness-goal').value;
            const activityLevel = document.getElementById('activity-level').value;

            // Define your prompt
            const prompt = `Generate a ${fitnessGoal} workout plan for a ${age}-year-old ${gender}, weighing ${weight}kg, and with a ${activityLevel} activity level. Include a variety of exercises, specify sets and reps, and suggest 3-4 workouts per week. The plan should be easy to follow and adjust to their needs.`;

            try {
                const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash" });
                const result = await model.generateContent(prompt);
                const responseText = result.response.text();

                // Store response in localStorage
                localStorage.setItem('apiResponse', responseText);

                // Redirect to the new page
                window.location.href = 'response.html';
            } catch (error) {
                console.error("Error generating content:", error);
            }
        });
    </script>
</body>
</html>
