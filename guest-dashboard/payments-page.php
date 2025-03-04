<?php
$db = new SQLite3(__DIR__ . '/../luckynest.db');

// Replace with your actual Stripe secret key)
$stripe_secret_key = 'sk_test';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];
    $amount = $_POST['amount'];
    $stripe_token = $_POST['stripeToken'];

    require_once __DIR__ . '/../vendor/autoload.php';
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    try {
        $charge = \Stripe\Charge::create([
            'amount' => $amount * 100,
            'currency' => 'usd',
            'source' => $stripe_token,
            'description' => 'Payment for room booking',
        ]);

        $payment_date = date('Y-m-d H:i:s');
        $stripe_payment_id = $charge->id;

        $stmt = $db->prepare('INSERT INTO payments (user_id, room_id, amount, payment_date, stripe_payment_id) VALUES (:user_id, :room_id, :amount, :payment_date, :stripe_payment_id)');
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':room_id', $room_id, SQLITE3_INTEGER);
        $stmt->bindValue(':amount', $amount, SQLITE3_FLOAT);
        $stmt->bindValue(':payment_date', $payment_date, SQLITE3_TEXT);
        $stmt->bindValue(':stripe_payment_id', $stripe_payment_id, SQLITE3_TEXT);
        $stmt->execute();

        echo "Payment successful!";
    } catch (\Stripe\Exception\CardException $e) {
        echo "Payment failed: " . $e->getError()->message;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
</head>

<body>
    <h1>Make a Payment</h1>
    <form action="" method="POST">
        <label for="user_id">User ID:</label>
        <input type="number" id="user_id" name="user_id" required><br><br>

        <label for="room_id">Room ID:</label>
        <input type="number" id="room_id" name="room_id" required><br><br>

        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required><br><br>

        <label for="stripeToken">Card Details:</label>
        <div id="card-element">
        </div>

        <div id="card-errors" role="alert"></div><br><br>

        <button type="submit">Submit Payment</button>
    </form>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        var stripe = Stripe('pk_test'); // Replace this with your actual Stripe publishable key
        var elements = stripe.elements();

        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        var card = elements.create('card', { style: style });
        card.mount('#card-element');

        card.on('change', function (event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        var form = document.querySelector('form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            stripe.createToken(card).then(function (result) {
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    stripeTokenHandler(result.token);
                }
            });
        });

        function stripeTokenHandler(token) {
            var form = document.querySelector('form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
            form.submit();
        }
    </script>
</body>

</html>