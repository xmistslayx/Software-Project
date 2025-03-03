<?php
session_start();
require "db.php";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifications as $row) {
        echo $row['message'] . "<br>";
    }
} else {
    echo "User ID is not set.";
}

// Handle AJAX requests for unread notifications
$current_user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["fetch"])) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESc");
    $stmt->bindValue(1, $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($notifications);
    exit();
}

// Handle marking notifications as read
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mark_read"])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bindValue(1, $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
    exit();
}

// Fetch unread notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->bindValue(1, $current_user_id, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="notification-container">
    <div id="notification-icon" onclick="markNotificationsAsRead()">
        <span class="bell">🔔</span>
        <span id="notif-count" class="count"><?= count($notifications) ?></span>
    </div>

    <div id="notif-dropdown" class="dropdown">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notif-item <?= htmlspecialchars($notif['type']) ?>">
                    <?= htmlspecialchars($notif["message"]) ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="notif-item">No new notifications</div>
        <?php endif; ?>
    </div>
</div>

<script>
    function fetchNotification() {
        fetch("notifications.php?fetch=true")
            .then(response => response.json())
            .then(data => {
                document.getElementById("notif-count").innerText = data.length;
                let dropdown = document.getElementById("notif-dropdown");
                dropdown.innerHTML = data.length > 0 ? "" : "<div class='notif-item'>No new notifications</div>";
                data.forEach(notif => {
                    let div = document.createElement("div");
                    div.classList.add("notif-item", notif.type);
                    div.innerText = notif.message;
                    dropdown.appendChild(div);
                });
            });
    }

    function markNotificationsAsRead() {
        fetch("notifications.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "mark_read=true"
        }).then(() => {
            document.getElementById("notif-count").innerText = "0";
        });
    };

    setInterval(fetchNotification, 10000);
</script>
