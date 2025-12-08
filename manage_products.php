<?php
session_start();

// Assume access is granted initially
$has_access = false;
$required_role = 'admin';

// Check if the user is logged in and has the required role
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $required_role) {
    $has_access = true;
}

require_once 'config/database.php';

// Include necessary functions for product management (will be added later)
// require_once 'includes/product_functions.php';

$message = ''; // To display success or error messages

// Function to handle image upload
function uploadProductImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false; // Invalid file type
    }
    
    if ($file['size'] > $maxSize) {
        return false; // File too large
    }
    
    $uploadDir = 'uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_', true) . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }
    
    return false;
}

// Handle Add/Edit/Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            // Handle add product
            $name = $_POST['name'];
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category = $_POST['category'];
            $imagePath = null;

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadProductImage($_FILES['image']);
                if ($uploadResult === false) {
                    $message = '<div class="error-message">Invalid image file. Please upload a valid image (JPEG, PNG, GIF, WebP) under 5MB.</div>';
                } elseif ($uploadResult !== null) {
                    $imagePath = $uploadResult;
                }
            }

            // Basic validation
            if (empty($name) || empty($price) || empty($stock) || empty($category)) {
                $message = '<div class="error-message">Please fill in all required fields.</div>';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category, image) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $stock, $category, $imagePath]);
                    $message = '<div class="success-message">Product added successfully!</div>';
                } catch (PDOException $e) {
                    $message = '<div class="error-message">Failed to add product: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }

        } elseif ($action === 'edit') {
            // Handle edit product
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category = $_POST['category'];

            // Basic validation
             if (empty($name) || empty($price) || empty($stock) || empty($category)) {
                 $message = '<div class="error-message">Please fill in all required fields.</div>';
             } else {
                 try {
                     // Handle image upload if new image is provided
                     if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                         $uploadResult = uploadProductImage($_FILES['image']);
                         if ($uploadResult === false) {
                             $message = '<div class="error-message">Invalid image file. Please upload a valid image (JPEG, PNG, GIF, WebP) under 5MB.</div>';
                         } elseif ($uploadResult !== null) {
                             // Delete old image if exists
                             $stmtOld = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                             $stmtOld->execute([$id]);
                             $oldProduct = $stmtOld->fetch();
                             if ($oldProduct && !empty($oldProduct['image']) && file_exists($oldProduct['image'])) {
                                 unlink($oldProduct['image']);
                             }
                             // Update with new image
                             $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, image = ? WHERE id = ?");
                             $stmt->execute([$name, $description, $price, $stock, $category, $uploadResult, $id]);
                         }
                     } else {
                         // Update without changing image
                         $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ? WHERE id = ?");
                         $stmt->execute([$name, $description, $price, $stock, $category, $id]);
                     }
                     $message = '<div class="success-message">Product updated successfully!</div>';
                 } catch (PDOException $e) {
                     $message = '<div class="error-message">Failed to update product: ' . htmlspecialchars($e->getMessage()) . '</div>';
                 }
             }

        } elseif ($action === 'delete') {
            // Handle delete product
            $id = $_POST['id'];

            try {
                // Get image path before deleting
                $stmtImg = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                $stmtImg->execute([$id]);
                $product = $stmtImg->fetch();
                
                // Delete product from database
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                
                // Delete image file if exists
                if ($product && !empty($product['image']) && file_exists($product['image'])) {
                    unlink($product['image']);
                }
                
                $message = '<div class="success-message">Product deleted successfully!</div>';
            } catch (PDOException $e) {
                $message = '<div class="error-message">Failed to delete product: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    }
}

// Fetch all products for display
$products = [];
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY category, name");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = '<div class="error-message">Failed to fetch products: ' . $e->getMessage() . '</div>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - CoffeeQueue</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="payment_process.php">Payment</a></li>
                    <li><a href="queue.php">Queue</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="manage_products.php">Products</a></li>
                        <li><a href="manage_orders.php">Orders</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>
            <section class="hero">
                <h2>Manage Menu</h2>
                <p>Add, update, and remove products and menu items</p>
            </section>

            <?php if ($has_access): ?>
                <?php echo $message; // Display messages only if access is granted ?>

                <section class="form-section">
                    <div class="form-card">
                        <h3>Add New Menu</h3>
                        <form action="manage_products.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name">Menu Name:</label>
                                    <input type="text" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="category">Category:</label>
                                    <select id="category" name="category" required>
                                        <option value="Coffee">Coffee</option>
                                        <option value="Tea">Tea</option>
                                        <option value="Pastries">Pastries</option>
                                        <option value="Snacks">Snacks</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="price">Price:</label>
                                    <input type="number" id="price" name="price" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="stock">Stock:</label>
                                    <input type="number" id="stock" name="stock" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description:</label>
                                    <textarea id="description" name="description"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="image">Product Image:</label>
                                    <input type="file" id="image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                    <small style="display: block; color: var(--text-light); margin-top: 0.25rem;">Accepted formats: JPEG, PNG, GIF, WebP (Max 5MB)</small>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn">Add Product</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="inventory-table">
                    <h3>Existing Menu</h3>
                    <div class="product-cards-grid">
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <div class="product-card">
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image" style="width: 100%; height: 200px; object-fit: cover; border-radius: var(--border-radius); margin-bottom: 1rem;">
                                        <?php else: ?>
                                            <div class="product-image-placeholder" style="width: 100%; height: 200px; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%); border-radius: var(--border-radius); margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; color: #999; font-size: 3rem;">🍽️</div>
                                        <?php endif; ?>
                                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                                        <p><strong>Price:</strong> ₱<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                                        <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['stock']); ?></p>
                                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                        <div class="product-card-actions">
                                            <!-- Edit and Delete forms/buttons -->
                                            <form action="manage_products.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                            </form>
                                            <!-- Edit button can trigger a modal or redirect to an edit page -->
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-small">Edit</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No products found.</p>
                            <?php endif; ?>
                    </div>
                </section>

            <?php else: ?>
                <section class="hero">
                    <h2>Access Denied</h2>
                    <p>You do not have permission to view this page.</p>
                </section>
            <?php endif; ?>

        </main>

        <footer>
            <p>&copy; 2025 CoffeeQueue. All rights reserved.</p>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
    <!-- Add specific JS for product management if needed (e.g., for modals) -->
</body>
</html> 