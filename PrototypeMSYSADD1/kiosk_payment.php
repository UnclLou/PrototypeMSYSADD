<?php
// Payment option selection page for the kiosk

// Keep the previously selected dining option in the query string
$allowedDiningOptions = ['dine_in', 'take_out'];
$diningOption = isset($_GET['dining_option']) && in_array($_GET['dining_option'], $allowedDiningOptions, true)
    ? $_GET['dining_option']
    : 'dine_in';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Payment Method - CoffeeQueue</title>
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

        <main>
            <section class="kiosk-greeting">
                <h2>Select Payment Method</h2>
                <p>Please choose how you would like to pay for your order.</p>
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; justify-content: center; margin-top: 2rem;">
                    <a href="kiosk.php?dining_option=<?php echo urlencode($diningOption); ?>&payment_method=gcash"
                       class="btn" style="min-width: 180px;">GCash</a>
                    <a href="kiosk.php?dining_option=<?php echo urlencode($diningOption); ?>&payment_method=cash"
                       class="btn" style="min-width: 180px;">Cash</a>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2025 Let's Meet Cafe. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>


