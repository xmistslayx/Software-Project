<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view this page.");
}

require __DIR__ . "/../include/db.php";

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT i.invoice_id, i.invoice_number, i.amount, i.filename, i.created_at, i.type, b.booking_id, r.room_number
        FROM invoices i
        JOIN bookings b ON i.booking_id = b.booking_id
        JOIN rooms r ON b.room_id = r.room_id
        WHERE i.user_id = :user_id
        ORDER BY i.created_at DESC
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$rent_invoices = array_filter($invoices, function ($invoice) {
    return $invoice['type'] === 'rent';
});

$food_invoices = array_filter($invoices, function ($invoice) {
    return $invoice['type'] === 'food';
});

$laundry_invoices = array_filter($invoices, function ($invoice) {
    return $invoice['type'] === 'laundry';
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Invoices</title>
</head>

<body>
    <h1>Rent Payments</h1>
    <?php if (empty($rent_invoices)): ?>
        <p>No rent invoices found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Booking ID</th>
                    <th>Room Number</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rent_invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['room_number']); ?></td>
                        <td>£<?php echo number_format($invoice['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($invoice['created_at']); ?></td>
                        <td>
                            <a href="../invoices/<?php echo htmlspecialchars($invoice['filename']); ?>"
                                target="_blank">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h1>Food Payments</h1>
    <?php if (empty($food_invoices)): ?>
        <p>No food invoices found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Booking ID</th>
                    <th>Room Number</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($food_invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['room_number']); ?></td>
                        <td>£<?php echo number_format($invoice['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($invoice['created_at']); ?></td>
                        <td>
                            <a href="../invoices/<?php echo htmlspecialchars($invoice['filename']); ?>"
                                target="_blank">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h1>Laundry Payments</h1>
    <?php if (empty($laundry_invoices)): ?>
        <p>No laundry invoices found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Booking ID</th>
                    <th>Room Number</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($laundry_invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['room_number']); ?></td>
                        <td>£<?php echo number_format($invoice['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($invoice['created_at']); ?></td>
                        <td>
                            <a href="../invoices/<?php echo htmlspecialchars($invoice['filename']); ?>"
                                target="_blank">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>