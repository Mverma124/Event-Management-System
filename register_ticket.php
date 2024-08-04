<?php
// register_ticket.php

session_start(); // Start the session

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'event_management');

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch user email
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT email FROM users WHERE id='$user_id'";
$user_result = $conn->query($user_sql);

if ($user_result->num_rows !== 1) {
    die('User not found.');
}

$user = $user_result->fetch_assoc();
$user_email = htmlspecialchars($user['email']);

// Fetch event details
$event_id = isset($_GET['event_id']) ? $conn->real_escape_string($_GET['event_id']) : '';
$event_sql = "SELECT * FROM events WHERE id='$event_id'";
$event_result = $conn->query($event_sql);

if ($event_result->num_rows !== 1) {
    die('Event not found.');
}

$event = $event_result->fetch_assoc();
$event_title = htmlspecialchars($event['title']);
$event_description = htmlspecialchars($event['description']); // Fetch event description

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Insert registration into the database
    $sql = "INSERT INTO registrations (event_id, user_id, name, phone)
            VALUES ('$event_id', '$user_id', '$name', '$phone')";

    if ($conn->query($sql) === TRUE) {
        echo 'Registration successful!';
    } else {
        echo 'Error: ' . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event</title>
    <style>
        /* Basic styles for the registration page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
            margin-bottom: 20px;
        }
        .navbar {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 10px 0;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        main {
            width: 80%;
            max-width: 1000px;
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
        input[type="text"], input[type="tel"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 16px;
            width: 100%;
        }
        button {
            padding: 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #555;
        }
        .event-info {
            margin-bottom: 20px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .event-info h2 {
            margin-top: 0;
            font-size: 18px;
            color: #333;
        }
        .event-info p {
            margin: 5px 0;
        }
        .description-toggle {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-weight: bold;
        }
        .event-description {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
    <script>
        function toggleDescription() {
            var desc = document.getElementById('event-description');
            var toggle = document.getElementById('description-toggle');
            if (desc.style.display === 'none') {
                desc.style.display = 'block';
                toggle.textContent = 'Read Less';
            } else {
                desc.style.display = 'none';
                toggle.textContent = 'Read About the Event';
            }
        }
    </script>
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="create_event.php">Create Event</a>
            <a href="discover_events.php">Discover Events</a>
            <a href="attendee_forum.php">Forum</a>
            <a href="Attendee_Engagement_Features.php">Live Q&A</a>
            <a href="logout.php">Logout</a>
            <a href="dashboard.php">Dashboard</a>
        </nav>
        <h1>Register for Event</h1>
    </header>
    <main>
        <div class="event-info">
            <h2>Event Name: <?php echo $event_title; ?></h2>
            <p><strong>Your Email:</strong> <?php echo $user_email; ?></p>
            <p id="description-toggle" class="description-toggle" onclick="toggleDescription()">Read About the Event</p>
            <div id="event-description" class="event-description">
                <p><?php echo $event_description; ?></p>
            </div>
        </div>
        <form action="register_ticket.php?event_id=<?php echo $event_id; ?>" method="POST">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>
            
            <button type="submit">Register</button>
        </form>
    </main>
    <footer style="background-color: #333; color: white; text-align: center; padding: 10px;">
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>
</body>
</html>
