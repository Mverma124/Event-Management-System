<?php
// Attendee_Engagement_Features.php

session_start();
$conn = new mysqli('localhost', 'root', '', 'event_management');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch user ID from session
$user_id = isset($_SESSION['user_id']) ? $conn->real_escape_string($_SESSION['user_id']) : null;
$user_email = isset($_SESSION['user_email']) ? $conn->real_escape_string($_SESSION['user_email']) : '';

// Fetch date, event ID from query string, if set
$selected_date = isset($_GET['selected_date']) ? $conn->real_escape_string($_GET['selected_date']) : '';
$event_id = isset($_GET['event_id']) ? $conn->real_escape_string($_GET['event_id']) : '';

// Handle Question Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_content']) && isset($_POST['event_id'])) {
    if ($user_id) {
        $content = $conn->real_escape_string($_POST['question_content']);
        $event_id = $conn->real_escape_string($_POST['event_id']);

        $insert_question_sql = "INSERT INTO questions (event_id, user_id, content) VALUES ('$event_id', '$user_id', '$content')";

        if ($conn->query($insert_question_sql) === TRUE) {
            header("Location: Attendee_Engagement_Features.php?event_id=$event_id");
            exit();
        } else {
            echo '<p class="error">Error: ' . $conn->error . '</p>';
        }
    } else {
        echo '<p class="error">You must be logged in to submit questions.</p>';
    }
}

// Handle Comment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content']) && isset($_POST['question_id'])) {
    if ($user_id) {
        $content = $conn->real_escape_string($_POST['comment_content']);
        $question_id = $conn->real_escape_string($_POST['question_id']);
        $event_id = $conn->real_escape_string($_POST['event_id']);

        $insert_comment_sql = "INSERT INTO comments (question_id, user_id, content) VALUES ('$question_id', '$user_id', '$content')";

        if ($conn->query($insert_comment_sql) === TRUE) {
            header("Location: Attendee_Engagement_Features.php?event_id=$event_id");
            exit();
        } else {
            echo '<p class="error">Error: ' . $conn->error . '</p>';
        }
    } else {
        echo '<p class="error">You must be logged in to comment.</p>';
    }
}

// Fetch Dates
$dates_sql = "SELECT DISTINCT DATE(`date`) AS event_date FROM events ORDER BY event_date";
$dates_result = $conn->query($dates_sql);

// Fetch Events based on selected date or all events
$events_sql = "SELECT id, title FROM events ORDER BY title";
if ($selected_date) {
    $events_sql = "SELECT id, title FROM events WHERE DATE(`date`) = '$selected_date' ORDER BY title";
}
$events_result = $conn->query($events_sql);

// Fetch Questions and Comments if an event is selected
$questions_result = $comments_result = [];
if ($event_id) {
    $question_sql = "SELECT questions.id, questions.content, questions.created_at, users.email AS user_email
                      FROM questions
                      JOIN users ON questions.user_id = users.id
                      WHERE questions.event_id='$event_id'
                      ORDER BY questions.created_at DESC";
    $questions_result = $conn->query($question_sql);

    $comments_sql = "SELECT comments.id, comments.question_id, comments.content, comments.created_at, users.email AS user_email
                     FROM comments
                     JOIN users ON comments.user_id = users.id
                     WHERE comments.question_id IN (SELECT id FROM questions WHERE event_id='$event_id')
                     ORDER BY comments.created_at ASC";
    $comments_result = $conn->query($comments_sql);

    // Prepare comments by question
    $comments_by_question = [];
    while ($comment = $comments_result->fetch_assoc()) {
        $comments_by_question[$comment['question_id']][] = $comment;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Q&A Feature</title>
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
        .error {
            color: red;
        }
        .section {
            margin-bottom: 20px;
        }
        .question, .comment {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .question h4, .comment h4 {
            margin-top: 0;
        }
        .comments {
            margin-top: 10px;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <header>
    <h1>Live Q&A Feature</h1>
        <nav class="navbar">
            <a href="create_event.php">Create Event</a>
            <a href="discover_events.php">Discover Events</a>
        
            <a href="logout.php">Logout</a>
        </nav>
        
    </header>
    <main>
        <!-- Select Date -->
        <section class="section">
            <h2>Select a Date</h2>
            <form action="Attendee_Engagement_Features.php" method="GET">
                <label for="selected_date">Choose a date:</label>
                <input type="date" id="selected_date" name="selected_date" value="<?php echo htmlspecialchars($selected_date); ?>" placeholder="YYYY-MM-DD">
                <button type="submit">Show Events</button>
            </form>
        </section>

        <!-- List of Events for Selected Date or All Events -->
        <section class="section">
            <h2>Select an Event</h2>
            <form action="Attendee_Engagement_Features.php" method="GET">
                <label for="event_select">Choose an event:</label>
                <select id="event_select" name="event_id" onchange="this.form.submit()">
                    <option value="">--Select an Event--</option>
                    <?php while ($event = $events_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($event['id']); ?>" <?php echo ($event_id == $event['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($event['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if ($selected_date): ?>
                    <input type="hidden" name="selected_date" value="<?php echo htmlspecialchars($selected_date); ?>">
                <?php endif; ?>
            </form>
        </section>

        <!-- Live Q&A -->
        <?php if ($event_id): ?>
            <section class="section">
                <h2>Live Q&A for Event ID: <?php echo htmlspecialchars($event_id); ?></h2>
                <?php if ($user_id): ?>
                    <form action="Attendee_Engagement_Features.php" method="POST">
                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
                        <label for="question_content">Ask a question:</label>
                        <textarea id="question_content" name="question_content" rows="4" required></textarea>
                        <button type="submit">Submit Question</button>
                    </form>
                <?php else: ?>
                    <p>You must be logged in to ask questions.</p>
                <?php endif; ?>

                <h3>Questions:</h3>
                <?php while ($question = $questions_result->fetch_assoc()): ?>
                    <div class="question">
                        <strong><?php echo htmlspecialchars($question['user_email']); ?>:</strong>
                        <p><?php echo htmlspecialchars($question['content']); ?></p>
                        <small>Asked on <?php echo htmlspecialchars($question['created_at']); ?></small>

                        <!-- Display Comments for each Question -->
                        <?php if (isset($comments_by_question[$question['id']])): ?>
                            <div class="comments">
                                <?php foreach ($comments_by_question[$question['id']] as $comment): ?>
                                    <div class="comment">
                                        <strong><?php echo htmlspecialchars($comment['user_email']); ?>:</strong>
                                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                        <small>Commented on <?php echo htmlspecialchars($comment['created_at']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Comment Form -->
                        <?php if ($user_id): ?>
                            <form action="Attendee_Engagement_Features.php" method="POST">
                                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
                                <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($question['id']); ?>">
                                <label for="comment_content">Add a comment:</label>
                                <textarea id="comment_content" name="comment_content" rows="2" required></textarea>
                                <button type="submit">Submit Comment</button>
                            </form>
                        <?php else: ?>
                            <p>You must be logged in to comment.</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </section>
        <?php endif; ?>
    </main>
    <footer style="background-color: #333; color: white; text-align: center; padding: 10px;">
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>

</body>
</html>
