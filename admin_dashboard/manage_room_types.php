<?php
session_start();

$db = new SQLite3(__DIR__ . '/../luckynest.db');

$feedback = '';
$roomTypeData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Logic for adding, editing, and deleting values in the database
        if ($action === 'add') {
            $roomTypeName = $_POST['room_type_name'];
            $rateMonthly = $_POST['rate_monthly'];

            $stmt = $db->prepare("INSERT INTO room_types (room_type_name, rate_monthly) VALUES (:roomTypeName, :rateMonthly)");
            $stmt->bindValue(':roomTypeName', $roomTypeName, SQLITE3_TEXT);
            $stmt->bindValue(':rateMonthly', $rateMonthly, SQLITE3_FLOAT);

            if ($stmt->execute()) {
                $feedback = 'Room type added successfully!';
            } else {
                $feedback = 'Error adding room type.';
            }
        } 
        elseif ($action === 'edit') {
            $id = $_POST['room_type_id'];
            $roomTypeName = $_POST['room_type_name'];
            $rateMonthly = $_POST['rate_monthly'];

            $stmt = $db->prepare("UPDATE room_types SET room_type_name = :roomTypeName, rate_monthly = :rateMonthly WHERE room_type_id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->bindValue(':roomTypeName', $roomTypeName, SQLITE3_TEXT);
            $stmt->bindValue(':rateMonthly', $rateMonthly, SQLITE3_FLOAT);

            if ($stmt->execute()) {
                $feedback = 'Room type updated successfully!';
            } else {
                $feedback = 'Error updating room type.';
            }
        } 
        elseif ($action === 'delete') {
            $id = $_POST['room_type_id'];

            $stmt = $db->prepare("DELETE FROM room_types WHERE room_type_id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                $feedback = 'Room type deleted successfully!';
            } else {
                $feedback = 'Error deleting room type.';
            }
        }
    }
}

$result = $db->query("SELECT * FROM room_types");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $roomTypeData[] = $row;
}

$db->close();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Room Types</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="manage-default">
        <h1>Manage Room Types</h1>
        <?php if ($feedback): ?>
            <p style="color: green;"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <!-- Add Room Type Form -->
        <h2>Add New Room Type</h2>
        <form method="POST" action="manage_room_types.php">
            <input type="hidden" name="action" value="add">
            <label for="room_type_name">Room Type Name:</label>
            <input type="text" id="room_type_name" name="room_type_name" required>
            <label for="rate_monthly">Monthly Rate:</label>
            <input type="number" step="0.01" id="rate_monthly" name="rate_monthly" required>
            <button type="submit" class="update-button">Add Room Type</button>
        </form>

        <!-- Room Type List -->
        <h2>Room Type List</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room Type Name</th>
                    <th>Monthly Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roomTypeData as $roomType): ?>
                    <tr>
                        <td><?php echo $roomType['room_type_id']; ?></td>
                        <td><?php echo $roomType['room_type_name']; ?></td>
                        <td><?php echo $roomType['rate_monthly']; ?></td>
                        <td>
                            <!-- Edit Form -->
                            <form method="POST" action="manage_room_types.php" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="room_type_id" value="<?php echo $roomType['room_type_id']; ?>">
                                <input type="text" name="room_type_name" value="<?php echo $roomType['room_type_name']; ?>" required>
                                <input type="number" step="0.01" name="rate_monthly" value="<?php echo $roomType['rate_monthly']; ?>" required>
                                <button type="submit" class="update-button">Update</button>
                            </form>

                            <!-- Delete Form -->
                            <form method="POST" action="manage_room_types.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="room_type_id" value="<?php echo $roomType['room_type_id']; ?>">
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
