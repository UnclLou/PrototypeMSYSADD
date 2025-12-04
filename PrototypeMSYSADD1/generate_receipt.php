<?php
session_start();

// Check if customer is logged in
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: customer_login.php');
    exit();
}

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    die('Order ID is required');
}

require_once 'config/database.php';

try {
    // Fetch order details
    $stmt = $pdo->prepare("
        SELECT o.*, c.first_name, c.last_name, c.email, c.phone 
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        WHERE o.id = ? AND o.customer_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['customer_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        die('Order not found or access denied');
    }

    // Fetch order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $orderItems = $stmt->fetchAll();

} catch (Exception $e) {
    die('Error fetching order details: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #<?php echo htmlspecialchars($order['order_number']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .receipt-container {
            max-width: 400px;
            margin: 2rem auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .receipt-header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .receipt-header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .receipt-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        .receipt-content {
            padding: 1.5rem;
        }
        .receipt-section {
            margin-bottom: 1.5rem;
        }
        .receipt-section h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.25rem;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }
        .receipt-info strong {
            color: var(--text-color);
        }
        .receipt-items {
            margin-bottom: 1rem;
        }
        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        .receipt-item:last-child {
            border-bottom: none;
        }
        .item-details {
            flex: 1;
        }
        .item-name {
            font-weight: 500;
        }
        .item-quantity {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .item-price {
            font-weight: 500;
            color: var(--primary-color);
        }
        .receipt-total {
            background: var(--accent-color);
            padding: 1rem;
            border-radius: var(--border-radius);
            text-align: center;
        }
        .total-amount {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        .receipt-actions {
            padding: 1.5rem;
            background: var(--background-color);
            text-align: center;
        }
        .receipt-actions .btn {
            margin: 0.25rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-preparing {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-ready {
            background: #d1fae5;
            color: #065f46;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        @media print {
            .receipt-actions {
                display: none;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="receipt-container">
            <div class="receipt-header">
                <h1>Let's Meet Cafe</h1>
                <p>Order Receipt</p>
            </div>

            <div class="receipt-content">
                <div class="receipt-section">
                    <h3>Order Information</h3>
                    <div class="receipt-info">
                        <span>Order Number:</span>
                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </div>
                    <div class="receipt-info">
                        <span>Order Date:</span>
                        <strong><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></strong>
                    </div>
                    <div class="receipt-info">
                        <span>Status:</span>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div class="receipt-info">
                        <span>Order Type:</span>
                        <strong><?php echo ucfirst(str_replace('_', ' ', $order['dining_option'])); ?></strong>
                    </div>
                    <div class="receipt-info">
                        <span>Payment:</span>
                        <strong><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></strong>
                    </div>
                </div>

                <div class="receipt-section">
                    <h3>Customer Information</h3>
                    <div class="receipt-info">
                        <span>Name:</span>
                        <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong>
                    </div>
                    <div class="receipt-info">
                        <span>Email:</span>
                        <strong><?php echo htmlspecialchars($order['email']); ?></strong>
                    </div>
                    <?php if ($order['phone']): ?>
                    <div class="receipt-info">
                        <span>Phone:</span>
                        <strong><?php echo htmlspecialchars($order['phone']); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="receipt-section">
                    <h3>Order Items</h3>
                    <div class="receipt-items">
                        <?php foreach ($orderItems as $item): ?>
                        <div class="receipt-item">
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                                <?php if ($item['instructions']): ?>
                                <div class="item-instructions" style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.25rem;">
                                    Note: <?php echo htmlspecialchars($item['instructions']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-price">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="receipt-total">
                    <div>Total Amount</div>
                    <div class="total-amount">₱<?php echo number_format($order['total_amount'], 2); ?></div>
                </div>
            </div>

            <div class="receipt-actions">
                <button onclick="window.print()" class="btn">Print Receipt</button>
                <button onclick="downloadReceipt()" class="btn" style="background: var(--secondary-color);">Download PDF</button>
                <a href="online_ordering.php" class="btn" style="background: var(--text-light);">Back to Menu</a>
            </div>
        </div>
    </div>

    <script>
        function downloadReceipt() {
            // Create a new window for PDF generation
            const printWindow = window.open('', '_blank');
            const receiptContent = document.querySelector('.receipt-container').innerHTML;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Receipt - Order #<?php echo htmlspecialchars($order['order_number']); ?></title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                        .receipt-container { max-width: 400px; margin: 0 auto; }
                        .receipt-header { background: #6F4E37; color: white; padding: 1.5rem; text-align: center; }
                        .receipt-header h1 { margin: 0; font-size: 1.5rem; }
                        .receipt-header p { margin: 0.5rem 0 0 0; opacity: 0.9; }
                        .receipt-content { padding: 1.5rem; }
                        .receipt-section { margin-bottom: 1.5rem; }
                        .receipt-section h3 { color: #6F4E37; margin-bottom: 0.5rem; font-size: 1rem; border-bottom: 1px solid #E8D5C4; padding-bottom: 0.25rem; }
                        .receipt-info { display: flex; justify-content: space-between; margin-bottom: 0.25rem; }
                        .receipt-items { margin-bottom: 1rem; }
                        .receipt-item { display: flex; justify-content: space-between; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #E8D5C4; }
                        .receipt-item:last-child { border-bottom: none; }
                        .item-details { flex: 1; }
                        .item-name { font-weight: 500; }
                        .item-quantity { color: #64748b; font-size: 0.9rem; }
                        .item-price { font-weight: 500; color: #6F4E37; }
                        .receipt-total { background: #D4B996; padding: 1rem; border-radius: 0.5rem; text-align: center; }
                        .total-amount { font-size: 1.5rem; font-weight: 600; color: #6F4E37; }
                        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.8rem; font-weight: 500; text-transform: uppercase; }
                        .status-pending { background: #fef3c7; color: #92400e; }
                        .status-preparing { background: #dbeafe; color: #1e40af; }
                        .status-ready { background: #d1fae5; color: #065f46; }
                        .status-completed { background: #d1fae5; color: #065f46; }
                    </style>
                </head>
                <body>
                    ${receiptContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>
