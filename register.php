<?php
// Start or resume PHP session
session_start();

// Variables for messages and form data
$error = '';
$success = '';
$name = $email = $address = '';

// Process form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data from POST request
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validate user input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($address)) {
        $error = 'All fields are required!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        try {
            // Database connection
            $pdo = new PDO("mysql:host=localhost;dbname=fabulous_finds", "root", "");
            
            // Check if email is already registered
            $stmt = $pdo->prepare("SELECT UserID FROM user WHERE Email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email already exists!';
            } else {
                // Secure password storage with hashing
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user into database
                $stmt = $pdo->prepare("INSERT INTO user (Name, Email, Password, Address) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $address]);
                
                // Success message
                $success = 'Registration successful! You can now login.';
                
                // Reset form fields after successful registration
                if ($success) {
                    $name = $email = $address = '';
                }
            }
            
        } catch (Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title> FABULOUS FINDS - Register </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/jpg" href="assets/img/Fabulous-finds.png">
    <link rel="stylesheet" href="assets/css/register-style.css">
</head>
<body>

    <!-- Registration page container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Page header with logo -->
            <div class="login-header">
                <div class="logo">
                    <img src="assets/img/Fabulous-finds.png" alt="Fabulous Finds Logo" width="100" height="100">
                </div>
                <h1> Join Fabulous Finds </h1>
                <p> Create your account to get started </p>
            </div>

            <!-- Display error messages from PHP validation -->
            <?php if ($error): ?>
                <div class="php-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Display success message after registration -->
            <?php if ($success): ?>
                <div class="php-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Registration form -->
            <form class="login-form" id="registerForm" method="POST" action="" novalidate>
                <!-- Name input -->
                <div class="input-group">
                    <input type="text" id="name" name="name" required placeholder=" " value="<?php echo htmlspecialchars($name); ?>">
                    <label for="name"> Full Name </label>
                    <span class="input-border"></span>
                </div>

                <!-- Email input -->
                <div class="input-group">
                    <input type="email" id="email" name="email" required placeholder=" " value="<?php echo htmlspecialchars($email); ?>">
                    <label for="email"> Email Address </label>
                    <span class="input-border"></span>
                </div>

                <!-- Password input -->
                <div class="input-group">
                    <input type="password" id="password" name="password" required placeholder=" ">
                    <label for="password"> Password </label>
                    <span class="input-border"></span>
                </div>

                <!-- Confirm password input -->
                <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder=" ">
                    <label for="confirm_password"> Confirm Password </label>
                    <span class="input-border"></span>
                </div>

                <!-- Address textarea -->
                <div class="input-group">
                    <textarea id="address" name="address" required placeholder=" "><?php echo htmlspecialchars($address); ?></textarea>
                    <label for="address"> Address </label>
                    <span class="input-border"></span>
                </div>

                <!-- Submit button with loading spinner -->
                <button type="submit" class="submit-btn">
                    <span class="btn-text"> Create Account </span>
                    <div class="btn-loader">
                        <!-- Loading spinner SVG -->
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="2" opacity="0.25"/>
                            <path d="M16 9a7 7 0 01-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <animateTransform attributeName="transform" type="rotate" dur="1s" values="0 9 9;360 9 9" repeatCount="indefinite"/>
                            </path>
                        </svg>
                    </div>
                </button>
            </form>

            <!-- Link to login page -->
            <div class="signup-link">
                <span> Already have an account? </span>
                <a href="login.php"> Sign in </a>
            </div>
        </div>
    </div>

    <!-- JavaScript for form interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const submitBtn = document.querySelector('.submit-btn');

            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('.input-group input, .input-group textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });

            // Show loading spinner when form is submitted
            form.addEventListener('submit', function(e) {
                submitBtn.classList.add('loading');
                // Form submission continues to PHP
            });
        });
    </script>

</body>
</html>