<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thank You</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, #6a11cb, #2575fc);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }

    .thank-you-container {
      text-align: center;
      max-width: 600px;
      background: rgba(255, 255, 255, 0.1);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(10px);
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 20px;
      font-weight: bold;
      text-transform: uppercase;
    }

    p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }

    a {
      display: inline-block;
      padding: 10px 20px;
      font-size: 1rem;
      text-decoration: none;
      background: #fff;
      color: #2575fc;
      border-radius: 5px;
      transition: all 0.3s ease;
      font-weight: bold;
    }

    a:hover {
      background: #2575fc;
      color: #fff;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 600px) {
      h1 {
        font-size: 2rem;
      }

      p {
        font-size: 1rem;
      }

      a {
        padding: 8px 16px;
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body>

  <div class="thank-you-container">
    <h1>Thank You!</h1>
    <p>We have received your message and will get back to you shortly.</p>
    <a href="index.php">Go back to Home</a>
  </div>

</body>

</html>
