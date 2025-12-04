<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - CoffeeQueue</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">
                    <h1>CoffeeQueue</h1>
                </div>
                <ul class="nav-links">
                    <li><a href="index.php">Dashboard</a></li>
                    <?php if (isset($_SESSION['user_role'])): ?>
                        <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff'): ?>
                            <li><a href="inventory.php">Inventory</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff'): ?>
                            <li><a href="queue.php">Queue</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff'): ?>
                            <li><a href="reports.php">Reports</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <li><a href="manage_products.php">Products</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>
            <section class="hero">
                <h2>Access Denied</h2>
                <p>You do not have permission to access this page.</p>
                <div style="margin-top: 2rem;">
                    <a href="index.php" class="btn">Return to Dashboard</a>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 CoffeeQueue. All rights reserved.</p>
        </footer>
    </div>
</body>
</html> 