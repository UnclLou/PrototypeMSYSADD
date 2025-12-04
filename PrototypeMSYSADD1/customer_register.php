<?php
session_start();

$error_message = '';
$success_message = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
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
            
            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT id FROM customers WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = 'Username or email already exists.';
            } else {
                // Hash password and insert new customer
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO customers (username, email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $email, $hashed_password, $first_name, $last_name, $phone);
                
                if ($stmt->execute()) {
                    $success_message = 'Registration successful! You can now log in.';
                } else {
                    $error_message = 'Registration failed. Please try again.';
                }
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
    <title>Register - Let's Meet Cafe</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            max-width: 500px;
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
                <p>Create your account to start ordering online</p>
            </div>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Create Account</button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="customer_login.php">Sign in here</a></p>
                <p><a href="online_home.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
