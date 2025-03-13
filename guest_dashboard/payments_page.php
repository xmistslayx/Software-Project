<?php
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../include/db.php";

$dotenv = Dotenv\Dotenv::createImmutable("C:\xampp\htdocs\luckynest_env_files");
$dotenv->load();

function getActiveStripeKey($conn)
{
    try {
        $stmt = $conn->prepare("SELECT key_reference, valid_until FROM stripe_keys WHERE is_active = 1 ORDER BY valid_until DESC LIMIt 1");
        $stmt->execute();
        $key = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$key || strtotime($key["valid_until"]) < time()) {
            $stmt = $conn->prepare("SELECT key_id, key_reference FROM stripe_keys WHERE valid_until > NOW() ORDER BY valid_until ASC LIMIt 1");
            $stmt->execute();
            $newKey = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($newKey) {
                $stmt = $conn->prepare("UPDATE stripe_keys SET is_active = 0 WHERE is_active = 1");
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE stripe_keys SET is_active = 1 WHERE key_id =:key_id");
                $stmt->bindValue(':key_id', $newKey['key_id'], PDO::PARAM_INT);
                $stmt->execute();

                return$_ENV[$newKey['key_reference']];
            } else {
                return $_ENV["stripe_key_fallback"];
            }
        }

        return $_ENV[$key['key_reference']];
    } catch (PDOException $e) {
        error_log("Error fetching Stripe key: " . $e->getMessage());
        return $_ENV["stripe_key_fallback"];
    }
}


$stripe_key = getActiveStripeKey($conn);
\Stripe\Stripe::setApiKey($stripe_key);

$guest_id = 1;

try {
    $stmt = $conn->prepare("SELECT booking_id, guest_id, room_id, check_in_date, check_out_date 
                           FROM bookings 
                           WHERE guest_id = :guest_id 
                           ANd booking_is_paid = 0");
    $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $roomRates = [];
    foreach ($bookings as $booking) {
        $stmt = $conn->prepare("SELECT rt.rate_monthly 
                               FROM rooms r 
                               JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                               WHERE r.room_id = :room_id");
        $stmt->bindValue(':room_id', $booking['room_id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $roomRates[$booking['room_id']] = $result ? floatval($result['rate_monthly']) : 0;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["payment_type"]) || !isset($_POST["booking_id"]) || empty($_POST["booking_id"])) {
        die("Payment type and booking ID are required.");
    }

    $bookingId = $_POST["booking_id"];
    $paymentType = $_POST["payment_type"];
    $description = $_POST["description"];
    $checkInDate = $_POST["check_in_date"];
    $checkOutDate = $_POST["check_out_date"];
    $roomId = $_POST["room_id"];

    try {
        $stmt = $conn->prepare("SELECT rt.rate_monthly 
                               FROM rooms r 
                               JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                               WHERE r.room_id = :room_id");
        $stmt->bindValue(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $roomrate = $result ? floatval($result['rate_monthly']) : 0;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

    $amount = 0;
    if ($paymentType == 'rent') {
        $date1 = new DateTime($checkInDate);
        $date2 = new DateTime($checkOutDate);
        $interval = $date1->diff($date2);
        $days = $interval->days;
        $amount = $roomrate;
    } else if ($paymentType == 'food') {
        $amount = 50;
    } else if ($paymentType == 'laundry') {
        $amount = 20;
    }

    try {
        $amountRounded = round($amount, 2);

        $checkoutSession = \Stripe\Checkout\Session::create([
            "payment_method_types" => ["card"],
            "line_items" => [
                [
                    "price_data" => [
                        "currency" => "gbp",
                        "product_data" => [
                            "name" => $description,
                        ],
                        "unit_amount" => $amountRounded * 100,
                    ],
                    "quantity" => 1,
                ]
            ],
            "mode" => "payment",
            "success_url" => "http://localhost/LuckyNest/guest_dashboard/success.php?session_id={CHECKOUT_SESSION_ID}&booking_id=" . $bookingId . "&payment_type=" . $paymentType,
            "cancel_url" => "http://localhost/LuckyNest/guest_dashboard/payments_page.php",
        ]);

        header("Location: " . $checkoutSession->url);
        exit();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit();
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script>
        // roomRates has to be defined here becuz it depends on php data
        const roomRates = <?php echo json_encode($roomRates); ?>;
    </script>
    <!--do not remove the double slash, scripts.js does not load when there is only a single slash-->
    <script src="..//assets/scripts.js"></script>
</head>

<body>
    <h2>Make a Payment</h2>

    <?php if (empty($bookings)): ?>
        <p>No unpaid bookings found for this guest.</p>
    <?php else: ?>
        <form action="" method="POST">
            <label for="booking_selection">Select Booking:</label>
            <select id="booking_selection" name="booking_id" onchange="updateBookingDetails()" required>
                <option value="">-- Select a booking --</option>
                <?php foreach ($bookings as $booking): ?>
                    <option value="<?php echo $booking['booking_id']; ?>" data-room-id="<?php echo $booking['room_id']; ?>"
                        data-check-in="<?php echo $booking['check_in_date']; ?>"
                        data-check-out="<?php echo $booking['check_out_date']; ?>">
                        Booking #<?php echo $booking['booking_id']; ?>
                        (<?php echo $booking['check_in_date']; ?> to <?php echo $booking['check_out_date']; ?>)
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="description">Description:</label>
            <input type="text" name="description" required><br>

            <label for="payment_type">Payment Type:</label>
            <select id="payment_type" name="payment_type" onchange="calculateAmount()" required>
                <option value="rent">Rent</option>
                <option value="food">Food</option>
                <option value="laundry">Laundry</option>
            </select><br>

            <label for="amount">Amount (£):</label>
            <input type="number" id="amount" name="amount" readonly><br>

            <input type="hidden" id="check_in_date" name="check_in_date">
            <input type="hidden" id="check_out_date" name="check_out_date">
            <input type="hidden" id="user_id" name="user_id" value="<?php echo $guest_id; ?>">
            <input type="hidden" id="room_id" name="room_id">
            <input type="hidden" id="payment_type_hidden" name="payment_type">

            <button type="submit">Pay with Stripe</button>
        </form>
    <?php endif; ?>
</body>

</html>