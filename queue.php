<?php
session_start();

// Access control - staff and admin only
$has_access = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'staff'])) {
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status']) && $_POST['status'] === 'completed') {
    $order_id = $_POST['order_id'];
    $status = 'completed'; // Force status to completed
    
    // Debug information
    error_log("Updating order ID: " . $order_id . " to status: " . $status);
    
    $update_query = "UPDATE orders SET status = ?, completed_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $error_message = "Failed to prepare update statement.";
    } else {
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            $success_message = "Order status updated successfully!";
            error_log("Status update successful");
        } else {
            $error_message = "Failed to update order status: " . $stmt->error;
            error_log("Status update failed: " . $stmt->error);
        }
        $stmt->close();
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle other potential POST requests if necessary, or ignore
    // For now, we only expect 'completed' status updates via AJAX
    error_log("Received POST request but not a completed status update.");
}

// Get all active orders
$query = "SELECT o.*, oi.quantity, p.name, oi.price, oi.instructions
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          LEFT JOIN products p ON oi.product_id = p.id 
          WHERE o.status != 'completed' AND o.status != 'awaiting_payment'
          ORDER BY o.created_at DESC, oi.id";

$result = $conn->query($query);
if (!$result) {
    error_log("Query failed: " . $conn->error);
    $error_message = "Failed to fetch orders.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Management - Let's Meet Cafe</title>
    <<link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <meta http-equiv="refresh" content="30">
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
                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff'): ?>
                        <li><a href="queue.php">Queue</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>
    

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
                            <?php 
                            $current_order_id = null;
                            $order = null; // Initialize order variable
                            while ($row = $result->fetch_assoc()) {
                                // Check if it's a new order
                                if ($row['id'] !== $current_order_id) {
                                    // If not the first order, close the previous order card
                                    if ($current_order_id !== null) {
                                        echo '</ul>'; // Close order-item-list
                                        echo '</div>'; // Close order-items
                                        echo '<div class="order-total"><p>Total: ₱' . number_format($order['total_amount'], 2) . '</p></div>';
                                        echo '<button class="btn complete-order-btn" data-order-id="' . htmlspecialchars($order['id']) . '">Mark as Completed</button>';
                                        echo '</div>'; // Close order-card
                                    }

                                    // Start a new order card
                                    $current_order_id = $row['id'];
                                    $order = $row; // Store order details
                                    ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <h3>Order #<?php echo htmlspecialchars($order['id']); ?></h3>
                                            <span class="order-time"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></span>
                                        </div>
                                        
                                        <div class="order-info">
                                             <p class="dining-option"><?php echo ucfirst(str_replace('_', ' ', $order['dining_option'])); ?></p>
                                             <p class="table-number">Table: <?php echo htmlspecialchars($order['table_number']); ?></p>
                                        </div>

                                        <div class="order-items">
                                            <h4>Items:</h4>
                                            <ul class="order-item-list">
                                    <?php
                                }
                                
                                // Display individual item with instructions
                                ?>
                                <li class="order-item-detail">
                                    <span><?php echo htmlspecialchars($row['quantity']); ?>x <?php echo htmlspecialchars($row['name']); ?> ₱<?php echo number_format($row['price'], 2); ?></span>
                                    <?php if (!empty($row['instructions'])): ?>
                                        <p class="item-instructions">Instructions: <?php echo htmlspecialchars($row['instructions']); ?></p>
                                    <?php endif; ?>
                                </li>
                            <?php 
                            }
                            // Close the last order card after the loop
                            if ($current_order_id !== null) {
                                echo '</ul>'; // Close order-item-list
                                echo '</div>'; // Close order-items
                                echo '<div class="order-total"><p>Total: ₱' . number_format($order['total_amount'], 2) . '</p></div>';
                                echo '<button class="btn complete-order-btn" data-order-id="' . htmlspecialchars($order['id']) . '">Mark as Completed</button>';
                                echo '</div>'; // Close order-card
                            }
                            ?>
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

    <?php if ($has_access): ?>
    <script>
    document.querySelectorAll('.complete-order-btn').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Mark as Completed button clicked');
            const orderId = this.dataset.orderId;
            console.log('Order ID:', orderId);
            
            // Confirm with the user before marking as completed
            if (confirm('Are you sure you want to mark order #' + orderId + ' as completed?')) {
                console.log('User confirmed completion');
                fetch('queue.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId + '&status=completed'
                })
                .then(response => {
                    console.log('Fetch response received', response);
                    // Check if the response was successful (optional, but good practice)
                    if (!response.ok) {
                        console.error('Network response was not ok', response);
                        throw new Error('Network response was not ok');
                    }
                    console.log('Response status is OK');
                    return response.text(); // Or response.json() if your PHP returns JSON
                })
                .then(() => {
                    console.log('Processing successful response');
                    // Find the parent order-card element and remove it
                    const orderCard = this.closest('.order-card');
                    console.log('Found order card element:', orderCard);
                    if (orderCard) {
                        console.log('Removing order card from DOM');
                        orderCard.remove();
                    }
                    // You might want to add a temporary success message here instead of reloading
                    // alert('Order #' + orderId + ' marked as completed!');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update status. Please try again.');
                });
            } else {
                console.log('User cancelled completion');
            }
        });
    });
    </script>
    <?php endif; ?>
</body>
</html> 