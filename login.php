<?php
// Start PHP session to manage user login state
session_start();

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: client/index.php');
    exit();
}

// Initialize variables for error messages and success state
$error = '';
$success = false;

// Handle form submission when POST request is made
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Connect to MySQL database using PDO
        $pdo = new PDO("mysql:host=localhost;dbname=fabulous_finds", "root", "");
        
        // Prepare SQL statement to find user by email
        $stmt = $pdo->prepare("SELECT UserID, Name, Password, Email FROM user WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['Password'])) {
            // Check if user is admin (admin has special login portal)
            if ($user['Email'] == 'admin@fabulousfinds.com') {
                $error = 'Admin must login through the admin portal.';
            } else {
                // Login successful for regular customers - set session variables
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['user_name'] = $user['Name'];
                $_SESSION['logged_in'] = true;
            
                $success = true; // Set success flag to show success message
            }
        } else {
            $error = 'Invalid email or password!';
        }
    } catch (Exception $e) {
        // Handle database connection or query errors
        $error = 'Login failed. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>FABULOUS FINDS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/jpg" href="assets/img/Fabulous-finds.png">
    <link rel="stylesheet" href="assets/css/login-style.css">
    <style>
        .success-message {
            display: none;
        }
        .success-message.show {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Main login container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Login header with logo and welcome message -->
            <div class="login-header">
                <div class="logo">
                    <img src="assets/img/Fabulous-finds.png" alt="Fabulous Finds Logo" width="100" height="100">
                </div>
                <h1> Sign in to Fabulous Finds </h1>
                <p> Welcome back! Please sign in to continue. </p>
            </div>

            <?php if ($success): ?>
                <!-- SUCCESS MESSAGE COMPONENT - Shows after successful login -->
                <div class="success-message show" id="successMessage">
                    <!-- Success icon with SVG checkmark -->
                    <div class="success-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <!-- Circular background -->
                            <circle cx="12" cy="12" r="12" fill="#635BFF"/>
                            <!-- Checkmark icon -->
                            <path d="M8 12l3 3 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <!-- Success heading -->
                    <h3> Welcome back! </h3>
                    <!-- Redirect notification -->
                    <p> Redirecting to your main page... </p>
                </div>

                <!-- JavaScript to redirect after 2 seconds -->
                <script>
                    setTimeout(function() {
                        window.location.href = 'client/index.php';
                    }, 2000);
                </script>
                
            <?php else: ?>
                <!-- ERROR MESSAGE - Shows when login fails -->
                <?php if ($error): ?>
                    <div class="alert alert-danger" style="color: #f56565; text-align: center; margin-bottom: 15px; padding: 10px; background: #fef5f5; border-radius: 5px; border: 1px solid #f56565;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- LOGIN FORM - Main form for user authentication -->
                <form class="login-form" id="loginForm" method="POST" action="" novalidate>
                    <!-- Email input field with floating label -->
                    <div class="input-group">
                        <input type="email" id="email" name="email" required autocomplete="email" placeholder=" ">
                        <label for="email"> Email address </label>
                        <span class="input-border"></span>
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <!-- Password input field with floating label -->
                    <div class="input-group">
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder=" ">
                        <label for="password"> Password </label>
                        <span class="input-border"></span>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <!-- Form options: Remember me and Forgot password -->
                    <div class="form-options">
                        <label for="checkbox-container">
                            <input type="checkbox" id="remember" name="remember">
                            remember me
                        </label>
                        <a href="#" class="forgot-link"> Forgot password? </a>
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="submit-btn">
                        <span class="btn-text"> Sign in </span>
                    </button>
                </form>

                <!-- Divider for social login options -->
                <div class="divider">
                    <span> Or continue with </span>
                </div>

                <!-- Social login buttons -->
                <div class="social-buttons">
                    <button type="button" class="social-btn">
                        <img src="assets/img/google-icon.png" alt="Google" width="16" height="16">
                        Google
                    </button>
                        
                    <button type="button" class="social-btn">
                        <img src="assets/img/facebook-icon.png" alt="Facebook" width="16" height="16">
                        Facebook
                    </button>
                </div>

                <!-- Sign up link for new users -->
                <div class="signup-link">
                    <span> Don't have an account? </span>
                    <a href="register.php"> Sign up </a>
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

                // FLOATING LABELS FUNCTIONALITY
                // Adds interactive floating labels to input fields
                const inputs = document.querySelectorAll('.input-group input');
                inputs.forEach(input => {
                    // Add focused class when input is focused
                    input.addEventListener('focus', function() {
                        this.parentElement.classList.add('focused');
                    });
                    
                    // Remove focused class when input loses focus
                    input.addEventListener('blur', function() {
                        this.parentElement.classList.remove('focused');
                        // Keep has-value class if input has content
                        if (this.value) {
                            this.parentElement.classList.add('has-value');
                        }
                    });

                    // Check on page load if inputs have values
                    if (input.value) {
                        input.parentElement.classList.add('has-value');
                    }
                });

                // FORM SUBMISSION HANDLER
                // Validates form and shows loading state
                form.addEventListener('submit', function(e) {
                    let isValid = true;

                    // Email validation - check if email exists and contains @
                    if (!emailInput.value || !emailInput.value.includes('@')) {
                        showError(emailInput, 'Please enter a valid email');
                        isValid = false;
                    }

                    // Password validation - check if password is provided
                    if (!passwordInput.value) {
                        showError(passwordInput, 'Password is required');
                        isValid = false;
                    }

                    // Prevent form submission if validation fails
                    if (!isValid) {
                        e.preventDefault();
                        return;
                    }

                    // Show loading animation on submit button
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                    }
                });

                // FUNCTION TO DISPLAY ERROR MESSAGES
                function showError(input, message) {
                    const inputGroup = input.closest('.input-group');
                    const errorElement = inputGroup.querySelector('.error-message');
                    
                    // Add error styling and show error message
                    inputGroup.classList.add('error');
                    errorElement.textContent = message;
                    errorElement.classList.add('show');
                }

                // CLEAR ERRORS ON INPUT
                // Remove error states when user starts typing
                emailInput.addEventListener('input', () => clearError(emailInput));
                passwordInput.addEventListener('input', () => clearError(passwordInput));

                function clearError(input) {
                    const inputGroup = input.closest('.input-group');
                    const errorElement = inputGroup.querySelector('.error-message');
                    
                    // Remove error styling and hide error message
                    inputGroup.classList.remove('error');
                    errorElement.classList.remove('show');
                }

                // SOCIAL BUTTONS HANDLERS
                // Placeholder for social login functionality
                document.querySelectorAll('.social-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        alert('Social login would be implemented here');
                    });
                });
            }

            // Console log for debugging - confirms JavaScript loaded successfully
            console.log('✅ Login form JavaScript loaded!');
        });
    </script>

</body>
</html>