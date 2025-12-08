<?php
session_start();

// Check if customer is logged in
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

header('Content-Type: application/json');

try {
    require_once 'config/database.php';

    // Get POST data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!$data || !isset($data['items']) || empty($data['items'])) {
        throw new Exception('Invalid or empty order data');
    }

    $items = $data['items'];
    $totalAmount = 0;

    // Log database connection attempt
    error_log("Processing online order for customer ID: " . $_SESSION['customer_id']);
    
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection not established");
    }
    
    $pdo->beginTransaction();

    // Validate stock and calculate total
    $productIds = array_column($items, 'id');
    error_log("Processing products: " . implode(', ', $productIds));
    
    // Fetch current stock and price for all items
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmtStock = $pdo->prepare("SELECT id, stock, price FROM products WHERE id IN ($placeholders)");
    $stmtStock->execute($productIds);
    
    $currentStockData = $stmtStock->fetchAll(PDO::FETCH_ASSOC);
    $currentStock = [];
    foreach ($currentStockData as $product) {
        $currentStock[$product['id']] = [
            'stock' => $product['stock'],
            'price' => $product['price']
        ];
    }

    foreach ($items as &$item) {
        $productId = $item['id'];
        $quantity = $item['quantity'];

        if (!isset($currentStock[$productId])) {
            throw new Exception("Product with ID " . $productId . " not found.");
        }

        $availableStock = $currentStock[$productId]['stock'];
        $price = $currentStock[$productId]['price'];

        if ($quantity <= 0 || $quantity > $availableStock) {
            throw new Exception("Insufficient stock or invalid quantity for product ID: " . $productId);
        }

        $item['price'] = $price;
        $totalAmount += $price * $quantity;
    }
    unset($item);

    // Generate order number
    $orderNumber = 'ONL-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    error_log("Generated online order number: " . $orderNumber);

    // Create order - goes directly to pending (queue) since e-wallet payment is automatic
    $stmtOrder = $pdo->prepare("INSERT INTO orders (order_number, order_date, total_amount, status, table_number, dining_option, customer_id, payment_method) VALUES (?, NOW(), ?, 'pending', 'ONLINE', 'take_out', ?, 'e_wallet')");
    $stmtOrder->execute([$orderNumber, $totalAmount, $_SESSION['customer_id']]);
    $orderId = $pdo->lastInsertId();
    
    error_log("Online order created successfully with ID: " . $orderId);

    // Add order items and update inventory
    $stmtOrderItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, instructions) VALUES (?, ?, ?, ?, ?)");
    $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($items as $item) {
        // Add order item
        $stmtOrderItem->execute([
            $orderId,
            $item['id'],
            $item['quantity'],
            $item['price'],
            $item['instructions'] ?? null
        ]);

        // Update inventory
        $stmtUpdateStock->execute([
            $item['quantity'],
            $item['id']
        ]);
        
        error_log("Added order item: Product ID " . $item['id'] . ", Quantity " . $item['quantity']);
    }

    $pdo->commit();
    error_log("Online order transaction committed successfully");
    
    echo json_encode([
        'success' => true, 
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'total_amount' => $totalAmount,
        'message' => 'Order placed successfully!'
    ]);

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Error processing online order: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error processing order: ' . $e->getMessage()]);
}
?>
