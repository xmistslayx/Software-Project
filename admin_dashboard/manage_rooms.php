<?php
session_start();

$db = new SQLite3(__DIR__ . '/../luckynest.db');

$feedback = '';
$roomData = [];
$roomTypeOptions = [];

$roomTypeResult = $db->query("SELECT room_type_id, room_type_name FROM room_types");
while ($row = $roomTypeResult->fetchArray(SQLITE3_ASSOC)) {
    $roomTypeOptions[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // CRUD Logic
        if ($action === 'add') {
            $roomNumber = $_POST['room_number'];
            $roomTypeId = $_POST['room_type_id'];
            $status = $_POST['status'];
            $roomIsAvailable = isset($_POST['room_is_available']) ? 1 : 0;

            $stmt = $db->prepare("INSERT INTO rooms (room_number, room_type_id, status, room_is_available) 
                                  VALUES (:roomNumber, :roomTypeId, :status, :roomIsAvailable)");
            $stmt->bindValue(':roomNumber', $roomNumber, SQLITE3_TEXT);
            $stmt->bindValue(':roomTypeId', $roomTypeId, SQLITE3_INTEGER);
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);
            $stmt->bindValue(':roomIsAvailable', $roomIsAvailable, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                $feedback = 'Room added successfully!';
            } else {
                $feedback = 'Error adding room.';
            }
        }
        elseif ($action === 'edit') {
            $roomId = $_POST['room_id'];
            $roomNumber = $_POST['room_number'];
            $roomTypeId = $_POST['room_type_id'];
            $status = $_POST['status'];
            $roomIsAvailable = isset($_POST['room_is_available']) ? 1 : 0;

            $stmt = $db->prepare("UPDATE rooms 
                                  SET room_number = :roomNumber, 
                                      room_type_id = :roomTypeId, 
                                      status = :status, 
                                      room_is_available = :roomIsAvailable 
                                  WHERE room_id = :roomId");
            $stmt->bindValue(':roomId', $roomId, SQLITE3_INTEGER);
            $stmt->bindValue(':roomNumber', $roomNumber, SQLITE3_TEXT);
            $stmt->bindValue(':roomTypeId', $roomTypeId, SQLITE3_INTEGER);
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);
            $stmt->bindValue(':roomIsAvailable', $roomIsAvailable, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                $feedback = 'Room updated successfully!';
            } else {
                $feedback = 'Error updating room.';
            }
        }
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

$result = $db->query("SELECT r.room_id, r.room_number, r.room_is_available, r.status, rt.room_type_name 
                      FROM rooms r 
                      JOIN room_types rt ON r.room_type_id = rt.room_type_id");
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
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/scripts.js"></script>
    <title>Manage Rooms</title>
</head>

<body>
    <div class="manage-default">
        <h1>Manage Rooms</h1>
        <?php if ($feedback): ?>
            <p style="color: green;"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <button onclick="toggleAddForm()" class="update-button">Add Room</button>

        <div id="add-form">
            <h2>Add New Room</h2>
            <form method="POST" action="manage_rooms.php">
                <input type="hidden" name="action" value="add">
                <label for="room_number">Room Number:</label>
                <input type="text" id="room_number" name="room_number" required>
                <label for="room_type_id">Room Type:</label>
                <select id="room_type_id" name="room_type_id" required>
                    <?php foreach ($roomTypeOptions as $roomType): ?>
                        <option value="<?php echo $roomType['room_type_id']; ?>">
                            <?php echo $roomType['room_type_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                </select>
                <label for="room_is_available">
                    <input type="checkbox" id="room_is_available" name="room_is_available"> Available
                </label>
                <button type="submit" class="update-button">Add Room</button>
            </form>
        </div>

        <h2>Room List</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Room ID</th>
                    <th>Room Number</th>
                    <th>Room Type</th>
                    <th>Status</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roomData as $room): ?>
                    <tr>
                        <td><?php echo $room['room_id']; ?></td>
                        <td><?php echo $room['room_number']; ?></td>
                        <td><?php echo $room['room_type_name']; ?></td>
                        <td><?php echo $room['status']; ?></td>
                        <td><?php echo $room['room_is_available'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <button onclick="toggleEditForm(<?php echo $room['room_id']; ?>)" class="update-button">Edit</button>
                            <div id="edit-form-<?php echo $room['room_id']; ?>" class="edit-form">
                                <form method="POST" action="manage_rooms.php" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                    <label for="room_number_<?php echo $room['room_id']; ?>">Room Number:</label>
                                    <input type="text" id="room_number_<?php echo $room['room_id']; ?>" name="room_number" value="<?php echo $room['room_number']; ?>" required>
                                    <label for="room_type_id_<?php echo $room['room_id']; ?>">Room Type:</label>
                                    <select id="room_type_id_<?php echo $room['room_id']; ?>" name="room_type_id" required>
                                        <?php foreach ($roomTypeOptions as $roomType): ?>
                                            <option value="<?php echo $roomType['room_type_id']; ?>" 
                                                <?php echo $roomType['room_type_name'] === $room['room_type_name'] ? 'selected' : ''; ?>>
                                                <?php echo $roomType['room_type_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="status_<?php echo $room['room_id']; ?>">Status:</label>
                                    <select id="status_<?php echo $room['room_id']; ?>" name="status" required>
                                        <option value="Available" <?php echo $room['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="Occupied" <?php echo $room['status'] === 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
                                    </select>
                                    <label for="room_is_available_<?php echo $room['room_id']; ?>">
                                        <input type="checkbox" id="room_is_available_<?php echo $room['room_id']; ?>" 
                                               name="room_is_available" <?php echo $room['room_is_available'] ? 'checked' : ''; ?>> Available
                                    </label>
                                    <button type="submit" class="update-button">Update</button>
                                </form>

                                <form method="POST" action="manage_rooms.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                    <button type="submit" class="update-button" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
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