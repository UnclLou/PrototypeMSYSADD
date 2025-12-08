<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser
ini_set('log_errors', 1); // Log errors instead
ini_set('error_log', 'php_errors.log'); // Set error log file

// Set proper JSON header
header('Content-Type: application/json');

try {
    require_once 'config/database.php';

    // Log the incoming request
    error_log("Received order request: " . file_get_contents('php://input'));

    // Get POST data (JSON format)
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!$data || !isset($data['items']) || empty($data['items'])) {
        throw new Exception('Your cart is empty. Please add items before placing an order.');
    }
    
    // Validate that items array is not empty
    if (!is_array($data['items']) || count($data['items']) === 0) {
        throw new Exception('Your cart is empty. Please add items before placing an order.');
    }

    // Validate required fields
    if (!isset($data['table_number']) || empty($data['table_number'])) {
        throw new Exception('Table number is required');
    }

    // Set default dining option if not provided
    $diningOption = isset($data['dining_option']) ? $data['dining_option'] : 'dine_in';
    
    // Validate dining option value
    if (!in_array($diningOption, ['dine_in', 'take_out'])) {
        throw new Exception('Invalid dining option. Must be either "dine_in" or "take_out"');
    }

    $items = $data['items'];
    $totalAmount = 0;

    // Log database connection attempt
    error_log("Attempting database connection...");
    
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection not established");
    }
    
    error_log("Database connection successful");
    
    // Track if transaction was started
    $transactionStarted = false;
    $pdo->beginTransaction();
    $transactionStarted = true;

    // Validate stock and calculate total before creating order
    $productIds = array_column($items, 'id');
    error_log("Processing products: " . implode(', ', $productIds));
    
    // Fetch current stock and price for all items in the cart
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmtStock = $pdo->prepare("SELECT id, stock, price FROM products WHERE id IN ($placeholders)");
    $stmtStock->execute($productIds);
    
    // Fetch results as associative array and restructure for easy lookup
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

    // Log order creation attempt
    error_log("Creating order with total amount: " . $totalAmount);
    error_log("Table number: " . $data['table_number']);
    error_log("Dining option: " . $diningOption);

    // Generate order number (format: ORD-YYYYMMDD-XXXX)
    $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    error_log("Generated order number: " . $orderNumber);

    // Create order
    $stmtOrder = $pdo->prepare("INSERT INTO orders (order_number, order_date, total_amount, status, table_number, dining_option) VALUES (?, NOW(), ?, 'awaiting_payment', ?, ?)");
    $stmtOrder->execute([$orderNumber, $totalAmount, $data['table_number'], $diningOption]);
    $orderId = $pdo->lastInsertId();
    
    error_log("Order created successfully with ID: " . $orderId);

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
    error_log("Transaction committed successfully");
    
    echo json_encode(['success' => true, 'order_id' => $orderId, 'message' => 'Order placed successfully!']);

} catch (Exception $e) {
    // Only rollback if transaction was actually started
    if (isset($pdo) && isset($transactionStarted) && $transactionStarted) {
        try {
            $pdo->rollBack();
        } catch (PDOException $rollbackError) {
            error_log("Error during rollback: " . $rollbackError->getMessage());
        }
    }
    error_log("Error processing order: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error processing order: ' . $e->getMessage()]);
}
?> 