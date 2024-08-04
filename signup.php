<?php
// signup.php

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'event_management');

    // Check connection
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username or email already exists
    $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $error = 'Username or email already exists.';
    } else {
        // Insert user into the database
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

        if ($conn->query($sql) === TRUE) {
            header('Location: login.php');
            exit();
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add styles for the signup page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        header {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
        }
        #signup {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
        }
        input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #555;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Signup</h1>
    </header>
    <main>
        <section id="signup">
            <form action="signup.php" method="POST">
                <label for="signup-username">Username:</label>
                <input type="text" id="signup-username" name="username" required>
                
                <label for="signup-email">Email:</label>
                <input type="email" id="signup-email" name="email" required>
                
                <label for="signup-password">Password:</label>
                <input type="password" id="signup-password" name="password" required>
                
                <button type="submit">Signup</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <?php if (isset($error)) { echo '<p class="error">' . $error . '</p>'; } ?>
        </section>
    </main>
    <footer style="background-color: #333; color: white; text-align: center; padding: 10px;">
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>
</body>
</html>
