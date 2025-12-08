<?php
// Simple dining option selection page for the kiosk
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Dining Option - CoffeeQueue</title>
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
                <h2>How would you like to dine?</h2>
                <p>Please choose an option to continue with your order.</p>
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; justify-content: center; margin-top: 2rem;">
                    <a href="kiosk_payment.php?dining_option=dine_in" class="btn" style="min-width: 180px;">Dine In</a>
                    <a href="kiosk_payment.php?dining_option=take_out" class="btn" style="min-width: 180px;">Take Out</a>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2025 Let's Meet Cafe. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>


