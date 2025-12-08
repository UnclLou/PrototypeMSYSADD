<?php
// Get payment method from query string
$paymentMethod = isset($_GET['payment_method']) ? $_GET['payment_method'] : 'cash';
$allowedPaymentMethods = ['cash', 'gcash'];
if (!in_array($paymentMethod, $allowedPaymentMethods)) {
    $paymentMethod = 'cash';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - CoffeeQueue</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container kiosk-container">
        <header class="kiosk-header">
            <div class="logo">
                <h1>CoffeeQueue</h1>
            </div>
        </header>

        <main style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center;">
            <?php if ($paymentMethod === 'cash'): ?>
                <!-- Cash Thank You Message -->
                <div id="cash-thank-you-message" class="kiosk-thank-you" style="position: relative; top: auto; left: auto; right: auto; bottom: auto;">
                    <div class="checkmark-circle">
                        <span style="font-size: 4rem; color: var(--success-color);">✓</span>
                    </div>
                    <h2>Thank You for Your Order!</h2>
                    <p>Your cash order has been placed successfully.</p>
                    <p>Please proceed to the counter to pay.</p>
                </div>
            <?php else: ?>
                <!-- GCash QR Code Message -->
                <div id="gcash-qr-code-message" class="kiosk-thank-you" style="position: relative; top: auto; left: auto; right: auto; bottom: auto;">
                    <h2>Scan to Pay with GCash</h2>
                    <p>Please scan the QR code below to complete your payment.</p>
                    <!-- Placeholder for QR Code Image -->
                    <div class="qr-code-placeholder" style="width: 200px; height: 200px; background-color: #ccc; margin: 20px auto;">
                        <!-- QR Code will be loaded here -->
                        <img src="placeholder_qr.png" alt="GCash QR Code" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <p>Thank you for your order!</p>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2025 Let's Meet Cafe. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Redirect back to kiosk after 5 seconds
        setTimeout(() => {
            window.location.href = 'kiosk.php';
        }, 5000);
    </script>
</body>
</html>

