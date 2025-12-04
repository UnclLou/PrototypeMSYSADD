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

// Common coffee shop ingredients
$ingredients = [
    // Coffee
    ['name' => 'Espresso Beans', 'unit' => 'kg', 'current_stock' => 5, 'minimum_stock' => 2, 'cost_per_unit' => 800, 'category' => 'Coffee'],
    ['name' => 'Arabica Beans', 'unit' => 'kg', 'current_stock' => 5, 'minimum_stock' => 2, 'cost_per_unit' => 900, 'category' => 'Coffee'],
    ['name' => 'Robusta Beans', 'unit' => 'kg', 'current_stock' => 5, 'minimum_stock' => 2, 'cost_per_unit' => 700, 'category' => 'Coffee'],
    
    // Milk
    ['name' => 'Fresh Milk', 'unit' => 'L', 'current_stock' => 10, 'minimum_stock' => 5, 'cost_per_unit' => 85, 'category' => 'Milk'],
    ['name' => 'Oat Milk', 'unit' => 'L', 'current_stock' => 5, 'minimum_stock' => 2, 'cost_per_unit' => 120, 'category' => 'Milk'],
    ['name' => 'Almond Milk', 'unit' => 'L', 'current_stock' => 5, 'minimum_stock' => 2, 'cost_per_unit' => 150, 'category' => 'Milk'],
    ['name' => 'Soy Milk', 'unit' => 'L', 'current_stock' => 5, 'minimum_stock' => 2, 'cost_per_unit' => 100, 'category' => 'Milk'],
    
    // Syrups
    ['name' => 'Vanilla Syrup', 'unit' => 'L', 'current_stock' => 2, 'minimum_stock' => 1, 'cost_per_unit' => 250, 'category' => 'Syrup'],
    ['name' => 'Caramel Syrup', 'unit' => 'L', 'current_stock' => 2, 'minimum_stock' => 1, 'cost_per_unit' => 250, 'category' => 'Syrup'],
    ['name' => 'Hazelnut Syrup', 'unit' => 'L', 'current_stock' => 2, 'minimum_stock' => 1, 'cost_per_unit' => 250, 'category' => 'Syrup'],
    ['name' => 'Chocolate Syrup', 'unit' => 'L', 'current_stock' => 2, 'minimum_stock' => 1, 'cost_per_unit' => 250, 'category' => 'Syrup'],
    
    // Toppings
    ['name' => 'Whipped Cream', 'unit' => 'L', 'current_stock' => 2, 'minimum_stock' => 1, 'cost_per_unit' => 200, 'category' => 'Toppings'],
    ['name' => 'Chocolate Chips', 'unit' => 'kg', 'current_stock' => 2, 'minimum_stock' => 1, 'cost_per_unit' => 300, 'category' => 'Toppings'],
    ['name' => 'Caramel Drizzle', 'unit' => 'L', 'current_stock' => 1, 'minimum_stock' => 0.5, 'cost_per_unit' => 250, 'category' => 'Toppings'],
    ['name' => 'Cinnamon Powder', 'unit' => 'g', 'current_stock' => 500, 'minimum_stock' => 200, 'cost_per_unit' => 150, 'category' => 'Toppings'],
    
    // Bakery
    ['name' => 'Croissant', 'unit' => 'pcs', 'current_stock' => 20, 'minimum_stock' => 10, 'cost_per_unit' => 35, 'category' => 'Bakery'],
    ['name' => 'Chocolate Muffin', 'unit' => 'pcs', 'current_stock' => 15, 'minimum_stock' => 8, 'cost_per_unit' => 40, 'category' => 'Bakery'],
    ['name' => 'Cheese Cake', 'unit' => 'pcs', 'current_stock' => 10, 'minimum_stock' => 5, 'cost_per_unit' => 120, 'category' => 'Bakery'],
    
    // Other
    ['name' => 'Sugar', 'unit' => 'kg', 'current_stock' => 5, 'minimum_stock' => 2, 'cost_per_unit' => 60, 'category' => 'Other'],
    ['name' => 'Brown Sugar', 'unit' => 'kg', 'current_stock' => 3, 'minimum_stock' => 1, 'cost_per_unit' => 70, 'category' => 'Other'],
    ['name' => 'Ice Cubes', 'unit' => 'kg', 'current_stock' => 10, 'minimum_stock' => 5, 'cost_per_unit' => 20, 'category' => 'Other'],
    ['name' => 'Paper Cups (12oz)', 'unit' => 'pcs', 'current_stock' => 200, 'minimum_stock' => 100, 'cost_per_unit' => 2, 'category' => 'Other'],
    ['name' => 'Paper Cups (16oz)', 'unit' => 'pcs', 'current_stock' => 200, 'minimum_stock' => 100, 'cost_per_unit' => 2.5, 'category' => 'Other'],
    ['name' => 'Plastic Lids', 'unit' => 'pcs', 'current_stock' => 400, 'minimum_stock' => 200, 'cost_per_unit' => 1, 'category' => 'Other'],
    ['name' => 'Straws', 'unit' => 'pcs', 'current_stock' => 500, 'minimum_stock' => 250, 'cost_per_unit' => 0.5, 'category' => 'Other']
];

// Insert ingredients
$stmt = $conn->prepare("INSERT INTO ingredients (name, unit, current_stock, minimum_stock, cost_per_unit, category) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($ingredients as $ingredient) {
    $stmt->bind_param("ssddds", 
        $ingredient['name'],
        $ingredient['unit'],
        $ingredient['current_stock'],
        $ingredient['minimum_stock'],
        $ingredient['cost_per_unit'],
        $ingredient['category']
    );
    $stmt->execute();
}

echo "Ingredients added successfully!";
$conn->close();
?> 