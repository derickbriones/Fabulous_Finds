<?php
// Start session to access cart and user data
session_start();

// Database connection details
$host = "localhost";
$user = "root";
$password = "";
$database = "fabulous_finds";

// Connect to MySQL database
$conn = mysqli_connect($host, $user, $password, $database);

// Check if connection succeeded
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Only allow POST requests (form submissions)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    die("Error: User not logged in. Please log in first.");
}

// Check if cart has items
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Sanitize form inputs
$totalAmount = mysqli_real_escape_string($conn, $_POST['total'] ?? '');
$paymentMethod = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
$shippingAddress = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

// Get user ID from session and current date/time
$userID = $_SESSION['userID'];
$orderDate = date("Y-m-d H:i:s");

// Initialize order status variables
$orderProcessed = false;
$orderID = null;
$error = null;

// Start database transaction for data consistency
mysqli_begin_transaction($conn);

try {
    // STEP 1: Get seller ID from the first product in cart
    $firstProductID = array_key_first($_SESSION['cart']);
    $getSeller = mysqli_query($conn, "SELECT SellerID FROM product WHERE ProductID = '$firstProductID'");
    
    if (!$getSeller) {
        throw new Exception("Error getting seller: " . mysqli_error($conn));
    }
    
    $sellerRow = mysqli_fetch_assoc($getSeller);
    
    if (!$sellerRow) {
        throw new Exception("No seller found for product ID: $firstProductID");
    }
    
    $sellerID = $sellerRow['SellerID'];

    // STEP 2: Verify user exists in database
    $checkUser = mysqli_query($conn, "SELECT UserID FROM user WHERE UserID = '$userID'");
    if (mysqli_num_rows($checkUser) == 0) {
        throw new Exception("User ID $userID does not exist in database");
    }

    // STEP 3: Create main order record
    $insertOrder = "INSERT INTO orders (UserID, SellerID, OrderDate, Status) 
                    VALUES ('$userID', '$sellerID', '$orderDate', 'Pending')";
    
    if (!mysqli_query($conn, $insertOrder)) {
        throw new Exception("Error creating order: " . mysqli_error($conn));
    }

    // Get the auto-generated order ID
    $orderID = mysqli_insert_id($conn);

    if ($orderID == 0) {
        throw new Exception("Failed to get order ID");
    }

    // STEP 4: Add all cart items to order details
    foreach ($_SESSION['cart'] as $productID => $qty) {
        $productID = mysqli_real_escape_string($conn, $productID);
        $qty = mysqli_real_escape_string($conn, $qty);
        
        // Verify product exists before adding
        $checkProduct = mysqli_query($conn, "SELECT ProductID FROM product WHERE ProductID = '$productID'");
        if (mysqli_num_rows($checkProduct) == 0) {
            throw new Exception("Product ID $productID does not exist");
        }
        
        // Insert item into order details table
        $insertOrderDetails = mysqli_query($conn, 
            "INSERT INTO orderdetails (OrderID, ProductID, Quantity) 
             VALUES ('$orderID', '$productID', '$qty')");
        
        if (!$insertOrderDetails) {
            throw new Exception("Error adding order details: " . mysqli_error($conn));
        }
    }

    // STEP 5: Record payment
    $insertPayment = "INSERT INTO payment (OrderID, Amount, PaymentMethod, PaymentDate)
                      VALUES ('$orderID', '$totalAmount', '$paymentMethod', '$orderDate')";
    
    if (!mysqli_query($conn, $insertPayment)) {
        throw new Exception("Error processing payment: " . mysqli_error($conn));
    }

    // STEP 6: Commit all database changes
    mysqli_commit($conn);
    
    // STEP 7: Clear cart after successful order
    unset($_SESSION['cart']);
    
    // Mark order as processed successfully
    $orderProcessed = true;

} catch (Exception $e) {
    // Rollback transaction if any step fails
    mysqli_rollback($conn);
    $error = $e->getMessage();
    // Log error for debugging
    error_log("Order processing failed: " . $error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $orderProcessed ? 'Order Successful' : 'Order Failed'; ?> - Fabulous Finds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
    <link rel="stylesheet" href="../assets/css/order-success.css">
</head>
<body>
    <div class="result-container">
        <?php if ($orderProcessed): ?>
            <!-- SUCCESS PAGE CONTENT -->
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="success-title">Order Successful!</h1>
            
            <p class="result-message">
                Thank you for your purchase! Your order has been successfully placed and is being processed.
            </p>
            
            <?php if ($orderID): ?>
            <div class="order-id">
                <strong>Order ID:</strong> #<?php echo htmlspecialchars($orderID); ?>
            </div>
            <?php endif; ?>
            
            <p class="result-message">
                You will receive an email confirmation shortly. You can track your order in your account.
            </p>
            
            <a href="index.php" class="btn-home">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
            
            <!-- Countdown timer for automatic redirect -->
            <div class="countdown">
                Redirecting to home in <span id="countdown">5</span> seconds...
            </div>

            <script>
                // 5-second countdown before auto-redirect
                let countdown = 5;
                const countdownElement = document.getElementById('countdown');
                const countdownInterval = setInterval(function() {
                    countdown--;
                    countdownElement.textContent = countdown;
                    
                    // Redirect when countdown reaches 0
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = 'index.php';
                    }
                }, 1000);

                // Allow immediate redirect on any click
                document.addEventListener('click', function() {
                    window.location.href = 'index.php';
                });
            </script>

        <?php else: ?>
            <!-- ERROR PAGE CONTENT -->
            <div class="error-icon">
                <i class="fas fa-times"></i>
            </div>
            
            <h1 class="error-title">Order Failed</h1>
            
            <p class="result-message">
                We encountered an issue while processing your order.
            </p>
            
            <?php if ($error): ?>
            <div class="error-message">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <p class="result-message">
                Please try again or contact our support team if the problem persists.
            </p>
            
            <!-- Action buttons for error page -->
            <div>
                <a href="checkout.php" class="btn-retry">
                    <i class="fas fa-redo me-2"></i>Try Again
                </a>
                <a href="index.php" class="btn-home">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>