<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Ordering - Let's Meet Cafe</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .online-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        .online-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .online-hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .online-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        .feature-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .auth-section {
            background: var(--accent-color);
            padding: 3rem 0;
            text-align: center;
        }
        .auth-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .auth-buttons .btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            min-width: 150px;
        }
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }
        .info-section {
            background: white;
            padding: 3rem 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .info-item {
            text-align: center;
            padding: 1.5rem;
        }
        .info-item h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .back-to-admin {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
        }
        .back-to-admin .btn {
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        .back-to-admin .btn:hover {
            background: var(--primary-color);
            color: white;
        }
        @media (max-width: 768px) {
            .online-hero h1 {
                font-size: 2rem;
            }
            .auth-buttons {
                flex-direction: column;
                align-items: center;
            }
            .auth-buttons .btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Hero Section -->
        <section class="online-hero">
            <h1>Let's Meet Cafe</h1>
            <p>Order online for quick pickup</p>
            <p>Fresh coffee and delicious food, ready when you are!</p>
        </section>

        <!-- Features Section -->
        <section class="online-features">
            <div class="feature-card">
                <div class="feature-icon">☕</div>
                <h3>Fresh Coffee</h3>
                <p>Premium coffee beans roasted to perfection, prepared by our expert baristas</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🍰</div>
                <h3>Delicious Food</h3>
                <p>Fresh pastries, sandwiches, and light meals made with quality ingredients</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Quick Pickup</h3>
                <p>Order online and pick up at your convenience - no waiting in line!</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💳</div>
                <h3>E-Wallet Payment</h3>
                <p>Secure and convenient e-wallet payments for a seamless experience</p>
            </div>
        </section>

        <!-- Authentication Section -->
        <section class="auth-section">
            <h2>Ready to Order?</h2>
            <p>Sign in to your account or create a new one to start ordering</p>
            <div class="auth-buttons">
                <a href="customer_login.php" class="btn btn-primary">Sign In</a>
                <a href="customer_register.php" class="btn btn-secondary">Create Account</a>
            </div>
        </section>

        <!-- Information Section -->
        <section class="info-section">
            <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 1rem;">How It Works</h2>
            <div class="info-grid">
                <div class="info-item">
                    <h4>1. Create Account</h4>
                    <p>Sign up with your email and personal information</p>
                </div>
                <div class="info-item">
                    <h4>2. Browse Menu</h4>
                    <p>Explore our delicious coffee and food options</p>
                </div>
                <div class="info-item">
                    <h4>3. Place Order</h4>
                    <p>Add items to cart and checkout with e-wallet payment</p>
                </div>
                <div class="info-item">
                    <h4>4. Pick Up</h4>
                    <p>Visit our cafe and collect your order when ready</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer style="background: var(--primary-color); color: white; padding: 2rem 0; text-align: center; margin-top: 3rem;">
            <p>&copy; 2025 Let's Meet Cafe. All rights reserved.</p>
            <p style="margin-top: 0.5rem; opacity: 0.8;">Lot 4, Block 5 Quirino Hwy, Caloocan, 1421 Metro Manila</p>
        </footer>
    </div>
</body>
</html>
