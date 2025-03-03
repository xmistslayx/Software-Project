<?php
session_start();
require "db.php";

$user_id = 1;

if (isset($user_id)) {
    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);

    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    $stmt->close();
} else {
    echo "User ID is not set.";
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["fetch"])) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read=FALSE ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($notifications);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mark_read"])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div id="notification-container">
    <div id="notification-icon" onclick="markNotificationsAsRead()">
        <span class="bell">🔔</span>
        <span id="notif-count" class="count"><?= count($notifications) ?></span>
    </div>

    <div id="notif-dropdown" class="dropdown">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notif-item <?= $notif['type'] ?>">
                    <?= $notif["message"] ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="notif-item">No new notifications</div>
        <?php endif; ?>
    </div>
</div>

<script>
    function fetchNotification() {
        fetch("test_notifications.php?fetch=true")
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
        fetch("test_notifications.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "mark_read=true"
        }).then(() => {
            document.getElementById("notif-count").innerText = "0";
        });
    };

    fetch("test_notifications.php?fetch=true&cache=" + new Date().getTime())

    setInterval(fetchNotification, 10000);
</script>