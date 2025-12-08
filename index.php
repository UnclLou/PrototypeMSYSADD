<?php
session_start();

// Assume access is granted initially
$has_access = false;

// Check if the user is logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $has_access = true;
}

// Database connection (assuming you still need it for dashboard data)
require_once 'config/database.php';

// Fetch data for the dashboard (example: count of products, orders, etc.)
// ... your existing PHP logic for fetching dashboard data ...

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Let's Meet Cafe - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">
                    <h1>Let's Meet Cafe</h1>
                </div>
                <ul class="nav-links">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="payment_process.php">Payment</a></li>
                        <li><a href="queue.php">Queue</a></li>
                        <li><a href="reports.php">Reports</a></li>
                        <li><a href="manage_products.php">Products</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'cashier'): ?>
                        <li><a href="payment_process.php">Payment</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff'): ?>
                        <li><a href="queue.php">Queue</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php else: ?>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>
            <section class="hero">
                <h2>Dashboard</h2>
                <p>Manage your cafe operations efficiently</p>
            </section>


            <?php if ($has_access): ?>
                 <section class="features">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <div class="feature-card">
                            <h3>Payment Process</h3>
                            <p>Confirm payments before orders enter the queue</p>
                            <a href="payment_process.php" class="btn">Open Payment</a>
                        </div>
                        <div class="feature-card">
                            <h3>Queue Management</h3>
                            <p>Monitor and manage customer queues</p>
                            <a href="queue.php" class="btn">View Queue</a>
                        </div>
                        <div class="feature-card">
                            <h3>Products</h3>
                            <p>Manage and update product information</p>
                            <a href="manage_products.php" class="btn">Manage Products</a>
                        </div>
                        <div class="feature-card">
                            <h3>Reports</h3>
                            <p>View sales reports and analytics</p>
                            <a href="reports.php" class="btn">View Reports</a>
                        </div>
                        
                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'cashier'): ?>
                        <div class="feature-card">
                            <h3>Payment Process</h3>
                            <p>Confirm payments before orders enter the queue</p>
                            <a href="payment_process.php" class="btn">Open Payment</a>
                        </div>
                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff'): ?>
                        <div class="feature-card">
                            <h3>Queue Management</h3>
                            <p>Monitor and manage customer queues</p>
                            <a href="queue.php" class="btn">View Queue</a>
                        </div>
                    <?php endif; ?>
                </section>
            <?php else: ?>
                 <section class="hero">
                    <h2>Access Denied</h2>
                    <p>You must be logged in to view the dashboard.</p>
                </section>
            <?php endif; ?>

        </main>

        <footer>
            <p>&copy; 2025 Let's Meet Cafe. All rights reserved.</p>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html> 
