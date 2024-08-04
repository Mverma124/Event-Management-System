<?php
// create_event.php

session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'event_management');

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT email FROM users WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $user_email = $user['email'];
} else {
    die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);
    $location = $conn->real_escape_string($_POST['location']);
    $ticket_price = $conn->real_escape_string($_POST['ticket_price']);
    $privacy = $conn->real_escape_string($_POST['privacy']);

    $sql = "INSERT INTO events (title, description, date, time, location, ticket_price, privacy, user_id, user_email)
            VALUES ('$title', '$description', '$date', '$time', '$location', '$ticket_price', '$privacy', '$user_id', '$user_email')";

    if ($conn->query($sql) === TRUE) {
        $message = 'Event created successfully!';
    } else {
        $message = 'Error: ' . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <style>
        /* Basic styles for the navigation bar */
        header {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
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
        .container {
            width: 80%;
            max-width: 1000px;
            margin: 20px auto;
        }
        form {
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, textarea, select {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
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
        .message {
            text-align: center;
            margin-top: 20px;
            color: green;
        }
        .error {
            text-align: center;
            margin-top: 20px;
            color: red;
        }
    </style>
</head>
<body>
    <header>
        <h1>Create Event</h1>
        <nav class="navbar">
          <a href="discover_events.php">Discover Events</a>
          <a href="Attendee_Engagement_Features.php">Live Q&A</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main class="container">
        <!-- Display user email -->
        <section id="user-email">
            <h2>Your Email</h2>
            <p><?php echo htmlspecialchars($user_email); ?></p>
        </section>
        <form action="create_event.php" method="POST">
            <label for="title">Event Title:</label>
            <input type="text" id="title" name="title" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
            
            <label for="time">Time:</label>
            <input type="time" id="time" name="time" required>
            
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>
            
            <label for="ticket_price">Ticket Price:</label>
            <input type="number" id="ticket_price" name="ticket_price" step="0.01" required>
            
            <label for="privacy">Privacy Setting:</label>
            <select id="privacy" name="privacy">
                <option value="public">Public</option>
                <option value="private">Private</option>
            </select>
            
            <button type="submit">Create Event</button>
        </form>
        <?php if (isset($message)) { echo '<p class="message">' . $message . '</p>'; } ?>
    </main>
    <footer style="background-color: #333; color: white; text-align: center; padding: 10px;">
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>

</body>
</html>
