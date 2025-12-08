<?php
session_start();

// Access control - cashier only
$has_access = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'cashier') {
	$has_access = true;
}

// Database connection (mysqli to match other pages)
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

// Handle mark as paid
if ($has_access && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['action']) && $_POST['action'] === 'mark_paid') {
	$order_id = (int)$_POST['order_id'];
	$status = 'pending'; // After payment, send to queue as pending

	$update_query = "UPDATE orders SET status = ?, order_date = COALESCE(order_date, NOW()) WHERE id = ?";
	$stmt = $conn->prepare($update_query);
	if ($stmt) {
		$stmt->bind_param("si", $status, $order_id);
		if ($stmt->execute()) {
			$success_message = "Payment confirmed. Order moved to queue.";
		} else {
			$error_message = "Failed to update order: " . $stmt->error;
		}
		$stmt->close();
	} else {
		$error_message = "Failed to prepare update statement.";
	}
}

// Fetch awaiting payment orders with items
$query = "SELECT o.*, oi.quantity, p.name, oi.price, oi.instructions
			FROM orders o
			LEFT JOIN order_items oi ON o.id = oi.order_id
			LEFT JOIN products p ON oi.product_id = p.id
			WHERE o.status = 'awaiting_payment'
			ORDER BY o.created_at DESC, oi.id";
$result = $conn->query($query);
if (!$result) {
	$error_message = "Failed to fetch orders.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Payment Process - Let's Meet Cafe</title>
	<link rel="stylesheet" href="assets/css/style.css">
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
						<li><a href="manage_orders.php">Orders</a></li>
						<li><a href="logout.php" class="logout-btn">Logout</a></li>
					<?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'cashier'): ?>
						<li><a href="payment_process.php">Payment</a></li>
						<li><a href="logout.php" class="logout-btn">Logout</a></li>
					<?php endif; ?>
				</ul>
			</nav>
		</header>

		<main>
			<section class="hero">
				<h2>Payment Processing</h2>
				<p>Confirm payments before orders enter the queue</p>
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
							<?php 
							$current_order_id = null;
							$order = null;
							while ($row = $result->fetch_assoc()) {
								if ($row['id'] !== $current_order_id) {
									if ($current_order_id !== null) {
										echo '</ul>';
										echo '</div>';
										echo '<div class="order-total"><p>Total: ₱' . number_format($order['total_amount'], 2) . '</p></div>';
										echo '<form method="POST"><input type="hidden" name="order_id" value="' . htmlspecialchars($order['id']) . '"><input type="hidden" name="action" value="mark_paid"><button type="submit" class="btn">Payment Completed</button></form>';
										echo '</div>';
									}

									$current_order_id = $row['id'];
									$order = $row;
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
									?>
									<li class="order-item-detail">
										<span><?php echo htmlspecialchars($row['quantity']); ?>x <?php echo htmlspecialchars($row['name']); ?> ₱<?php echo number_format($row['price'], 2); ?></span>
										<?php if (!empty($row['instructions'])): ?>
											<p class="item-instructions">Instructions: <?php echo htmlspecialchars($row['instructions']); ?></p>
										<?php endif; ?>
									</li>
									<?php 
							}
							if ($current_order_id !== null) {
								echo '</ul>';
								echo '</div>';
								echo '<div class="order-total"><p>Total: ₱' . number_format($order['total_amount'], 2) . '</p></div>';
								echo '<form method="POST"><input type="hidden" name="order_id" value="' . htmlspecialchars($order['id']) . '"><input type="hidden" name="action" value="mark_paid"><button type="submit" class="btn">Payment Completed</button></form>';
								echo '</div>';
							}
							?>
						</div>
					<?php else: ?>
						<div class="no-orders-message">
							<p>No orders awaiting payment.</p>
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
</body>
</html>


