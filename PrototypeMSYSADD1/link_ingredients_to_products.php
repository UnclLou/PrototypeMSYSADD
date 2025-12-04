<?php
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

// Define product-ingredient relationships (Product Name => [[Ingredient Name, Quantity, Ingredient Unit]])
$product_recipes = [
    'Cappuccino' => [
        ['Espresso Beans', 0.018, 'kg'], // Approx. 18g per shot
        ['Fresh Milk', 0.2, 'L'], // Approx. 200ml
        ['Paper Cups (12oz)', 1, 'pcs'], // Assuming 12oz cup
    ],
    'Espresso' => [
        ['Espresso Beans', 0.018, 'kg'], // Approx. 18g per shot
        ['Paper Cups (12oz)', 1, 'pcs'], // Assuming small cup/paper cup for takeout
    ],
    'Latte' => [
        ['Espresso Beans', 0.018, 'kg'], // Approx. 18g per shot
        ['Fresh Milk', 0.3, 'L'], // Approx. 300ml
        ['Paper Cups (16oz)', 1, 'pcs'], // Assuming 16oz cup
    ],
    'Mocha' => [
        ['Espresso Beans', 0.018, 'kg'], // Approx. 18g per shot
        ['Fresh Milk', 0.3, 'L'], // Approx. 300ml
        ['Chocolate Syrup', 0.05, 'L'], // Approx. 50ml
        ['Whipped Cream', 0.03, 'L'], // Approx. 30ml
        ['Paper Cups (16oz)', 1, 'pcs'], // Assuming 16oz cup
    ],
    // Add relationships for other products if needed (e.g., bakery ingredients)
    // 'Cheesecake' => [['Ingredient Name', Quantity, 'Unit'], ...],
    // 'Chocolate Cake' => [['Ingredient Name', Quantity, 'Unit'], ...],
    // 'Croissant' => [['Ingredient Name', Quantity, 'Unit'], ...],
];

// Prepare insert statement for product_ingredients table
$insert_stmt = $conn->prepare("INSERT INTO product_ingredients (product_id, ingredient_id, quantity) VALUES (?, ?, ?)");

// Clear existing relationships to prevent duplicates on re-run
$conn->query("TRUNCATE TABLE product_ingredients");

$all_products = $conn->query("SELECT id, name FROM products");
$product_map = [];
while($row = $all_products->fetch_assoc()) {
    $product_map[$row['name']] = $row['id'];
}

$all_ingredients = $conn->query("SELECT id, name FROM ingredients");
$ingredient_map = [];
while($row = $all_ingredients->fetch_assoc()) {
    $ingredient_map[$row['name']] = $row['id'];
}

foreach ($product_recipes as $product_name => $ingredients) {
    if (!isset($product_map[$product_name])) {
        echo "Warning: Product '" . $product_name . "' not found in products table.\n";
        continue;
    }
    $product_id = $product_map[$product_name];

    foreach ($ingredients as $ingredient_data) {
        $ingredient_name = $ingredient_data[0];
        $quantity = $ingredient_data[1];

        if (!isset($ingredient_map[$ingredient_name])) {
            echo "Warning: Ingredient '" . $ingredient_name . "' not found in ingredients table.\n";
            continue;
        }
        $ingredient_id = $ingredient_map[$ingredient_name];

        // Insert the relationship
        $insert_stmt->bind_param("iid", $product_id, $ingredient_id, $quantity);
        $insert_stmt->execute();
    }
}

echo "Product-ingredient relationships added successfully!";
$conn->close();
?> 