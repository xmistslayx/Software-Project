<?php
require __DIR__ . "/../include/db.php";
require __DIR__ . "/../vendor/autoload.php";

if (!isset($_GET['session_id']) || !isset($_GET['booking_id'])) {
    die("Invalid request. Session ID or Booking ID is missing.");
}

$payment_type = $_GET['payment_type'];
$session_id = $_GET['session_id'];
$booking_id = $_GET['booking_id'];
$invoice_number = date('Ymd') . "_" . $booking_id;
$pdf_generated = false;

try {
    $stmt = $conn->prepare("SELECT b.*, u.forename, u.surname, u.email, u.address, u.phone, r.room_number 
                           FROM bookings b 
                           JOIN users u ON b.guest_id = u.user_id 
                           JOIN rooms r ON b.room_id = r.room_id 
                           WHERE b.booking_id = :booking_id");
    $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        die("Booking not found.");
    }

    $user_id = $booking['guest_id'];
    $room_id = $booking['room_id'];
    $check_in_date = $booking['check_in_date'];
    $check_out_date = $booking['check_out_date'];
    $guest_name = $booking['forename'] . ' ' . $booking['surname'];
    $guest_email = $booking['email'];
    $guest_address = $booking['address'];
    $guest_phone = $booking['phone'];
    $room_number = $booking['room_number'];

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

try {
    $stmt = $conn->prepare("SELECT rt.rate_monthly, rt.room_type_name 
                           FROM rooms r 
                           JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                           WHERE r.room_id = :room_id");
    $stmt->bindValue(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $roomrate = $result ? floatval($result['rate_monthly']) : 0;
    $room_type = $result ? $result['room_type_name'] : 'Standard Room';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$date1 = new DateTime($check_in_date);
$date2 = new DateTime($check_out_date);
$interval = $date1->diff($date2);
$days = $interval->days;

if ($payment_type == 'rent') {
    $amount = $roomrate;
} else if ($payment_type == 'food') {
    $amount = 50;
} else if ($payment_type == 'laundry') {
    $amount = 20;
}

try {
    $stmt = $conn->prepare("SELECT booking_is_paid FROM bookings WHERE booking_id = :booking_id");
    $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $isPaid = $stmt->fetchColumn();

    if (!$isPaid) {
        $stmt = $conn->prepare("UPDATE bookings SET booking_is_paid = 1 WHERE booking_id = :booking_id");
        $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("SELECT COUNT(*) FROM payments WHERE stripe_payment_id = :stripe_payment_id");
        $stmt->bindValue(':stripe_payment_id', $session_id, PDO::PARAM_STR);
        $stmt->execute();
        $paymentExists = $stmt->fetchColumn();

        if (!$paymentExists) {
            $stmt = $conn->prepare("INSERT INTO payments (user_id, room_id, amount, payment_date, stripe_payment_id) VALUES (:user_id, :room_id, :amount, :payment_date, :stripe_payment_id)");
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':room_id', $room_id, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindValue(':payment_date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':stripe_payment_id', $session_id, PDO::PARAM_STR);
            $stmt->execute();
            $payment_id = $conn->lastInsertId();

            $message = "Your payment for booking #$booking_id has been successfully processed.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)");
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':message', $message, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("SELECT payment_id FROM payments WHERE stripe_payment_id = :stripe_payment_id");
            $stmt->bindValue(':stripe_payment_id', $session_id, PDO::PARAM_STR);
            $stmt->execute();
            $payment_id = $stmt->fetchColumn();
        }
    } else {
        $stmt = $conn->prepare("SELECT payment_id FROM payments WHERE stripe_payment_id = :stripe_payment_id");
        $stmt->bindValue(':stripe_payment_id', $session_id, PDO::PARAM_STR);
        $stmt->execute();
        $payment_id = $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$pdf_filename = 'invoice_' . $invoice_number . '.pdf';
$pdf_path = __DIR__ . '/../invoices/' . $pdf_filename;

if (!file_exists($pdf_path)) {
    if (!file_exists(__DIR__ . '/../invoices/')) {
        mkdir(__DIR__ . '/../invoices/', 0755, true);
    }

    class PDF extends FPDF
    {
        function Header()
        {
            $this->SetFont('Arial', 'B', 18);
            $this->Cell(0, 10, 'Accommodation Invoice', 0, 1, 'C');
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 6, 'LuckyNest', 0, 1, 'C');
            $this->Cell(0, 6, '123 Main Street, City, Country', 0, 1, 'C');
            $this->Cell(0, 6, 'Phone: +44 1234 567890  |  Email: info@example.com', 0, 1, 'C');
            $this->Ln(10);
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(95, 8, 'Invoice To:', 0, 0);
    $pdf->Cell(95, 8, 'Invoice Details:', 0, 1);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(95, 6, $guest_name, 0, 0);
    $pdf->Cell(35, 6, 'Invoice Number:', 0, 0);
    $pdf->Cell(60, 6, $invoice_number, 0, 1);

    $pdf->Cell(95, 6, $guest_address, 0, 0);
    $pdf->Cell(35, 6, 'Payment Date:', 0, 0);
    $pdf->Cell(60, 6, date('d/m/Y'), 0, 1);

    $pdf->Cell(95, 6, 'Phone: ' . $guest_phone, 0, 0);
    $pdf->Cell(35, 6, 'Booking ID:', 0, 0);
    $pdf->Cell(60, 6, $booking_id, 0, 1);

    $pdf->Cell(95, 6, 'Email: ' . $guest_email, 0, 0);
    $pdf->Cell(35, 6, 'Payment ID:', 0, 0);
    $pdf->Cell(60, 6, $payment_id, 0, 1);

    $pdf->Ln(10);

    $pdf->SetFillColor(235, 235, 235);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 10, 'Description', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Room No.', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Check-in', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Check-out', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Amount', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(70, 10, $room_type . ' - ' . $days . ' days stay', 1, 0, 'L');
    $pdf->Cell(30, 10, $room_number, 1, 0, 'C');
    $pdf->Cell(30, 10, date('d/m/Y', strtotime($check_in_date)), 1, 0, 'C');
    $pdf->Cell(30, 10, date('d/m/Y', strtotime($check_out_date)), 1, 0, 'C');
    $pdf->Cell(30, 10, chr(163) . number_format($amount, 2), 1, 1, 'R');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(160, 10, 'Total', 1, 0, 'R', true);
    $pdf->Cell(30, 10, chr(163) . number_format($amount, 2), 1, 1, 'R', true);

    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Payment Information', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, 'Payment Status: Paid', 0, 1);
    $pdf->Cell(0, 6, 'Payment Method: Credit Card (via Stripe)', 0, 1);
    $pdf->Cell(0, 6, 'Payment Date: ' . date('d/m/Y'), 0, 1);

    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Terms and Conditions', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 5, 'Thank you for your business. This invoice serves as confirmation that your payment has been processed successfully. If you have any questions regarding your booking or this invoice, please contact our customer service team.');

    $pdf->Output('F', $pdf_path);
    $pdf_generated = true;

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM invoices WHERE booking_id = :booking_id AND user_id = :user_id");
        $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $invoiceExists = $stmt->fetchColumn();

        if (!$invoiceExists) {
            $stmt = $conn->prepare("INSERT INTO invoices (user_id, booking_id, payment_id, invoice_number, amount, filename, type, created_at) 
            VALUES (:user_id, :booking_id, :payment_id, :invoice_number, :amount, :filename, :type, :created_at)");
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
            $stmt->bindValue(':payment_id', $payment_id, PDO::PARAM_INT);
            $stmt->bindValue(':invoice_number', $invoice_number, PDO::PARAM_STR);
            $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindValue(':filename', $pdf_filename, PDO::PARAM_STR);
            $stmt->bindValue(':type', $payment_type, PDO::PARAM_STR);
            $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();
        }
    } catch (PDOException $e) {
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
</head>

<body>
    <div class="success-container">
        <h1>Payment Successful!</h1>
        <p>Your payment has been processed successfully and your booking is now confirmed.</p>

        <div class="details-box">
            <p>Booking ID: <?php echo htmlspecialchars($booking_id); ?></p>
            <p>Room Type: <?php echo htmlspecialchars($room_type); ?></p>
            <p>Room Number: <?php echo htmlspecialchars($room_number); ?></p>
            <p>Check-in Date: <?php echo date('d/m/Y', strtotime($check_in_date)); ?></p>
            <p>Check-out Date: <?php echo date('d/m/Y', strtotime($check_out_date)); ?></p>
            <p>Amount Paid: <?php echo '£' . number_format($amount, 2); ?></p>
        </div>

        <p>A confirmation email has been sent to your registered email address.</p>

        <div>
            <a href="../invoices/<?php echo $pdf_filename; ?>" class="btn" target="_blank">Download Invoice</a>
            <a href="../guest_dashboard/guest_dashboard.php" class="btn btn-outline">Return to Dashboard</a>
        </div>

        <p>If you have any questions about your booking, please contact our support team.</p>
    </div>
</body>

</html>