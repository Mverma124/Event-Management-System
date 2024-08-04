<?php
// dashboard.php

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

// Fetch current user data
$sql = "SELECT * FROM users WHERE id='$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_email'])) {
        $new_email = $conn->real_escape_string($_POST['email']);

        // Update email in database
        $sql = "UPDATE users SET email='$new_email' WHERE id='$user_id'";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Email updated successfully.";
        } else {
            $error_message = "Error updating email: " . $conn->error;
        }
    } elseif (isset($_POST['update_password'])) {
        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Update password in database
        $sql = "UPDATE users SET password='$new_password' WHERE id='$user_id'";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Password updated successfully.";
        } else {
            $error_message = "Error updating password: " . $conn->error;
        }
    } elseif (isset($_POST['delete_event'])) {
        $event_id = $conn->real_escape_string($_POST['event_id']);

        // Check if the event belongs to the user
        $sql = "SELECT * FROM events WHERE id='$event_id' AND user_id='$user_id'";
        $result = $conn->query($sql);

        if ($result->num_rows === 1) {
            // Delete event
            $delete_sql = "DELETE FROM events WHERE id='$event_id' AND user_id='$user_id'";
            if ($conn->query($delete_sql) === TRUE) {
                $success_message = "Event deleted successfully.";
            } else {
                $error_message = "Error deleting event: " . $conn->error;
            }
        } else {
            $error_message = "Event not found or you do not have permission to delete this event.";
        }
    }
}

// Fetch events posted by the user
$events_sql = "SELECT * FROM events WHERE user_id='$user_id'";
$events_result = $conn->query($events_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            padding: 0 20px;
        }
        form {
            margin-bottom: 20px;
        }
        input, textarea {
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
        .event-list {
            margin-top: 20px;
        }
        .event-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .event-item h4 {
            margin-top: 0;
        }
        .event-actions {
            margin-top: 10px;
        }
        .event-actions form {
            display: inline;
        }
        .event-actions button {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Dashboard</h1>
        <nav class="navbar">
            <a href="create_event.php">Create Event</a>
            <a href="discover_events.php">Discover Events</a>

         
            <a href="Attendee_Engagement_Features.php">Live Q&A</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <div class="container">
            <h2>Welcome to your Dashboard!</h2>
            <p>You are now logged in. This is your personal dashboard where you can manage your events.</p>

            <!-- Update Email Form -->
            <section id="update-email">
                <h3>Update Email</h3>
                <form action="dashboard.php" method="POST">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <button type="submit" name="update_email">Update Email</button>
                </form>
            </section>

            <!-- Update Password Form -->
            <section id="update-password">
                <h3>Update Password</h3>
                <form action="dashboard.php" method="POST">
                    <input type="password" name="password" placeholder="New Password" required>
                    <button type="submit" name="update_password">Update Password</button>
                </form>
            </section>

            <!-- Display Messages -->
            <?php if (isset($success_message)) { echo '<p class="message">' . $success_message . '</p>'; } ?>
            <?php if (isset($error_message)) { echo '<p class="error">' . $error_message . '</p>'; } ?>

            <!-- User-Posted Events -->
            <section id="user-events">
                <h3>Your Events</h3>
                <div class="event-list">
                    <?php if ($events_result->num_rows > 0): ?>
                        <?php while ($event = $events_result->fetch_assoc()): ?>
                            <div class="event-item" id="event-<?php echo $event['id']; ?>">
                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                <p><strong>Price:</strong> Rs<?php echo htmlspecialchars($event['ticket_price']); ?></p>
                                <p><strong>Privacy:</strong> <?php echo htmlspecialchars($event['privacy']); ?></p>
                                <div class="event-actions">
                                    <a href="edit_event.php?event_id=<?php echo $event['id']; ?>">
                                        <button>Edit</button>
                                    </a>
                                    <form action="dashboard.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" name="delete_event">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>You have not created any events yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    <footer style="background-color: #333; color: white; text-align: center; padding: 10px;">
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>

</body>
</html>
