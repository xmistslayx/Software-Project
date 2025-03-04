<?php
$db = new SQLite3(__DIR__ . '/../luckynest.db');

function getGuests()
{
    global $db;
    $result = $db->query("SELECT * FROM users");
    $guests = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $guests[] = $row;
    }
    return $guests;
}

if (isset($_POST["submit"])) {
    $user_id = $_POST["user_id"];
    $forename = $_POST["forename"];
    $surname = $_POST["surname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $emergency_contact = $_POST["emergency_contact"];
    $address = $_POST["address"];
    $role = $_POST["role"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    if ($user_id) {
        $stmt = $db->prepare("UPDATE users SET forename=:forename, surname=:surname, email=:email, phone=:phone, emergency_contact=:emergency_contact, address=:address, role=:role, password=:password WHERE user_id=:user_id");
        $stmt->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
    } else {
        $stmt = $db->prepare("INSERT INTO users (forename, surname, email, phone, emergency_contact, address, role, password) VALUES (:forename, :surname, :email, :phone, :emergency_contact, :address, :rolee, :password)");
    }
    $stmt->bindValue(':forename', $forename, SQLITE3_TEXT);
    $stmt->bindValue(':surname', $surname, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
    $stmt->bindValue(':emergency_contact', $emergency_contact, SQLITE3_TEXT);
    $stmt->bindValue(':address', $address, SQLITE3_TEXT);
    $stmt->bindValue(':role', $role, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->execute();
}

if (isset($_GET["delete"])) {
    $user_id = $_GET["delete"];
    $stmt = $db_prepare("DELETE FROM users wHERE user+id=:user_id");
    $stmt->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
    $stmt->execute();
}

if (isset($_GET['edit'])) {
    $user_id = $_GET['edit'];
    $result = $db->querySingle("SELECT * FROM users WHERE user_id=$user_id", true);
    echo "<script>
            document.getElementById('user_id').value = '{$result['user_id']}';
            document.getElementById('forename').value = '{$result['forename']}';
            document.getElementById('surname').value = '{$result['surname']}';
            document.getElementById('email').value = '{$result['email']}';
            document.getElementById('phone').value = '{$result['phone']}';
            document.getElementById('emergency_contact').value = '{$result['emergency_contact']}';
            document.getElementById('address').value = '{$result['address']}';
            document.getElementById('role').value = '{$result['role']}';
            document.getElementById('password').value = '';
          </script>";
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Management</title>
</head>
<body>
    <h1>Guest Management</h1>

    <!-- Form to Add/Update Guest -->
    <form action="guest_management.php" method="post">
        <input type="hidden" name="user_id" id="user_id">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required><br><br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br><br>
        <label for="phone">Phone:</label>
        <input type="text" name="phone" id="phone" required><br><br>
        <label for="address">Address:</label>
        <input type="text" name="address" id="address" required><br><br>
        <label for="role">Role:</label>
        <select name="role" id="role">
            <option value="guest">Guest</option>
            <option value="admin">Admin</option>
            <option value="owner">Owner</option>
        </select><br><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>
        <button type="submit" name="submit">Submit</button>
    </form>

    <hr>

    <!-- Display Guest List -->
    <h2>Guest List</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php
        include 'guest_management.php';
        $guests = getGuests();
        foreach ($guests as $guest) {
            echo "<tr>
                    <td>{$guest['user_id']}</td>
                    <td>{$guest['name']}</td>
                    <td>{$guest['email']}</td>
                    <td>{$guest['phone']}</td>
                    <td>{$guest['address']}</td>
                    <td>{$guest['role']}</td>
                    <td>
                        <a href='guest_management.php?edit={$guest['user_id']}'>Edit</a> |
                        <a href='guest_management.php?delete={$guest['user_id']}'>Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>