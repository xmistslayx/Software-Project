<?php
session_start();

$db = new SQLite3(__DIR__ . '/../luckynest.db');

$feedback = '';
$userData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            // Check if all required fields are present
            if (isset($_POST['forename'], $_POST['surname'], $_POST['email'], $_POST['phone'], $_POST['emergency_contact'], $_POST['address'], $_POST['role'])) {
                $forename = $_POST['forename'];
                $surname = $_POST['surname'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $emergencyContact = $_POST['emergency_contact'];
                $address = $_POST['address'];
                $role = $_POST['role'];

                $stmt = $db->prepare("INSERT INTO users (forename, surname, email, phone, emergency_contact, address, role) VALUES (:forename, :surname, :email, :phone, :emergencyContact, :address, :role)");
                $stmt->bindValue(':forename', $forename, SQLITE3_TEXT);
                $stmt->bindValue(':surname', $surname, SQLITE3_TEXT);
                $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
                $stmt->bindValue(':emergencyContact', $emergencyContact, SQLITE3_TEXT);
                $stmt->bindValue(':address', $address, SQLITE3_TEXT);
                $stmt->bindValue(':role', $role, SQLITE3_TEXT);

                if ($stmt->execute()) {
                    $feedback = 'User added successfully!';
                } else {
                    $feedback = 'Error adding user.';
                }
            } else {
                $feedback = 'Missing required fields for adding a user.';
            }
        } elseif ($action === 'edit') {
            // Check if all required fields are present
            if (isset($_POST['user_id'], $_POST['forename'], $_POST['surname'], $_POST['email'], $_POST['phone'], $_POST['emergency_contact'], $_POST['address'], $_POST['role'])) {
                $id = $_POST['user_id'];
                $forename = $_POST['forename'];
                $surname = $_POST['surname'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $emergencyContact = $_POST['emergency_contact'];
                $address = $_POST['address'];
                $role = $_POST['role'];

                $stmt = $db->prepare("UPDATE users SET forename = :forename, surname = :surname, email = :email, phone = :phone, emergency_contact = :emergencyContact, address = :address, role = :role WHERE user_id = :id");
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $stmt->bindValue(':forename', $forename, SQLITE3_TEXT);
                $stmt->bindValue(':surname', $surname, SQLITE3_TEXT);
                $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
                $stmt->bindValue(':emergencyContact', $emergencyContact, SQLITE3_TEXT);
                $stmt->bindValue(':address', $address, SQLITE3_TEXT);
                $stmt->bindValue(':role', $role, SQLITE3_TEXT);

                if ($stmt->execute()) {
                    $feedback = 'User updated successfully!';
                } else {
                    $feedback = 'Error updating user.';
                }
            } else {
                $feedback = 'Missing required fields for editing a user.';
            }
        } elseif ($action === 'delete') {
            // Check if the user_id is present
            if (isset($_POST['user_id'])) {
                $id = $_POST['user_id'];

                $stmt = $db->prepare("DELETE FROM users WHERE user_id = :id");
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

                if ($stmt->execute()) {
                    $feedback = 'User deleted successfully!';
                } else {
                    $feedback = 'Error deleting user.';
                }
            } else {
                $feedback = 'Missing user ID for deletion.';
            }
        }
    }
}

$result = $db->query("SELECT * FROM users");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $userData[] = $row;
}

$db->close();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
</head>

<body>
    <div class="manage-default">
        <h1>Manage Users</h1>
        <?php if ($feedback): ?>
            <p style="color: green;" class="feedback-message"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <h2>Add New User</h2>
        <form method="POST" class="add-user-form" action="guest_management.php">
            <input type="hidden" name="action" value="add">
            <label for="forename">Forename:</label>
            <input type="text" name="forename" required>
            <label for="surname">Surname:</label>
            <input type="text" name="surname" required>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <label for="phone">Phone:</label>
            <input type="text" name="phone" required>
            <label for="emergency_contact">Emergency Contact:</label>
            <input type="text" name="emergency_contact" required>
            <label for="address">Address:</label>
            <input type="text" name="address" required>
            <label for="role">Role:</label>
            <select name="role" required>
                <option value="guest">Guest</option>
                <option value="admin">Admin</option>
                <option value="owner">Owner</option>
            </select>
            <button type="submit" class="update-button">Add User</button>
        </form>

        <h3>User List</h3>
        <table border="1">
            <thead class="table-columns">
                <tr>
                    <th>ID</th>
                    <th>Forename</th>
                    <th>Surname</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Emergency Contact</th>
                    <th>Address</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userData as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo $user['forename']; ?></td>
                        <td><?php echo $user['surname']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['phone']; ?></td>
                        <td><?php echo $user['emergency_contact']; ?></td>
                        <td><?php echo $user['address']; ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td>
                            <form method="POST" action="guest_management.php" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="text" name="forename" value="<?php echo $user['forename']; ?>" required>
                                <input type="text" name="surname" value="<?php echo $user['surname']; ?>" required>
                                <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                                <input type="text" name="phone" value="<?php echo $user['phone']; ?>" required>
                                <input type="text" name="emergency_contact" value="<?php echo $user['emergency_contact']; ?>" required>
                                <input type="text" name="address" value="<?php echo $user['address']; ?>" required>
                                <select name="role" required>
                                    <option value="guest" <?php echo $user['role'] === 'guest' ? 'selected' : ''; ?>>Guest</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="owner" <?php echo $user['role'] === 'owner' ? 'selected' : ''; ?>>Owner</option>
                                </select>
                                <button type="submit" class="update-button">Update</button>
                            </form>

                            <form method="POST" action="guest_management.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
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