<?php
require_once 'config/database.php';

// Fetch products to display on the kiosk
$stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY category, name");
$products = $stmt->fetchAll();

// Get dining option from query string
$allowedDiningOptions = ['dine_in', 'take_out'];
$hasDiningOption = isset($_GET['dining_option']) && in_array($_GET['dining_option'], $allowedDiningOptions, true);
$diningOption = $hasDiningOption ? $_GET['dining_option'] : 'dine_in';

// Get payment method from query string
$allowedPaymentMethods = ['gcash', 'cash'];
$hasPaymentMethod = isset($_GET['payment_method']) && in_array($_GET['payment_method'], $allowedPaymentMethods, true);
$paymentMethod = $hasPaymentMethod ? $_GET['payment_method'] : null;

// If dining option is chosen but payment method is missing, redirect to payment selection
if ($hasDiningOption && !$hasPaymentMethod) {
    $redirectUrl = 'kiosk_payment.php?dining_option=' . urlencode($diningOption);
    header("Location: $redirectUrl");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk - CoffeeQueue</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Kiosk specific CSS will be added later -->
</head>
<body>
    <div class="container kiosk-container">
        <header class="kiosk-header">
            <div class="logo">
                <h1>CoffeeQueue</h1>
            </div>
        </header>

        <main>
            <?php if (!$hasDiningOption): ?>
                <!-- Greeting Screen -->
                <section id="greeting-screen" class="kiosk-greeting">
                    <h2>Welcome to Let's Meet Cafe</h2>
                    <p>Tap the button below to start your order!</p>
                    <button id="start-order-btn" class="btn">Order Now</button> 
                </section>
            <?php endif; ?>

            <!-- Main Kiosk Content -->
            <div id="main-kiosk-content" class="kiosk-layout <?php echo ($hasDiningOption && $hasPaymentMethod) ? '' : 'hidden'; ?>">
                <div class="kiosk-products-section">
                    <div class="kiosk-menu-header">
                        <h2>Menu</h2>
                        <div class="kiosk-menu-header-right">
                            <?php if ($hasDiningOption): ?>
                                <div class="kiosk-dining-indicator">
                                    <span class="label">Dining:</span>
                                    <span class="value">
                                        <?php echo $diningOption === 'take_out' ? 'Take Out' : 'Dine In'; ?>
                                    </span>
                                    <a href="kiosk_dining.php" class="btn kiosk-change-dining-btn">Change</a>
                                </div>
                            <?php endif; ?>
                            <?php if ($hasPaymentMethod): ?>
                                <div class="kiosk-dining-indicator">
                                    <span class="label">Payment:</span>
                                    <span class="value">
                                        <?php echo $paymentMethod === 'gcash' ? 'GCash' : 'Cash'; ?>
                                    </span>
                                    <a href="kiosk_payment.php?dining_option=<?php echo urlencode($diningOption); ?>" class="btn kiosk-change-dining-btn">Change</a>
                                </div>
                            <?php endif; ?>
                            <button id="cart-button" class="btn cart-btn" type="button">
                                🛒 Cart (<span id="cart-count">0</span>)
                            </button>
                        </div>
                    </div>
                    <div class="kiosk-category-filters">
                         <button class="kiosk-filter-btn active" data-category="all">All</button>
                        <?php
                         // Fetch unique categories for filters
                         $categoryStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE stock > 0 ORDER BY category");
                         $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
                         foreach ($categories as $category):
                        ?>
                            <button class="kiosk-filter-btn" data-category="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="kiosk-products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="kiosk-product-card" 
                                 data-id="<?php echo $product['id']; ?>" 
                                 data-price="<?php echo $product['price']; ?>"
                                 data-category="<?php echo $product['category']; ?>">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="kiosk-product-image">
                                <?php else: ?>
                                    <div class="kiosk-product-image-placeholder">🍽️</div>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="kiosk-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p class="kiosk-price">₱<?php echo number_format($product['price'], 2); ?></p>
                                <p class="kiosk-stock">Stock: <?php echo $product['stock']; ?></p>
                                <div class="kiosk-product-quantity-badge" data-product-id="<?php echo $product['id']; ?>" style="display: none;">
                                    <span class="quantity-text">In Cart: </span>
                                    <span class="quantity-number">0</span>
                                </div>
                                <button class="btn kiosk-add-to-cart">Add to Order</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="kiosk-order-summary hidden" id="cart-panel">
                    <div class="kiosk-cart-header">
                        <button type="button" class="btn cart-back-btn" id="back-to-menu-btn">← Back to Menu</button>
                        <h2>Your Order</h2>
                    </div>
                    <div class="kiosk-cart-items">
                        <!-- Kiosk cart items will be added here dynamically -->
                        <p>Your order is empty.</p>
                    </div>
                    <div class="kiosk-cart-total">
                        <h3>Total: ₱<span id="kiosk-total-amount">0.00</span></h3>
                    </div>
                    <button class="btn kiosk-checkout-btn" disabled>Place Order</button>
                </div>
            </div>

            <!-- Table Number Screen (shown after clicking Place Order) -->
            <section id="table-number-screen" class="kiosk-greeting hidden">
                <h2>Enter Your Table Number</h2>
                <p>Please enter your table number so we can serve your order.</p>
                <div class="form-group" style="max-width: 320px; margin: 1.5rem auto 0 auto; text-align: left;">
                    <label for="table_number_input">Table Number:</label>
                    <input type="number" id="table_number_input" name="table_number_input" min="1" required placeholder="Enter your table number" style="width: 100%; padding: 0.75rem; font-size: 1.1rem;">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; flex-wrap: wrap;">
                    <button id="confirm-table-btn" class="btn" style="min-width: 180px;">Confirm</button>
                    <button id="cancel-table-btn" class="btn btn-danger" style="min-width: 180px;">Back</button>
                </div>
            </section>

            <!-- Thank You Message (Initially hidden) -->
            <div id="cash-thank-you-message" class="kiosk-thank-you hidden">
                <div class="checkmark-circle">
                    <div class="checkmark"></div>
                </div>
                <h2>Thank You for Your Order!</h2>
                <p>Your cash order has been placed successfully.</p>
                <p>Please proceed to the counter to pay.</p>
            </div>

            <!-- GCash QR Code Message (Initially hidden) -->
            <div id="gcash-qr-code-message" class="kiosk-thank-you hidden">
                <h2>Scan to Pay with GCash</h2>
                <p>Please scan the QR code below to complete your payment.</p>
                <!-- Placeholder for QR Code Image -->
                <div class="qr-code-placeholder" style="width: 200px; height: 200px; background-color: #ccc; margin: 20px auto;">
                    <!-- QR Code will be loaded here -->
                    <img src="placeholder_qr.png" alt="GCash QR Code" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <p>Thank you for your order!</p>
                <button id="gcash-done-btn" class="btn" style="margin-top: 2rem; padding: 1rem 3rem; font-size: 1.2rem;">Done</button>
            </div>

            <!-- Final Thank You Message after GCash Done (Initially hidden) -->
            <div id="gcash-final-thank-you-message" class="kiosk-thank-you hidden">
                <div class="checkmark-circle">
                    <span style="font-size: 4rem; color: var(--success-color);">✓</span>
                </div>
                <h2>Thank you for ordering in Let's Meet Cafe!</h2>
                <p>Please proceed to the counter to review your payment</p>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Let's Meet Cafe. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Pass selected dining option and payment method from PHP to JavaScript
        window.KIOSK_DINING_OPTION = "<?php echo htmlspecialchars($diningOption, ENT_QUOTES, 'UTF-8'); ?>";
        window.KIOSK_PAYMENT_METHOD = "<?php echo htmlspecialchars((string)$paymentMethod, ENT_QUOTES, 'UTF-8'); ?>";
    </script>
    <script src="assets/js/kiosk.js"></script>
    <script>
        // After greeting, go to dining option screen (only if greeting exists)
        const startOrderBtn = document.getElementById('start-order-btn');
        if (startOrderBtn) {
            startOrderBtn.addEventListener('click', function() {
                window.location.href = 'kiosk_dining.php';
            });
        }

        // Handle redirect after thank you message
    </script>
</body>
</html>