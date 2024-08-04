<?php
// edit_event.php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'event_management');

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['event_id'])) {
    die('No event ID provided.');
}

$event_id = $conn->real_escape_string($_GET['event_id']);

// Fetch event data
$sql = "SELECT * FROM events WHERE id='$event_id' AND user_id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    die('Event not found or you do not have permission to edit this event.');
}

$event = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);
    $location = $conn->real_escape_string($_POST['location']);
    $ticket_price = $conn->real_escape_string($_POST['ticket_price']);
    $privacy = $conn->real_escape_string($_POST['privacy']);

    // Update event in database
    $update_sql = "UPDATE events 
                   SET title='$title', description='$description', date='$date', time='$time', location='$location', ticket_price='$ticket_price', privacy='$privacy'
                   WHERE id='$event_id' AND user_id='$user_id'";

    if ($conn->query($update_sql) === TRUE) {
        $success_message = "Event updated successfully.";
    } else {
        $error_message = "Error updating event: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic styles for the form */
        header {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
        }
        .container {
            width: 80%;
            max-width: 800px;
            margin: 20px auto;
        }
        form {
            margin-bottom: 20px;
        }
        input, textarea, select {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
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
            margin: 10px 0;
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <header>
        <h1>Edit Event</h1>
    </header>
    <header>
        <nav class="navbar">
            <a href="create_event.php">Create Event</a>
            <a href="discover_events.php">Discover Events</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="Attendee_Engagement_Features.php">Live Q&A</a>
            <a href="logout.php">Logout</a>
            
        </nav>
    </header>
    <main>
        <div class="container">
            <h2>Edit Event Details</h2>
            
            <!-- Display Messages -->
            <?php if (isset($success_message)) { echo '<p class="message">' . $success_message . '</p>'; } ?>
            <?php if (isset($error_message)) { echo '<p class="error">' . $error_message . '</p>'; } ?>

            <!-- Edit Event Form -->
            <form action="edit_event.php?event_id=<?php echo $event['id']; ?>" method="POST">
                <label for="title">Event Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($event['date']); ?>" required>
                <label for="time">Time:</label>
                <input type="time" id="time" name="time" value="<?php echo htmlspecialchars($event['time']); ?>" required>
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                <label for="ticket_price">Ticket Price:</label>
                <input type="number" id="ticket_price" name="ticket_price" step="0.01" value="<?php echo htmlspecialchars($event['ticket_price']); ?>" required>
                <label for="privacy">Privacy Setting:</label>
                <select id="privacy" name="privacy">
                    <option value="public" <?php echo $event['privacy'] === 'public' ? 'selected' : ''; ?>>Public</option>
                    <option value="private" <?php echo $event['privacy'] === 'private' ? 'selected' : ''; ?>>Private</option>
                </select>
                <button type="submit">Update Event</button>
            </form>
        </div>
    </main>
    <footer style="background-color: #333; color: white; text-align: center; padding: 10px;">
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>

</body>
</html>
