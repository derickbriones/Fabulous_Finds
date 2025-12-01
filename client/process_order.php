<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "fabulous_finds";

$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in. Please log in first.");
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Get form data
$totalAmount = mysqli_real_escape_string($conn, $_POST['total'] ?? '');
$paymentMethod = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
$shippingAddress = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

$userID = $_SESSION['user_id'];
$orderDate = date("Y-m-d H:i:s");

$orderProcessed = false;
$orderID = null;
$error = null;

// Start transaction
mysqli_begin_transaction($conn);

try {
    // 1️⃣ Get seller ID from the first product in cart
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

    // 2️⃣ Verify user exists in database
    $checkUser = mysqli_query($conn, "SELECT UserID FROM user WHERE UserID = '$userID'");
    if (mysqli_num_rows($checkUser) == 0) {
        throw new Exception("User ID $userID does not exist in database");
    }

    // 3️⃣ Check stock availability FIRST
    foreach ($_SESSION['cart'] as $productID => $qty) {
        $productID = mysqli_real_escape_string($conn, $productID);
        $qty = mysqli_real_escape_string($conn, $qty);
        
        // Check current stock
        $checkStock = mysqli_query($conn, "SELECT StockQuantity, ProductName FROM product WHERE ProductID = '$productID'");
        if (!$checkStock) {
            throw new Exception("Error checking stock for product ID: $productID");
        }
        
        $stockRow = mysqli_fetch_assoc($checkStock);
        if (!$stockRow) {
            throw new Exception("Product ID $productID does not exist");
        }
        
        $currentStock = $stockRow['StockQuantity'];
        $productName = $stockRow['ProductName'];
        
        // Check if enough stock is available
        if ($currentStock < $qty) {
            throw new Exception("Insufficient stock for $productName. Available: $currentStock, Requested: $qty");
        }
    }

    // 4️⃣ Create Order
    $insertOrder = "INSERT INTO orders (UserID, SellerID, OrderDate, Status) 
                    VALUES ('$userID', '$sellerID', '$orderDate', 'Pending')";
    
    if (!mysqli_query($conn, $insertOrder)) {
        throw new Exception("Error creating order: " . mysqli_error($conn));
    }

    // Get the generated orderID
    $orderID = mysqli_insert_id($conn);

    if ($orderID == 0) {
        throw new Exception("Failed to get order ID");
    }

    // 5️⃣ Insert all order items
    foreach ($_SESSION['cart'] as $productID => $qty) {
        $productID = mysqli_real_escape_string($conn, $productID);
        $qty = mysqli_real_escape_string($conn, $qty);
        
        $insertOrderDetails = mysqli_query($conn, 
            "INSERT INTO orderdetails (OrderID, ProductID, Quantity) 
             VALUES ('$orderID', '$productID', '$qty')");
        
        if (!$insertOrderDetails) {
            throw new Exception("Error adding order details: " . mysqli_error($conn));
        }
    }

// 6️⃣ ✅ STOCK DEDUCTION - DEDUCT STOCK HERE (THIS IS WHAT'S MISSING!)
foreach ($_SESSION['cart'] as $productID => $qty) {
    $productID = mysqli_real_escape_string($conn, $productID);
    $qty = mysqli_real_escape_string($conn, $qty);
    
    // Deduct from database
    $updateStock = mysqli_query($conn, "UPDATE product SET StockQuantity = StockQuantity - $qty WHERE ProductID = '$productID'");
    
    if (!$updateStock) {
        throw new Exception("Error updating stock for product ID: $productID");
    }
    
    error_log("Stock deducted - ProductID: $productID, Quantity: $qty");
}

    // 7️⃣ Insert payment record
    $insertPayment = "INSERT INTO payment (OrderID, Amount, PaymentMethod, PaymentDate)
                      VALUES ('$orderID', '$totalAmount', '$paymentMethod', '$orderDate')";
    
    if (!mysqli_query($conn, $insertPayment)) {
        throw new Exception("Error processing payment: " . mysqli_error($conn));
    }

    // 8️⃣ Commit transaction
    mysqli_commit($conn);
    
    // 9️⃣ Clear cart from session
    unset($_SESSION['cart']);
    
    $orderProcessed = true;

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    $error = $e->getMessage();
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
    <link rel="icon" type="image/png" href="assets/img/Fabulous-finds.png" />
    <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        .result-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: bounce 0.6s ease-out;
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            background: #f44336;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: bounce 0.6s ease-out;
        }
        
        .success-icon i, .error-icon i {
            color: white;
            font-size: 40px;
        }
        
        .success-title {
            color: #2E7D32;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .error-title {
            color: #c62828;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .result-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .order-id {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #4CAF50;
        }
        
        .error-message {
            background: #ffebee;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #f44336;
            color: #c62828;
        }
        
        .order-id strong {
            color: #2E7D32;
        }
        
        .countdown {
            color: #666;
            font-size: 0.9rem;
            margin-top: 20px;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-retry {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
            margin-left: 10px;
        }
        
        .btn-home:hover, .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            color: white;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    </style>
</head>
<body>
    <div class="result-container">
        <?php if ($orderProcessed): ?>
            <!-- SUCCESS MESSAGE -->
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
            
            <div class="countdown">
                Redirecting to home in <span id="countdown">5</span> seconds...
            </div>

            <script>
                // Countdown timer for automatic redirect
                let countdown = 5;
                const countdownElement = document.getElementById('countdown');
                const countdownInterval = setInterval(function() {
                    countdown--;
                    countdownElement.textContent = countdown;
                    
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = 'index.php';
                    }
                }, 1000);
            </script>

        <?php else: ?>
            <!-- ERROR MESSAGE -->
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
            
            <a href="checkout.php" class="btn-retry">
                <i class="fas fa-redo me-2"></i>Try Again
            </a>
            <a href="index.php" class="btn-home">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
        <?php endif; ?>
    </div>

    <script>
        // Redirect immediately if user clicks anywhere (for success only)
        <?php if ($orderProcessed): ?>
        document.addEventListener('click', function() {
            window.location.href = 'home.php';
        });
        <?php endif; ?>
    </script>
</body>
</html>