<?php
/**
 * Setup script to add image column to products table
 * Run this once to add the image column to your database
 */
require_once 'config/database.php';

try {
    // Check if image column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'image'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Add image column
        $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER description");
        echo "<h2>Success!</h2>";
        echo "<p>Image column has been added successfully to the products table.</p>";
        echo "<p>You can now upload images for your products.</p>";
        echo "<p><a href='manage_products.php'>Go to Manage Products</a></p>";
    } else {
        echo "<h2>Already Set Up</h2>";
        echo "<p>The image column already exists in the products table.</p>";
        echo "<p><a href='manage_products.php'>Go to Manage Products</a></p>";
    }
} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>

