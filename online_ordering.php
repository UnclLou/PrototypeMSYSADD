<?php
session_start();

// Check if customer is logged in
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: customer_login.php');
    exit();
}

require_once 'config/database.php';

// Fetch products to display
$stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY category, name");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Ordering - Let's Meet Cafe</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .online-header {
            background: var(--primary-color);
            color: white;
            padding: 1rem 0;
            text-align: center;
        }
        .online-header h1 {
            margin-bottom: 0.5rem;
        }
        .customer-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .online-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        .products-section {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        .category-filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid var(--border-color);
            background: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        .product-card {
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        .product-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            background: #f5f5f5;
        }
        .product-image-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 3rem;
        }
        .order-summary {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        .cart-items {
            margin-bottom: 1.5rem;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        .cart-total {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        .payment-info {
            background: var(--accent-color);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }
        .payment-info h4 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        .checkout-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
        }
        .checkout-btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
        }
        .hidden {
            display: none;
        }
        @media (max-width: 768px) {
            .online-layout {
                grid-template-columns: 1fr;
            }
            .customer-info {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="online-header">
            <h1>Let's Meet Cafe</h1>
            <p>Online Ordering - Pickup Only</p>
            <div style="margin-top: 1rem;">
                <a href="online_home.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid white;">← Back to Home</a>
            </div>
        </div>

        <div class="customer-info">
            <div>
                <h3>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</h3>
                <p>Order for pickup at our cafe</p>
            </div>
            <div>
                <a href="customer_logout.php" class="btn" style="background: var(--danger);">Logout</a>
            </div>
        </div>

        <div class="online-layout">
            <div class="products-section">
                <h2>Menu</h2>
                <div class="category-filters">
                    <button class="filter-btn active" data-category="all">All</button>
                    <?php
                    // Fetch unique categories for filters
                    $categoryStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE stock > 0 ORDER BY category");
                    $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($categories as $category):
                    ?>
                        <button class="filter-btn" data-category="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></button>
                    <?php endforeach; ?>
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
                            <button class="btn add-to-cart">Add to Order</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="order-summary">
                <h2>Your Order</h2>
                <div class="cart-items">
                    <p>Your order is empty.</p>
                </div>
                <div class="cart-total">
                    Total: ₱<span id="total-amount">0.00</span>
                </div>
                
                <div class="payment-info">
                    <h4>Payment Information</h4>
                    <p><strong>Payment Method:</strong> E-Wallet Only</p>
                    <p><strong>Order Type:</strong> Pickup Only</p>
                    <p><strong>Pickup Location:</strong> Let's Meet Cafe</p>
                </div>

                <button class="btn checkout-btn" disabled>Place Order</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cart = [];
            const productsGrid = document.querySelector('.products-grid');
            const cartItems = document.querySelector('.cart-items');
            const totalAmount = document.getElementById('total-amount');
            const checkoutBtn = document.querySelector('.checkout-btn');
            const filterButtons = document.querySelectorAll('.filter-btn');

            // Category filtering
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.dataset.category;
                    
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    document.querySelectorAll('.product-card').forEach(card => {
                        if (category === 'all' || card.dataset.category === category) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Add to cart functionality
            productsGrid.addEventListener('click', function(e) {
                if (e.target.classList.contains('add-to-cart')) {
                    const productCard = e.target.closest('.product-card');
                    const productId = productCard.dataset.id;
                    const productName = productCard.querySelector('h3').textContent;
                    const productPrice = parseFloat(productCard.dataset.price);

                    const existingItem = cart.find(item => item.id === productId);
                    if (existingItem) {
                        existingItem.quantity++;
                    } else {
                        cart.push({
                            id: productId,
                            name: productName,
                            price: productPrice,
                            quantity: 1
                        });
                    }
                    updateCart();
                }
            });

            // Update cart display
            function updateCart() {
                cartItems.innerHTML = '';
                let total = 0;

                if (cart.length === 0) {
                    cartItems.innerHTML = '<p>Your order is empty.</p>';
                    checkoutBtn.disabled = true;
                } else {
                    cart.forEach(item => {
                        const itemTotal = item.price * item.quantity;
                        total += itemTotal;

                        const cartItem = document.createElement('div');
                        cartItem.className = 'cart-item';
                        cartItem.innerHTML = `
                            <div>
                                <span>${item.name}</span>
                                <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem;">
                                    <button class="quantity-btn minus" data-id="${item.id}">-</button>
                                    <span>${item.quantity}</span>
                                    <button class="quantity-btn plus" data-id="${item.id}">+</button>
                                </div>
                            </div>
                            <div>
                                <span>₱${itemTotal.toFixed(2)}</span>
                                <button class="remove-item" data-id="${item.id}" style="margin-left: 0.5rem; color: var(--danger);">×</button>
                            </div>
                        `;
                        cartItems.appendChild(cartItem);
                    });
                    checkoutBtn.disabled = false;
                }

                totalAmount.textContent = total.toFixed(2);
            }

            // Handle quantity changes and item removal
            cartItems.addEventListener('click', function(e) {
                const target = e.target;
                const productId = target.dataset.id;

                if (target.classList.contains('remove-item')) {
                    const index = cart.findIndex(item => item.id === productId);
                    if (index > -1) {
                        cart.splice(index, 1);
                        updateCart();
                    }
                } else if (target.classList.contains('quantity-btn')) {
                    const item = cart.find(item => item.id === productId);
                    if (item) {
                        if (target.classList.contains('plus')) {
                            item.quantity++;
                        } else if (target.classList.contains('minus')) {
                            if (item.quantity > 1) {
                                item.quantity--;
                            } else {
                                const index = cart.findIndex(i => i.id === productId);
                                cart.splice(index, 1);
                            }
                        }
                        updateCart();
                    }
                }
            });

            // Checkout functionality
            checkoutBtn.addEventListener('click', async function() {
                if (cart.length === 0) {
                    alert('Your order is empty!');
                    return;
                }
                
                if (!confirm('Confirm your order for ₱' + totalAmount.textContent + '? This will be charged to your e-wallet.')) {
                    return;
                }
                
                const orderData = {
                    items: cart
                };

                try {
                    checkoutBtn.disabled = true;
                    checkoutBtn.textContent = 'Processing...';

                    const response = await fetch('process_online_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(orderData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Clear cart
                        cart.length = 0;
                        updateCart();
                        
                        // Show success message and redirect to receipt
                        alert('Order placed successfully! Redirecting to receipt...');
                        window.location.href = 'generate_receipt.php?order_id=' + result.order_id;
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error placing order. Please try again.');
                } finally {
                    checkoutBtn.disabled = false;
                    checkoutBtn.textContent = 'Place Order';
                }
            });

            // Initial cart display
            updateCart();
        });
    </script>
</body>
</html>