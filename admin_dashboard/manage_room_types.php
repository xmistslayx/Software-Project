<?php
// TODO Add the following:
// 1. A search function
// 2. A filter function

session_start();

if ($_SESSION['role'] == 'guest' || !isset($_SESSION['role'])) {
    header('Location: unauthorized.php');
    exit();
}

include __DIR__ . '/../include/db.php';
include __DIR__ . '/../include/pagination.php';

$feedback = '';
$roomTypeData = [];

$recordsPerPage = 10;
$page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
$offset = ($page - 1) * $recordsPerPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // This if elseif elseif is the CRUD Logic
        if ($action === 'add') {
            $roomTypeName = $_POST['room_type_name'];
            $rateMonthly = $_POST['rate_monthly'];

            $stmt = $conn->prepare("INSERT INTO room_types (room_type_name, rate_monthly) VALUES (:roomTypeName, :rateMonthly)");
            $stmt->bindParam(':roomTypeName', $roomTypeName, PDO::PARAM_STR);
            $stmt->bindParam(':rateMonthly', $rateMonthly, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $feedback = 'Room type added successfully!';
            } else {
                $feedback = 'Error adding room type.';
            }
        } elseif ($action === 'edit') {
            $id = $_POST['room_type_id'];
            $roomTypeName = $_POST['room_type_name'];
            $rateMonthly = $_POST['rate_monthly'];

            $stmt = $conn->prepare("UPDATE room_types SET room_type_name = :roomTypeName, rate_monthly = :rateMonthly WHERE room_type_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':roomTypeName', $roomTypeName, PDO::PARAM_STR);
            $stmt->bindParam(':rateMonthly', $rateMonthly, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $feedback = 'Room type updated successfully!';
            } else {
                $feedback = 'Error updating thee room type.';
            }
        } elseif ($action === 'delete') {
            $id = $_POST['room_type_id'];

            $stmt = $conn->prepare("DELETE FROM room_types WHERE room_type_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $feedback = 'Room type deleted successfully!';
            } else {
                $feedback = 'Error deleting the room type.';
            }
        }
    }
}

$result = $conn->query("SELECT * FROM room_types LIMIT $recordsPerPage OFFSET $offset");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $roomTypeData[] = $row;
}

$totalRecordsQuery = $conn->query("SELECT COUNT(*) As total FROM room_types");
$totalRecords = $totalRecordsQuery->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

$conn = null;
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/scripts.js"></script>
    <title>Manage Room Types</title>
</head>

<body>
    <div class="manage-default">
        <h1>Manage Room Types</h1>
        <?php if ($feedback): ?>
            <p style="color: green;"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <button onclick="toggleAddForm()" class="update-button">Add Room Type</button>

        <div id="add-form">
            <h2>Add New Room Type</h2>
            <form method="POST" action="manage_room_types.php">
                <input type="hidden" name="action" value="add">
                <label for="room_type_name">Room Type Name:</label>
                <input type="text" id="room_type_name" name="room_type_name" required>
                <label for="rate_monthly">Monthly Rate:</label>
                <input type="number" step="0.01" id="rate_monthly" name="rate_monthly" required>
                <button type="submit" class="update-button">Add Room Type</button>
            </form>
        </div>

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
                            <button onclick="toggleEditForm(<?php echo $roomType['room_type_id']; ?>)"
                                class="update-button">Edit</button>
                            <div id="edit-form-<?php echo $roomType['room_type_id']; ?>" class="edit-form">
                                <form method="POST" action="manage_room_types.php" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="room_type_id"
                                        value="<?php echo $roomType['room_type_id']; ?>">
                                    <label for="room_type_name_<?php echo $roomType['room_type_id']; ?>">Room Type
                                        Name:</label>
                                    <input type="text" id="room_type_name_<?php echo $roomType['room_type_id']; ?>"
                                        name="room_type_name" value="<?php echo $roomType['room_type_name']; ?>" required>
                                    <label for="rate_monthly_<?php echo $roomType['room_type_id']; ?>">Monthly Rate:</label>
                                    <input type="number" step="0.01"
                                        id="rate_monthly_<?php echo $roomType['room_type_id']; ?>" name="rate_monthly"
                                        value="<?php echo $roomType['rate_monthly']; ?>" required>
                                    <button type="submit" class="update-button">Update</button>
                                </form>

                                <form method="POST" action="manage_room_types.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="room_type_id"
                                        value="<?php echo $roomType['room_type_id']; ?>">
                                    <button type="submit" class="update-button"
                                        onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        $url = 'manage_room_types.php';
        echo generatePagination($page, $totalPages, $url);
        ?>

        <br>
        <a href="admin_dashboard.php" class="button">Back to Dashboard</a>
    </div>
</body>

</html>