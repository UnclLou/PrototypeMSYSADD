<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Ordering - Let's Meet Cafe</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* 
         * Background Image: Place your cafe image as 'uploads/cafe_background.jpg'
         * Supported formats: JPG, JPEG, PNG, WebP
         * Recommended size: 1920x1080 or larger for best quality
         */
        body {
            background-image: url('uploads/cafe_background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            position: relative;
            /* Fallback gradient if image doesn't exist */
            background-color: var(--primary-color);
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }
        .container {
            position: relative;
            z-index: 1;
        }
        .online-hero {
            background: linear-gradient(135deg, rgba(111, 78, 55, 0.85) 0%, rgba(184, 126, 92, 0.85) 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
            backdrop-filter: blur(2px);
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
        .auth-section {
            background: rgba(0, 0, 0, 0.3);
            padding: 3rem 0;
            text-align: center;
            color: white;
            backdrop-filter: blur(3px);
        }
        .auth-section h2 {
            color: white;
        }
        .auth-section p {
            color: white;
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
            background: rgba(0, 0, 0, 0.3);
            padding: 3rem 0;
            backdrop-filter: blur(3px);
        }
        .info-section h2 {
            color: white;
        }
        .info-item {
            text-align: center;
            padding: 1.5rem;
        }
        .info-item h4 {
            color: white;
            margin-bottom: 0.5rem;
        }
        .info-item p {
            color: white;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
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
            <p style="margin-top: 0.5rem; opacity: 0.8;">Lot 4, Block 5 Quirino Hwy, Caloocan, 1421 Metro Manila</p>
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
            <h2 style="text-align: center; margin-bottom: 1rem;">How It Works</h2>
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
        </footer>
    </div>
</body>
</html>
