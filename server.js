import express from "express";
import dotenv from "dotenv";
import { GoogleGenerativeAI } from "@google/generative-ai";  // Assuming this is correct
import bodyParser from "body-parser";

dotenv.config();

const app = express();

// Initialize the Google Generative AI client using the API key
const genAI = new GoogleGenerativeAI({ apiKey: process.env.API_KEY });

app.use(bodyParser.urlencoded({ extended: true }));
app.use(express.static("public"));

// Route to handle the generation of the workout plan
app.post("/generate-plan", async (req, res) => {
    const { age, gender, weight, height, fitnessGoal, activityLevel } = req.body;

    // Create a prompt using the form data
    const prompt = `Create a personalized workout and diet plan for a ${age}-year-old ${gender} weighing ${weight}kg, with a height of ${height}cm. Their fitness goal is to ${fitnessGoal}, and they have an ${activityLevel} activity level.`;

    try {
        // Assuming the correct API call to generate text
        const result = await genAI.generate({
            model: "gemini-pro",
            prompt: prompt
        });

        // Access the generated content
        const text = result?.content ?? "No content generated";

        // Send the generated plan back to the client
        res.send({ plan: text });
    } catch (error) {
        console.error("Error generating plan:", error);
        res.status(500).send({ error: "Failed to generate the plan." });
    }
});

// Start the server
app.listen(3000, () => {
    console.log("Server is running on http://localhost:3000");
});
