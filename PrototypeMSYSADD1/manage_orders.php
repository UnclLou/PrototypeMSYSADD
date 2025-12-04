<?php
session_start();

// Access control - admin only
$has_access = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $has_access = true;
}

// Database connection
$host = 'localhost';
$dbname = 'coffee_queue';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Failed to update order status.";
    }
}

// Get all active orders
$query = "SELECT o.*, GROUP_CONCAT(oi.quantity, 'x ', p.name) as items 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          LEFT JOIN products p ON oi.product_id = p.id 
          WHERE o.status != 'completed'
          GROUP BY o.id 
          ORDER BY o.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Let's Meet Cafe</title>
    <link rel="stylesheet" href="style.css">
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
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="payment_process.php">Payment</a></li>
                        <li><a href="queue.php">Queue</a></li>
                        <li><a href="reports.php">Reports</a></li>
                        <li><a href="manage_products.php">Products</a></li>
                        <li><a href="manage_orders.php">Orders</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>
            <section class="hero">
                <h2>Manage Orders</h2>
                <p>Update order status and track customer orders</p>
            </section>

            <?php if ($has_access): ?>
                <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <section class="orders-section">
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="orders-grid">
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <h3>Order #<?php echo htmlspecialchars($order['id']); ?></h3>
                                    <span class="order-time"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></span>
                                </div>
                                
                                <div class="order-items">
                                    <h4>Items:</h4>
                                    <p><?php echo htmlspecialchars($order['items']); ?></p>
                                </div>
                                
                                <div class="order-total">
                                    <p>Total: ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>

                                <form method="POST" class="status-update-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <div class="form-group">
                                        <label for="status">Update Status:</label>
                                        <select name="status" id="status" class="status-select">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                            <option value="ready" <?php echo $order['status'] == 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn">Update Status</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-orders-message">
                        <p>No active orders found.</p>
                    </div>
                <?php endif; ?>
            </section>
            <?php else: ?>
                <section class="hero">
                    <h2>Access Denied</h2>
                    <p>You do not have permission to view this page.</p>
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