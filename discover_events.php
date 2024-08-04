<?php
// discover_events.php

$conn = new mysqli('localhost', 'root', '', 'event_management');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get search parameters
$title = isset($_GET['title']) ? $conn->real_escape_string($_GET['title']) : '';
$date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';
$time = isset($_GET['time']) ? $conn->real_escape_string($_GET['time']) : '';
$location = isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '';

// Build the SQL query with dynamic filters
$events_sql = "SELECT * FROM events WHERE 1=1";
if ($title) {
    $events_sql .= " AND title LIKE '%$title%'";
}
if ($date) {
    $events_sql .= " AND date = '$date'";
}
if ($time) {
    $events_sql .= " AND time = '$time'";
}
if ($location) {
    $events_sql .= " AND location LIKE '%$location%'";
}
$events_sql .= " ORDER BY date, time";
$events_result = $conn->query($events_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Events</title>
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
        main {
            width: 80%;
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }
        form {
            margin-bottom: 20px;
        }
        input, select {
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
            width: 100%;
        }
        button:hover {
            background-color: #555;
        }
        .event-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .event-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            width: calc(25% - 20px);
            box-sizing: border-box;
        }
        .event-card h3 {
            margin-top: 0;
        }
        .event-card p {
            margin: 5px 0;
        }
        .event-card a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
        .event-card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
<h1>Discover Events</h1>

        <nav class="navbar">
            <a href="create_event.php">Create Event</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="Attendee_Engagement_Features.php">Live Q&A</a>
            <a href="logout.php">Logout</a>
        </nav>
        
    </header>
    <main>
        <form action="discover_events.php" method="GET">
            <input type="text" name="title" placeholder="Search by title..." value="<?php echo htmlspecialchars($title); ?>">
            <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
            <input type="time" name="time" value="<?php echo htmlspecialchars($time); ?>">
            <input type="text" name="location" placeholder="Search by location..." value="<?php echo htmlspecialchars($location); ?>">
            <button type="submit">Search</button>
        </form>
        <h2>Upcoming Events</h2>
        <div class="event-grid">
            <?php if ($events_result->num_rows > 0): ?>
                <?php while ($event = $events_result->fetch_assoc()): ?>
                    <div class="event-card">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p>Date: <?php echo htmlspecialchars($event['date']); ?></p>
                        <p>Time: <?php echo htmlspecialchars($event['time']); ?></p>
                        <p>Location: <?php echo htmlspecialchars($event['location']); ?></p>
                        <p>Price: Rs<?php echo htmlspecialchars($event['ticket_price']); ?></p>
                        <p>Organizer: <?php echo htmlspecialchars($event['user_email']); ?></p>
                        <a href="register_ticket.php?event_id=<?php echo $event['id']; ?>">Register</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No events found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </main>
    <footer style="background-color: #333; color: white; text-align: center; padding: 10px;">
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>

</body>
</html>
