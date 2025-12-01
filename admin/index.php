<?php
// Start session to manage admin login state
session_start();

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: admin-dashboard.php');
    exit();
}

// Initialize variables for error messages and success state
$error = '';
$success = false;

// List of authorized admin email addresses
$admin_emails = [
    'admin@fabulousfinds.com',
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Connect to database
        $pdo = new PDO("mysql:host=localhost;dbname=fabulous_finds", "root", "");
        
        // Find user by email
        $stmt = $pdo->prepare("SELECT UserID, Name, Password FROM user WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Check if user exists AND is an admin
        if ($user && password_verify($password, $user['Password'])) {
            if (in_array($email, $admin_emails)) {
                // Admin login successful - set session variables
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['user_name'] = $user['Name'];
                $_SESSION['logged_in'] = true;
                $_SESSION['is_admin'] = true; // Admin flag
                
                $success = true;
            } else {
                $error = 'Access denied. Admin privileges required!';
            }
        } else {
            $error = 'Invalid email or password!';
        }
        
    } catch (Exception $e) {
        $error = 'Login failed. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title> FABULOUS FINDS ADMIN DASHBOARD </title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/jpg" href="../assets/img/Fabulous-finds.png">
    <link rel="stylesheet" href="../assets/css/login-style.css">
    <style>
        /* Success message styling */
        .success-message {
            display: none;
        }
        .success-message.show {
            display: block;
        }
        
        /* Admin-only notice styling */
        .admin-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
        
        /* Error message styling */
        .error-message-global {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- Main login container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Login header with logo -->
            <div class="login-header">
                <div class="logo">
                    <img src="../assets/img/Fabulous-finds.png" alt="Fabulous Finds Logo" width="100" height="100">
                </div>
                <h1> Admin Dashboard </h1>
                <p> Administrative access required </p>
            </div>

            <!-- Success page after login -->
            <?php if ($success): ?>
                <div class="success-message show" id="successMessage">
                    <div class="success-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="12" fill="#635BFF"/>
                            <path d="M8 12l3 3 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3>Admin Access Granted!</h3>
                    <p>Redirecting to admin dashboard...</p>
                </div>
                
                <!-- Auto-redirect to admin dashboard -->
                <script>
                    setTimeout(function() {
                        window.location.href = 'admin-dashboard.php';
                    }, 2000);
                </script>
                
            <!-- Login form (shown when not logged in) -->
            <?php else: ?>
                <!-- Admin access warning -->
                <?php if ($error && strpos($error, 'Access denied') !== false): ?>
                    <div class="admin-notice">
                        ⚠️ Admin access only - Regular users cannot login here
                    </div>
                <?php endif; ?>

                <!-- Regular error messages -->
                <?php if ($error && strpos($error, 'Access denied') === false): ?>
                    <div class="error-message-global">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Admin login form -->
                <form class="login-form" id="loginForm" method="POST" action="" novalidate>
                    <!-- Email input field -->
                    <div class="input-group">
                        <input type="email" id="email" name="email" required autocomplete="email" placeholder=" " value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <label for="email"> Admin Email </label>
                        <span class="input-border"></span>
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <!-- Password input field -->
                    <div class="input-group">
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder=" ">
                        <label for="password"> Password </label>
                        <span class="input-border"></span>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <!-- Remember me and forgot password -->
                    <div class="form-options">
                        <label for="checkbox-container">
                            <input type="checkbox" id="remember" name="remember">
                            remember me
                        </label>
                        <a href="#" class="forgot-link"> Forgot password? </a>
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="submit-btn">
                        <span class="btn-text"> Login </span>
                    </button>
                </form>

                <!-- Social login divider -->
                <div class="divider">
                    <span> Or continue with </span>
                </div>

                <!-- Social login buttons -->
                <div class="social-buttons">
                    <button type="button" class="social-btn">
                        <img src="../assets/img/google-icon.png" alt="Google" width="16" height="16">
                        Google
                    </button>
                        
                    <button type="button" class="social-btn">
                        <img src="../assets/img/facebook-icon.png" alt="Facebook" width="16" height="16">
                        Facebook
                    </button>
                </div>

                <!-- Admin access contact link -->
                <div class="signup-link">
                    <span> Need admin access? </span>
                    <a href=""> Contact administrator </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript for form validation and interactivity -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            if (form) {
                const submitBtn = document.querySelector('.submit-btn');
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');

                // Floating labels functionality
                const inputs = document.querySelectorAll('.input-group input');
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.parentElement.classList.add('focused');
                    });
                    
                    input.addEventListener('blur', function() {
                        this.parentElement.classList.remove('focused');
                        if (this.value) {
                            this.parentElement.classList.add('has-value');
                        }
                    });

                    // Check existing values on page load
                    if (input.value) {
                        input.parentElement.classList.add('has-value');
                    }
                });

                // Form submission validation
                form.addEventListener('submit', function(e) {
                    let isValid = true;

                    // Email validation
                    if (!emailInput.value || !emailInput.value.includes('@')) {
                        showError(emailInput, 'Please enter a valid email');
                        isValid = false;
                    }

                    // Password validation
                    if (!passwordInput.value) {
                        showError(passwordInput, 'Password is required');
                        isValid = false;
                    }

                    // Prevent submission if validation fails
                    if (!isValid) {
                        e.preventDefault();
                        return;
                    }

                    // Show loading animation
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                    }
                });

                // Display error messages
                function showError(input, message) {
                    const inputGroup = input.closest('.input-group');
                    const errorElement = inputGroup.querySelector('.error-message');
                    
                    inputGroup.classList.add('error');
                    errorElement.textContent = message;
                    errorElement.classList.add('show');
                }

                // Clear error messages on input
                emailInput.addEventListener('input', () => clearError(emailInput));
                passwordInput.addEventListener('input', () => clearError(passwordInput));

                function clearError(input) {
                    const inputGroup = input.closest('.input-group');
                    const errorElement = inputGroup.querySelector('.error-message');
                    
                    inputGroup.classList.remove('error');
                    errorElement.classList.remove('show');
                }

                // Social buttons (placeholder functionality)
                document.querySelectorAll('.social-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        alert('Social login would be implemented here');
                    });
                });
            }

            // Debug log
            console.log('✅ Login form JavaScript loaded!');
        });
    </script>

</body>
</html>