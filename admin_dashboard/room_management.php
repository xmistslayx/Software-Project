<?php
session_start();

$db = new SQLite3(__DIR__ . '/../luckynest.db');

$feedback = '';
$roomData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Adding a new room
        if ($action === 'add') {
            $roomNumber = $_POST['room_number'];
            $type = $_POST['type'];
            $price = $_POST['price'];
            $status = $_POST['status'];

            $stmt = $db->prepare("INSERT INTO rooms (room_number, type, price, status) VALUES (:roomNumber, :type, :price, :status)");
            $stmt->bindValue(':roomNumber', $roomNumber, SQLITE3_TEXT);
            $stmt->bindValue(':type', $type, SQLITE3_TEXT);
            $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);

            if ($stmt->execute()) {
                $feedback = 'Room added successfully!';
            } else {
                $feedback = 'Error adding room.';
            }
        }
        // Editing an existing room
        elseif ($action === 'edit') {
            $roomId = $_POST['room_id'];
            $roomNumber = $_POST['room_number'];
            $type = $_POST['type'];
            $price = $_POST['price'];
            $status = $_POST['status'];

            $stmt = $db->prepare("UPDATE rooms SET room_number = :roomNumber, type = :type, price = :price, status = :status WHERE room_id = :roomId");
            $stmt->bindValue(':roomId', $roomId, SQLITE3_INTEGER);
            $stmt->bindValue(':roomNumber', $roomNumber, SQLITE3_TEXT);
            $stmt->bindValue(':type', $type, SQLITE3_TEXT);
            $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);

            if ($stmt->execute()) {
                $feedback = 'Room updated successfully!';
            } else {
                $feedback = 'Error updating room.';
            }
        }
        // Deleting a room
        elseif ($action === 'delete') {
            $roomId = $_POST['room_id'];

            $stmt = $db->prepare("DELETE FROM rooms WHERE room_id = :roomId");
            $stmt->bindValue(':roomId', $roomId, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                $feedback = 'Room deleted successfully!';
            } else {
                $feedback = 'Error deleting room.';
            }
        }
    }
}

// Fetch all rooms
$result = $db->query("SELECT * FROM rooms");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $roomData[] = $row;
}

$db->close();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="manage-default">
        <h1>Manage Rooms</h1>
        <?php if ($feedback): ?>
            <p style="color: green;"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <!-- Add Room Form -->
        <h2>Add New Room</h2>
        <form method="POST" action="manage_rooms.php">
            <input type="hidden" name="action" value="add">
            <label for="room_number">Room Number:</label>
            <input type="text" id="room_number" name="room_number" required>
            <label for="type">Room Type:</label>
            <select id="type" name="type" required>
                <option value="Single">Single</option>
                <option value="Double">Double</option>
                <option value="Triple">Triple</option>
            </select>
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="Available">Available</option>
                <option value="Occupied">Occupied</option>
            </select>
            <button type="submit" class="update-button">Add Room</button>
        </form>

        <!-- Room List -->
        <h2>Room List</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Room ID</th>
                    <th>Room Number</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roomData as $room): ?>
                    <tr>
                        <td><?php echo $room['room_id']; ?></td>
                        <td><?php echo $room['room_number']; ?></td>
                        <td><?php echo $room['type']; ?></td>
                        <td><?php echo $room['price']; ?></td>
                        <td><?php echo $room['status']; ?></td>
                        <td>
                            <!-- Edit Form -->
                            <form method="POST" action="manage_rooms.php" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                <input type="text" name="room_number" value="<?php echo $room['room_number']; ?>" required>
                                <select name="type" required>
                                    <option value="Single" <?php echo $room['type'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Double" <?php echo $room['type'] === 'Double' ? 'selected' : ''; ?>>Double</option>
                                    <option value="Triple" <?php echo $room['type'] === 'Triple' ? 'selected' : ''; ?>>Triple</option>
                                </select>
                                <input type="number" name="price" value="<?php echo $room['price']; ?>" step="0.01" required>
                                <select name="status" required>
                                    <option value="Available" <?php echo $room['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="Occupied" <?php echo $room['status'] === 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
                                </select>
                                <button type="submit" class="update-button">Update</button>
                            </form>

                            <!-- Delete Form -->
                            <form method="POST" action="manage_rooms.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                <button type="submit" class="update-button" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <a href="admin_dashboard.php" class="button">Back to Dashboard</a>
    </div>
</body>

</html>