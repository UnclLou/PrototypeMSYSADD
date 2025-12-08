<?php
session_start();

$error_message = '';
$success_message = '';
$action = $_POST['action'] ?? '';

// PHPMailer 
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// SMTP configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465); // 465 for SSL, 587 for TLS
define('SMTP_USERNAME', 'uncllou2@gmail.com');
define('SMTP_PASSWORD', 'txes gcpd uxbq thuy');
define('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_SMTPS); 
define('SMTP_FROM_EMAIL', 'no-reply@letsmeet.cafe');
define('SMTP_FROM_NAME', "Let's Meet Cafe");

// Helper to send verification code via SMTP (PHPMailer), with mail() fallback
function send_verification_code($email, $code) {
    // Try SMTP first
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        $mail->isHTML(false);
        $mail->Subject = "Let's Meet Cafe - Email Verification Code";
        $mail->Body    = "Your verification code is: {$code}\n\nIf you did not request this, please ignore.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Fallback to mail() if SMTP fails
        $subject = "Let's Meet Cafe - Email Verification Code";
        $message = "Your verification code is: {$code}\n\nIf you did not request this, please ignore.";
        $headers = "From: " . SMTP_FROM_EMAIL;
        return @mail($email, $subject, $message, $headers);
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code_input = trim($_POST['verification_code'] ?? '');

    // Resend code if requested and pending data exists
    if ($action === 'resend_code' && !empty($_SESSION['pending_registration'])) {
        $pending = $_SESSION['pending_registration'];
        $pending['code'] = (string)random_int(100000, 999999);
        $_SESSION['pending_registration'] = $pending;

        $mailSent = send_verification_code($pending['email'], $pending['code']);
        if ($mailSent) {
            $success_message = 'A new verification code has been sent to your email.';
        } else {
            $success_message = 'Email sending failed. For testing, use this code: ' . htmlspecialchars($pending['code']);
        }
    }
    // Verify code and create account
    elseif (!empty($_SESSION['pending_registration']) && $verification_code_input !== '') {
        $pending = $_SESSION['pending_registration'];

        if ($verification_code_input === $pending['code']) {
            // Proceed to create the account using pending data
            $host = 'localhost';
            $dbname = 'coffee_queue';
            $db_username = 'root';
            $db_password = '';

            try {
                $conn = new mysqli($host, $db_username, $db_password, $dbname);
                if ($conn->connect_error) {
                    throw new Exception("Connection failed: " . $conn->connect_error);
                }

                // Double-check uniqueness before insert
                $stmt = $conn->prepare("SELECT id FROM customers WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $pending['username'], $pending['email']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error_message = 'Username or email already exists.';
                } else {
                    $stmt = $conn->prepare("INSERT INTO customers (username, email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param(
                        "ssssss",
                        $pending['username'],
                        $pending['email'],
                        $pending['hashed_password'],
                        $pending['first_name'],
                        $pending['last_name'],
                        $pending['phone']
                    );

                    if ($stmt->execute()) {
                        $success_message = 'Registration successful! You can now log in.';
                        // Clear pending data
                        unset($_SESSION['pending_registration']);
                    } else {
                        $error_message = 'Registration failed. Please try again.';
                    }
                }

                $stmt->close();
                $conn->close();
            } catch (Exception $e) {
                $error_message = 'Database error. Please try again.';
            }
        } else {
            $error_message = 'Invalid verification code. Please try again.';
        }
    }
    // Fresh registration attempt (send code)
    else {
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
                    // Generate verification code and store pending data in session
                    $verification_code = (string)random_int(100000, 999999);
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $_SESSION['pending_registration'] = [
                        'username' => $username,
                        'email' => $email,
                        'hashed_password' => $hashed_password,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'phone' => $phone,
                        'code' => $verification_code
                    ];

                    $mailSent = send_verification_code($email, $verification_code);
                    if ($mailSent) {
                        $success_message = 'A verification code has been sent to your email. Please enter it below to complete registration.';
                    } else {
                        // Fallback: show code if email sending fails (for testing/localhost)
                        $success_message = 'Email sending failed. For testing, use this code: ' . htmlspecialchars($verification_code);
                    }
                }
                
                $stmt->close();
                $conn->close();
            } catch (Exception $e) {
                $error_message = 'Database error. Please try again.';
            }
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
                <input type="hidden" name="action" value="">
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

                <?php if (!empty($_SESSION['pending_registration'])): ?>
                    <div class="form-group">
                        <label for="verification_code">Email Verification Code <span class="required">*</span></label>
                        <input type="text" id="verification_code" name="verification_code" pattern="\d{6}" maxlength="6" placeholder="Enter the 6-digit code sent to your email" required>
                    </div>
                <?php endif; ?>

                <?php if (empty($_SESSION['pending_registration'])): ?>
                    <button type="submit" class="btn" style="width: 100%;" onclick="this.form.action.value='send_code';">Send Verification Code</button>
                <?php else: ?>
                    <button type="submit" class="btn" style="width: 100%; margin-bottom: 0.75rem;" onclick="this.form.action.value='verify';">Verify &amp; Create Account</button>
                    <button type="submit" class="btn btn-outline" style="width: 100%;" onclick="this.form.action.value='resend_code'; return true;">Resend Code</button>
                <?php endif; ?>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="customer_login.php">Sign in here</a></p>
                <p><a href="online_home.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
