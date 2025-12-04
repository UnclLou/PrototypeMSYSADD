<?php
session_start();

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Database connection
        $host = 'localhost';
        $dbname = 'coffee_queue';
        $db_username = 'root';
        $db_password = '';
        
        try {
            $conn = new mysqli($host, $db_username, $db_password, $dbname);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            // Check customer credentials
            $stmt = $conn->prepare("SELECT id, username, password, first_name, last_name, email FROM customers WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $customer = $result->fetch_assoc();
                
                if (password_verify($password, $customer['password'])) {
                    // Login successful
                    $_SESSION['customer_loggedin'] = true;
                    $_SESSION['customer_id'] = $customer['id'];
                    $_SESSION['customer_username'] = $customer['username'];
                    $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
                    $_SESSION['customer_email'] = $customer['email'];
                    
                    // Redirect to online ordering
                    header('Location: online_ordering.php');
                    exit();
                } else {
                    $error_message = 'Invalid username or password.';
                }
            } else {
                $error_message = 'Invalid username or password.';
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error_message = 'Database error. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Let's Meet Cafe</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .auth-header p {
            color: var(--text-light);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
        .required {
            color: var(--danger);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Let's Meet Cafe</h1>
                <p>Sign in to your account</p>
            </div>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Sign In</button>
            </form>

            <div class="auth-links">
                <p>Don't have an account? <a href="customer_register.php">Create one here</a></p>
                <p><a href="online_home.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
