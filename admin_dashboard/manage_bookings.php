<?php
session_start();

$db = new SQLite3(__DIR__ . '/../luckynest.db');

$feedback = '';
$bookingData = [];
$guestOptions = [];
$roomOptions = [];

$guestResult = $db->query("SELECT user_id, forename, surname FROM users WHERE role = 'guest'");
while ($row = $guestResult->fetchArray(SQLITE3_ASSOC)) {
    $guestOptions[] = $row;
}

$roomResult = $db->query("SELECT r.room_id, r.room_number, rt.room_type_name 
                          FROM rooms r 
                          JOIN room_types rt ON r.room_type_id = rt.room_type_id");
while ($row = $roomResult->fetchArray(SQLITE3_ASSOC)) {
    $roomOptions[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // This if elseif elseif is the logic for adding, editing, and deleting values in the database
        if ($action === 'add') {
            $roomId = $_POST['room_id'];
            $guestId = $_POST['guest_id'];
            $checkInDate = $_POST['check_in_date'];
            $checkOutDate = $_POST['check_out_date'];

            $checkIn = new DateTime($checkInDate);
            $checkOut = new DateTime($checkOutDate);

            $formattedCheckInDate = $checkIn->format('Y-m-d');
            $formattedCheckOutDate = $checkOut->format('Y-m-d');

            $nights = $checkIn->diff($checkOut)->days;

            $roomPriceQuery = $db->prepare("SELECT rt.rate_monthly 
                                            FROM rooms r 
                                            JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                                            WHERE r.room_id = :roomId");
            $roomPriceQuery->bindValue(':roomId', $roomId, SQLITE3_INTEGER);
            $roomPriceResult = $roomPriceQuery->execute()->fetchArray(SQLITE3_ASSOC);
            $pricePerNight = $roomPriceResult['rate_monthly'];

            $totalPrice = $nights * $pricePerNight;

            $isCancelled = isset($_POST['booking_is_cancelled']) ? 1 : 0;
            $isPaid = isset($_POST['booking_is_paid']) ? 1 : 0;

            $overlapQuery = $db->prepare("SELECT COUNT(*) as count 
                                          FROM bookings 
                                          WHERE room_id = :roomId 
                                          AND booking_is_cancelled = 0 
                                          AND ((check_in_date <= :checkOutDate AND check_out_date >= :checkInDate))");
            $overlapQuery->bindValue(':roomId', $roomId, SQLITE3_INTEGER);
            $overlapQuery->bindValue(':checkInDate', $formattedCheckInDate, SQLITE3_TEXT);
            $overlapQuery->bindValue(':checkOutDate', $formattedCheckOutDate, SQLITE3_TEXT);
            $overlapResult = $overlapQuery->execute()->fetchArray(SQLITE3_ASSOC);

            if ($overlapResult['count'] > 0) {
                $feedback = 'Error: Room is already booked for the selected dates.';
            } else {
                $stmt = $db->prepare("INSERT INTO bookings (room_id, guest_id, check_in_date, check_out_date, total_price, booking_is_cancelled, booking_is_paid) 
                                      VALUES (:roomId, :guestId, :checkInDate, :checkOutDate, :totalPrice, :isCancelled, :isPaid)");
                $stmt->bindValue(':roomId', $roomId, SQLITE3_INTEGER);
                $stmt->bindValue(':guestId', $guestId, SQLITE3_INTEGER);
                $stmt->bindValue(':checkInDate', $formattedCheckInDate, SQLITE3_TEXT);
                $stmt->bindValue(':checkOutDate', $formattedCheckOutDate, SQLITE3_TEXT);
                $stmt->bindValue(':totalPrice', $totalPrice, SQLITE3_FLOAT);
                $stmt->bindValue(':isCancelled', $isCancelled, SQLITE3_INTEGER);
                $stmt->bindValue(':isPaid', $isPaid, SQLITE3_INTEGER);

                if ($stmt->execute()) {
                    $feedback = 'Booking added successfully!';
                } else {
                    $feedback = 'Error adding booking.';
                }
            }
        } elseif ($action === 'edit') {
            if (isset($_POST['booking_id']) && isset($_POST['guest_id'])) {
                $bookingId = $_POST['booking_id'];
                $roomId = $_POST['room_id'];
                $guestId = $_POST['guest_id'];
                $checkInDate = $_POST['check_in_date'];
                $checkOutDate = $_POST['check_out_date'];

                $checkIn = new DateTime($checkInDate);
                $checkOut = new DateTime($checkOutDate);

                $formattedCheckInDate = $checkIn->format('Y-m-d');
                $formattedCheckOutDate = $checkOut->format('Y-m-d');

                $nights = $checkIn->diff($checkOut)->days;

                $roomPriceQuery = $db->prepare("SELECT rt.rate_monthly 
                                                FROM rooms r 
                                                JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                                                WHERE r.room_id = :roomId");
                $roomPriceQuery->bindValue(':roomId', $roomId, SQLITE3_INTEGER);
                $roomPriceResult = $roomPriceQuery->execute()->fetchArray(SQLITE3_ASSOC);
                $pricePerNight = $roomPriceResult['rate_monthly'];

                $totalPrice = $nights * $pricePerNight;

                $isCancelled = isset($_POST['booking_is_cancelled']) ? 1 : 0;
                $isPaid = isset($_POST['booking_is_paid']) ? 1 : 0;

                $stmt = $db->prepare("UPDATE bookings 
                                      SET room_id = :roomId, 
                                          guest_id = :guestId, 
                                          check_in_date = :checkInDate, 
                                          check_out_date = :checkOutDate, 
                                          total_price = :totalPrice, 
                                          booking_is_cancelled = :isCancelled, 
                                          booking_is_paid = :isPaid 
                                      WHERE booking_id = :bookingId");
                $stmt->bindValue(':bookingId', $bookingId, SQLITE3_INTEGER);
                $stmt->bindValue(':roomId', $roomId, SQLITE3_INTEGER);
                $stmt->bindValue(':guestId', $guestId, SQLITE3_INTEGER);
                $stmt->bindValue(':checkInDate', $formattedCheckInDate, SQLITE3_TEXT);
                $stmt->bindValue(':checkOutDate', $formattedCheckOutDate, SQLITE3_TEXT);
                $stmt->bindValue(':totalPrice', $totalPrice, SQLITE3_FLOAT);
                $stmt->bindValue(':isCancelled', $isCancelled, SQLITE3_INTEGER);
                $stmt->bindValue(':isPaid', $isPaid, SQLITE3_INTEGER);

                if ($stmt->execute()) {
                    $feedback = 'Booking updated successfully!';
                } else {
                    $feedback = 'Error updating booking.';
                }
            }
        } elseif ($action === 'delete') {
            $bookingId = $_POST['booking_id'];

            $stmt = $db->prepare("DELETE FROM bookings WHERE booking_id = :bookingId");
            $stmt->bindValue(':bookingId', $bookingId, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                $feedback = 'Booking deleted successfully!';
            } else {
                $feedback = 'Error deleting booking.';
            }
        }
    }
}

$result = $db->query("SELECT b.*, r.room_number, rt.room_type_name, u.forename, u.surname
                      FROM bookings b 
                      JOIN rooms r ON b.room_id = r.room_id 
                      JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                      JOIN users u ON b.guest_id = u.user_id");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $bookingData[] = $row;
}

$db->close();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles.css">
    <title>Manage Bookings</title>
</head>

<body>
    <div class="manage-default">
        <h1>Manage Bookings</h1>
        <?php if ($feedback): ?>
            <p style="color: green;"><?php echo $feedback; ?></p>
        <?php endif; ?>

        <h2>Add New Booking</h2>
        <form method="POST" action="manage_bookings.php">
            <input type="hidden" name="action" value="add">
            <label for="room_id">Room:</label>
            <select id="room_id" name="room_id" required>
                <?php foreach ($roomOptions as $room): ?>
                    <option value="<?php echo $room['room_id']; ?>">
                        Room <?php echo $room['room_number']; ?> (<?php echo $room['room_type_name']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="guest_id">Guest:</label>
            <select id="guest_id" name="guest_id" required>
                <?php foreach ($guestOptions as $guest): ?>
                    <option value="<?php echo $guest['user_id']; ?>">
                        <?php echo $guest['forename'] . ' ' . $guest['surname']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="check_in_date">Check-in Date:</label>
            <input type="date" id="check_in_date" name="check_in_date" required>
            <label for="check_out_date">Check-out Date:</label>
            <input type="date" id="check_out_date" name="check_out_date" required>
            <label for="booking_is_cancelled">
                <input type="checkbox" name="booking_is_cancelled"> Cancelled
            </label>
            <label for="booking_is_paid">
                <input type="checkbox" name="booking_is_paid"> Paid
            </label>
            <button type="submit" class="update-button">Add Booking</button>
        </form>

        <h2>Booking List</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Room</th>
                    <th>Room Type</th>
                    <th>Guest</th>
                    <th>Check-in Date</th>
                    <th>Check-out Date</th>
                    <th>Total Price</th>
                    <th>Cancelled</th>
                    <th>Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookingData as $booking): ?>
                    <tr>
                        <td><?php echo $booking['booking_id']; ?></td>
                        <td><?php echo $booking['room_number']; ?></td>
                        <td><?php echo $booking['room_type_name']; ?></td>
                        <td><?php echo $booking['forename'] . ' ' . $booking['surname']; ?></td>
                        <td><?php echo $booking['check_in_date']; ?></td>
                        <td><?php echo $booking['check_out_date']; ?></td>
                        <td><?php echo $booking['total_price']; ?></td>
                        <td><?php echo $booking['booking_is_cancelled'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $booking['booking_is_paid'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <form method="POST" action="manage_bookings.php" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                <input type="hidden" name="guest_id" value="<?php echo $booking['guest_id']; ?>">

                                <label for="room_id_<?php echo $booking['booking_id']; ?>">Room ID:</label>
                                <input type="text" id="room_id_<?php echo $booking['booking_id']; ?>" name="room_id"
                                    value="<?php echo $booking['room_id']; ?>" required>

                                <label for="check_in_date_<?php echo $booking['booking_id']; ?>">Check-In Date:</label>
                                <input type="date" id="check_in_date_<?php echo $booking['booking_id']; ?>"
                                    name="check_in_date" value="<?php echo $booking['check_in_date']; ?>" required>

                                <label for="check_out_date_<?php echo $booking['booking_id']; ?>">Check-Out Date:</label>
                                <input type="date" id="check_out_date_<?php echo $booking['booking_id']; ?>"
                                    name="check_out_date" value="<?php echo $booking['check_out_date']; ?>" required>

                                <label for="booking_is_cancelled_<?php echo $booking['booking_id']; ?>">
                                    <input type="checkbox" id="booking_is_cancelled_<?php echo $booking['booking_id']; ?>"
                                        name="booking_is_cancelled" <?php echo $booking['booking_is_cancelled'] ? 'checked' : ''; ?>> Cancelled
                                </label>

                                <label for="booking_is_paid_<?php echo $booking['booking_id']; ?>">
                                    <input type="checkbox" id="booking_is_paid_<?php echo $booking['booking_id']; ?>"
                                        name="booking_is_paid" <?php echo $booking['booking_is_paid'] ? 'checked' : ''; ?>>
                                    Paid
                                </label>

                                <button type="submit" class="update-button">Update</button>
                            </form>

                            <form method="POST" action="manage_bookings.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                <button type="submit" class="update-button"
                                    onclick="return confirm('Are you sure?')">Delete</button>
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