<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

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
    // Log the error instead of exposing it directly
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error.']);
    exit;
}

// Get JSON input
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Log received data for debugging
error_log("Received order data: " . print_r($data, true));

// Validate input
if (!$data || !isset($data['items']) || empty($data['items']) || !isset($data['table_number']) || empty($data['table_number'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data. Please ensure items and table number are provided.']);
    exit;
}

$items = $data['items'];
$tableNumber = $data['table_number'];
$totalAmount = 0;

// Start transaction
$conn->begin_transaction();

try {
    // 1. Check ingredient stock and calculate total
    $ingredients_to_deduct = [];

    // Fetch required ingredients for all products in the order
    $product_ids = array_column($items, 'id');
    
    // Log product IDs being queried
    error_log("Querying ingredients for product IDs: " . implode(', ', $product_ids));
    
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt_ingredients = $conn->prepare("SELECT pi.product_id, pi.ingredient_id, pi.quantity AS required_quantity, i.name, i.unit, i.current_stock FROM product_ingredients pi JOIN ingredients i ON pi.ingredient_id = i.id WHERE pi.product_id IN ($placeholders)");
    
    // Use call_user_func_array to bind parameters dynamically
    $types = str_repeat('i', count($product_ids));
    $bind_params = array_merge([$types], array_values($product_ids)); // Use array_values to re-index if needed
    
    if (!call_user_func_array([$stmt_ingredients, 'bind_param'], $bind_params)) {
        throw new Exception("Bind param failed: " . $stmt_ingredients->error);
    }

    $stmt_ingredients->execute();
    $ingredients_result = $stmt_ingredients->get_result();

    $product_ingredients_map = [];
    while ($row = $ingredients_result->fetch_assoc()) {
        $product_ingredients_map[$row['product_id']][] = $row;
    }

    // Log fetched ingredient relationships
    error_log("Product-ingredient map: " . print_r($product_ingredients_map, true));

    // Calculate total and check stock for each item in the order
    foreach ($items as &$item) { // Use reference to modify item in place
        $productId = $item['id'];
        $quantity_ordered = $item['quantity'];

        // Fetch product price from database to prevent price manipulation
        $stmt_price = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt_price->bind_param("i", $productId);
        $stmt_price->execute();
        $price_result = $stmt_price->get_result();
        $product_data = $price_result->fetch_assoc();

        if (!$product_data) {
            throw new Exception("Product with ID " . htmlspecialchars($productId) . " not found.");
        }

        $item['price'] = $product_data['price'];
        $totalAmount += $item['price'] * $quantity_ordered;

        // Check ingredient stock for this product
        if (isset($product_ingredients_map[$productId])) {
            foreach ($product_ingredients_map[$productId] as $ingredient_info) {
                $ingredient_id = $ingredient_info['ingredient_id'];
                $required_quantity_per_product = $ingredient_info['required_quantity'];
                $current_stock = $ingredient_info['current_stock'];
                $ingredient_name = $ingredient_info['name'];
                $ingredient_unit = $ingredient_info['unit'];

                $total_required_quantity = $required_quantity_per_product * $quantity_ordered;

                if ($current_stock < $total_required_quantity) {
                    throw new Exception("Insufficient stock for '" . htmlspecialchars($ingredient_name) . "'. Required: " . htmlspecialchars($total_required_quantity) . " " . htmlspecialchars($ingredient_unit) . ", Available: " . htmlspecialchars($current_stock) . " " . htmlspecialchars($ingredient_unit) . ".");
                }

                // Add to deduction list
                if (!isset($ingredients_to_deduct[$ingredient_id])) {
                    $ingredients_to_deduct[$ingredient_id] = 0;
                }
                $ingredients_to_deduct[$ingredient_id] += $total_required_quantity;
            }
        }
        // Note: Products without entries in product_ingredients won't deduct ingredients.

    }
    unset($item); // Break the reference

    // Log ingredients and quantities to be deducted
    error_log("Ingredients to deduct: " . print_r($ingredients_to_deduct, true));

    // 2. Deduct ingredient stock
    $stmt_deduct = $conn->prepare("UPDATE ingredients SET current_stock = current_stock - ? WHERE id = ?");
    $deduction_successful = true;
    foreach ($ingredients_to_deduct as $ingredient_id => $quantity) {
        $stmt_deduct->bind_param("di", $quantity, $ingredient_id);
        if (!$stmt_deduct->execute()) {
            $deduction_successful = false;
            error_log("Failed to deduct ingredient ID " . $ingredient_id . ": " . $stmt_deduct->error);
            // Continue to try deducting others, but mark as failed
        }
    }

    if (!$deduction_successful) {
        throw new Exception("One or more ingredient deductions failed.");
    }

    // 3. Create order
    $orderNumber = uniqid('ORD'); // Generate a simple unique order number
    $stmt_order = $conn->prepare("INSERT INTO orders (order_number, total_amount, status, table_number, created_at) VALUES (?, ?, ?, ?, NOW())");
    $status = 'awaiting_payment';
    $stmt_order->bind_param("sdss", $orderNumber, $totalAmount, $status, $tableNumber);
    $stmt_order->execute();
    $orderId = $conn->insert_id;

    // 4. Add order items
    $stmt_order_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, instructions) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        // Ensure instructions are not null if not provided
        $instructions = isset($item['instructions']) ? $item['instructions'] : null;
        $stmt_order_item->bind_param("iiids", $orderId, $item['id'], $item['quantity'], $item['price'], $instructions);
        $stmt_order_item->execute();
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'order_number' => $orderNumber, 'message' => 'Order placed successfully!']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Log the error
    error_log("Order processing error: " . $e->getMessage());

    // Return error response to user
    echo json_encode(['success' => false, 'message' => 'Error processing order: ' . $e->getMessage()]);
} finally {
    // Close connection
    if ($conn) {
        $conn->close();
    }
}

?> 