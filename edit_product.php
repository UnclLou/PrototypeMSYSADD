<?php
session_start();

// Check if user is logged in and has admin role
$has_access = false;
$required_role = 'admin';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $required_role) {
    $has_access = true;
}

require_once 'config/database.php';

$message = '';
$product = null;

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

// Get product ID from query string
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product data
if ($productId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $message = '<div class="error-message">Product not found.</div>';
            $productId = 0;
        }
    } catch (PDOException $e) {
        $message = '<div class="error-message">Error fetching product: ' . htmlspecialchars($e->getMessage()) . '</div>';
        $productId = 0;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];

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
                    $message = '<div class="success-message">Product updated successfully!</div>';
                }
            } else {
                // Update without changing image
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $stock, $category, $id]);
                $message = '<div class="success-message">Product updated successfully!</div>';
            }
            
            // Refresh product data
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
        } catch (PDOException $e) {
            $message = '<div class="error-message">Failed to update product: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - CoffeeQueue</title>
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
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="queue.php">Queue</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="manage_products.php">Products</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="hero">
                <h2>Edit Product</h2>
                <p>Update product information</p>
            </section>

            <?php if ($has_access): ?>
                <?php if ($productId > 0 && $product): ?>
                    <?php echo $message; ?>
                    
                    <section class="form-section">
                        <div class="form-card">
                            <h3>Edit Menu Item</h3>
                            <form action="edit_product.php?id=<?php echo $productId; ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                
                                <?php if (!empty($product['image'])): ?>
                                    <div style="margin-bottom: 1.5rem; text-align: center;">
                                        <p><strong>Current Image:</strong></p>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 300px; max-height: 300px; border-radius: var(--border-radius); border: 2px solid var(--border-color);">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="name">Menu Name:</label>
                                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="category">Category:</label>
                                        <select id="category" name="category" required>
                                            <option value="Coffee" <?php echo $product['category'] === 'Coffee' ? 'selected' : ''; ?>>Coffee</option>
                                            <option value="Tea" <?php echo $product['category'] === 'Tea' ? 'selected' : ''; ?>>Tea</option>
                                            <option value="Pastries" <?php echo $product['category'] === 'Pastries' ? 'selected' : ''; ?>>Pastries</option>
                                            <option value="Snacks" <?php echo $product['category'] === 'Snacks' ? 'selected' : ''; ?>>Snacks</option>
                                            <option value="Other" <?php echo $product['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="price">Price:</label>
                                        <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="stock">Stock:</label>
                                        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Description:</label>
                                        <textarea id="description" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Product Image (leave empty to keep current):</label>
                                        <input type="file" id="image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                        <small style="display: block; color: var(--text-light); margin-top: 0.25rem;">Accepted formats: JPEG, PNG, GIF, WebP (Max 5MB)</small>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn">Update Product</button>
                                    <a href="manage_products.php" class="btn" style="background: var(--text-light);">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </section>
                <?php else: ?>
                    <section class="hero">
                        <h2>Product Not Found</h2>
                        <p>The product you're looking for doesn't exist.</p>
                        <a href="manage_products.php" class="btn">Back to Products</a>
                    </section>
                <?php endif; ?>
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
</body>
</html>

