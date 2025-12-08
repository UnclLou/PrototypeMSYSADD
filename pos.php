<?php
require_once 'config/database.php';

// Fetch products from database
$stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY category, name");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - CoffeeQueue</title>
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
                    <li><a href="pos.php">Order Now</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="pos-container">
                <div class="products-section">
                    <div class="category-filters">
                        <button class="filter-btn active" data-category="all">All</button>
                        <button class="filter-btn" data-category="Coffee">Coffee</button>
                        <button class="filter-btn" data-category="Pastry">Pastry</button>
                        <button class="filter-btn" data-category="Dessert">Dessert</button>
                    </div>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                        <div class="product-card" 
                             data-id="<?php echo $product['id']; ?>" 
                             data-price="<?php echo $product['price']; ?>"
                             data-category="<?php echo $product['category']; ?>">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <?php else: ?>
                                <div class="product-image-placeholder">🍽️</div>
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
                            <p class="stock">Stock: <?php echo $product['stock']; ?></p>
                            <button class="btn add-to-cart">Add to Cart</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cart-section">
                    <h2>Current Order</h2>
                    <div class="cart-items">
                        <!-- Cart items will be added here dynamically -->
                    </div>
                    <div class="cart-total">
                        <h3>Total: ₱<span id="total-amount">0.00</span></h3>
                    </div>
                    <button class="btn checkout-btn">Process Order</button>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/pos.js"></script>
</body>
</html> 