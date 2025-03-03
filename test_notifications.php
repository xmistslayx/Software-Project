<?php
session_start();
require "db.php";

$user_id = 1;  // Manually set user id to test

if (isset($user_id)) {
    // Modified query to ensure 'type' is selected
    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "User ID is not set.";
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["fetch"])) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($notifications);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mark_read"])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="notification-container">
    <div id="notification-icon" onclick="markNotificationsAsRead()">
        <span class="bell">🔔</span>
        <span id="notif-count" class="count"><?= count($notifications) ?></span>
    </div>

    <div id="notif-dropdown" class="dropdown">
        <?php
        if (count($notifications) > 0):
            // Debugging: Uncomment to see the notification structure
            // var_dump($notifications);
            foreach ($notifications as $notif):
                // Ensure the 'type' field exists, else use 'default' or 'info'
                $type = isset($notif['type']) ? htmlspecialchars($notif['type']) : 'default';
                $message = htmlspecialchars($notif['message']);
        ?>
                <div class="notif-item <?= $type ?>"> 
                    <?= $message ?> 
                </div>
        <?php
            endforeach;
        else:
        ?>
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
                    div.classList.add("notif-item", notif.type || 'default');
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
