<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch username
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Handle AJAX deadline addition
if (isset($_POST['ajax_add']) && $_POST['ajax_add'] == 1) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $deadline_date = $_POST['deadline_date'];

    if ($title && $deadline_date) {
        $stmt = $conn->prepare("INSERT INTO deadlines (user_id, title, description, deadline_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $desc, $deadline_date);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Title and date required']);
    }
    exit();
}

// Fetch deadlines as JSON for FullCalendar
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $stmt = $conn->prepare("SELECT id, title, description, deadline_date FROM deadlines WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'start' => $row['deadline_date'],
            'description' => $row['description']
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($events);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Study Buddy - Deadlines</title>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
<link rel="stylesheet" href="css/dashboard.css">
<link rel="stylesheet" href="css/calender.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
      <h2><u>Study-Buddy 📚💻</u></h2>
      <ul>
        <li><a href="dashboard.php" style="color:white; text-decoration:none;">📊 Dashboard</a></li>
        <li><a href="sidebar_tasks.php" style="color:white; text-decoration:none;">📝 Tasks</a></li>
        <li><a href="flashcards.php" style="color:white; text-decoration:none;">📚 Flashcards</a></li>
        <li><a href="notes.php" style="color:white; text-decoration:none;">📂 Notes</a></li>
        <li class="active"><a href="calender.php" style="color:white; text-decoration:none;">👥 Calendar</a></li>
        <li><a href="logout.php" style="color:white; text-decoration:none;">🚪 Logout</a></li>
      </ul>
    </aside>

    <main class="main">
        <marquee behavior="scroll" direction="left" scrollamount="10">
          <h2>Your Deadlines, <?php echo htmlspecialchars($username); ?> 👋</h2>
        </marquee>

        <div class="cards">
            <!-- Add Deadline Form -->
            <div class="card">
                <h3>➕ Add Deadline</h3>
                <form id="addDeadlineForm">
                    <input type="text" name="title" placeholder="Deadline title" required>
                    <textarea name="description" placeholder="Description (optional)"></textarea>
                    <input type="date" name="deadline_date" required>
                    <button type="submit">Add Deadline</button>
                    <p class="success-msg" id="successMsg">✅ Deadline added!</p>
                </form>
            </div>

            <!-- Calendar -->
            <div class="card">
                <h3>📅 Deadline Calendar</h3>
                <div id="calendar"></div>
            </div>
        </div>

        <!-- Upcoming Deadlines This Week -->
        <div class="upcoming">
            <h3>📌 Upcoming Deadlines This Week</h3>
            <ul id="upcomingList"></ul>
        </div>
    </main>
</div>

<script>
let calendar;

// Initialize FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'calender.php?action=fetch',
        eventDidMount: function(info) {
            if(info.event.extendedProps.description) {
                info.el.title = info.event.extendedProps.description;
            }
        },
        eventColor: '#28a745'
    });
    calendar.render();
    fetchUpcomingDeadlines();
});

// Handle Add Deadline form submission
document.getElementById('addDeadlineForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    data.append('ajax_add', 1);

    fetch('calender.php', { method:'POST', body:data })
    .then(res => res.json())
    .then(res => {
        if(res.status === 'success') {
            document.getElementById('successMsg').style.display='block';
            form.reset();
            calendar.refetchEvents();
            fetchUpcomingDeadlines();
            setTimeout(()=>document.getElementById('successMsg').style.display='none',2000);
        } else {
            alert(res.message || "Error adding deadline");
        }
    });
});

// Fetch upcoming deadlines this week
function fetchUpcomingDeadlines() {
    fetch('calender.php?action=fetch')
    .then(res => res.json())
    .then(events => {
        const upcomingList = document.getElementById('upcomingList');
        upcomingList.innerHTML = '';

        const today = new Date();
        const weekFromNow = new Date();
        weekFromNow.setDate(today.getDate() + 7);

        events.forEach(event => {
            const eventDate = new Date(event.start);
            if (eventDate >= today && eventDate <= weekFromNow) {
                const li = document.createElement('li');
                li.textContent = `${event.title} - ${eventDate.toLocaleDateString()}`;
                upcomingList.appendChild(li);
            }
        });

        if (upcomingList.children.length === 0) {
            upcomingList.innerHTML = '<li>No upcoming deadlines this week.</li>';
        }
    });
}
</script>
</body>
</html>
