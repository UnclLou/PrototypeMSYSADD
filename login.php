<?php
session_start();
require_once 'config/database.php'; // Include database connection

// Simple hardcoded credentials for demonstration
// $valid_username = 'staff';
// $valid_password = 'password'; // *** In a real application, use secure password hashing! ***

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Prepare a SQL query to fetch the user
        $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verify user exists and password is correct
        // *** In a real application, use password_verify() for hashed passwords ***
        if ($user && $password === $user['password']) {
             // Authentication successful
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username']; // Store username in session
            $_SESSION['user_role'] = $user['role']; // Store user role in session
            
            // Redirect based on user role to their main feature page
            $userRole = strtolower($user['role']);
            switch ($userRole) {
                case 'admin':
                    // Admin can access multiple features, redirect to payment process (most common)
                    header('Location: payment_process.php');
                    break;
                case 'cashier':
                    // Cashier's main feature is payment processing
                    header('Location: payment_process.php');
                    break;
                case 'staff':
                    // Staff's main feature is queue management
                    header('Location: queue.php');
                    break;
                default:
                    // Fallback to dashboard for unknown roles
                    header('Location: index.php');
                    break;
            }
            exit;
        } else {
            // Authentication failed
            $error_message = 'Invalid username or password.';
        }
    }
}

// If user is already logged in, redirect based on role
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $userRole = isset($_SESSION['user_role']) ? strtolower($_SESSION['user_role']) : '';
    switch ($userRole) {
        case 'admin':
            header('Location: payment_process.php');
            break;
        case 'cashier':
            header('Location: payment_process.php');
            break;
        case 'staff':
            header('Location: queue.php');
            break;
        default:
            header('Location: index.php');
            break;
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - CoffeeQueue</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--background-color); /* Use your theme's background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: var(--card-bg); /* Use your theme's card background */
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg); /* Use your theme's shadow */
            width: 90%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            color: var(--primary-color); /* Use your theme's primary color */
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color); /* Use your theme's text color */
            font-weight: 500;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color); /* Use your theme's border color */
            border-radius: var(--border-radius);
            font-size: 1rem;
        }
         .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
             outline: none;
             border-color: var(--primary-color); /* Use your theme's primary color */
             box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.2); /* Adjusted for coffee theme */
        }
        .btn-login {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary-color); /* Use your theme's primary color */
            color: var(--accent-color); /* Use your theme's accent color */
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-login:hover {
            background-color: var(--hover-color); /* Use your theme's hover color */
        }
        .error-message {
            color: var(--error-color); /* Use your theme's error color */
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>User Login</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html> 