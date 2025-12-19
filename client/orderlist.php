<?php
// Start session to access user data
session_start();

// Database connection details
$host = "localhost";
$user = "root";
$password = "";
$database = "fabulous_finds";

// Connect to MySQL database
$conn = mysqli_connect($host, $user, $password, $database);

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get the logged-in user ID from session
$user_id = $_SESSION['user_id'];

// SQL query to fetch user's orders with joins
$sql = "
    SELECT 
        o.OrderID,
        o.Status,
        p.ProductName,
        p.Price,
        p.image,
        od.Quantity,
        (p.Price * od.Quantity) as ItemTotal,
        u.Address as shipping_address,
        u.ContactNo
    FROM orders o
    JOIN orderdetails od ON o.OrderID = od.OrderID
    JOIN product p ON od.ProductID = p.ProductID
    JOIN user u ON o.UserID = u.UserID
    WHERE o.UserID = $user_id
    ORDER BY o.OrderID DESC 
";

// Execute the query
$result = mysqli_query($conn, $sql);

// Group orders by OrderID
$orders = [];
$orderTotals = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orderId = $row['OrderID'];
    
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'Status' => $row['Status'],
            'shipping_address' => $row['shipping_address'],
            'ContactNo' => $row['ContactNo'],
            'items' => []
        ];
        $orderTotals[$orderId] = 0;
    }
    
    $orders[$orderId]['items'][] = [
        'ProductName' => $row['ProductName'],
        'Price' => $row['Price'],
        'image' => $row['image'],
        'Quantity' => $row['Quantity'],
        'ItemTotal' => $row['ItemTotal']
    ];
    
    $orderTotals[$orderId] += $row['ItemTotal'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
    <link rel="stylesheet" href="../assets/css/orderlist.css">
    <style>
        .order-group {
            background: #fff;
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .order-group-header {
            background: var(--color-light);
            padding: 10px 15px;
            border-bottom: 2px solid var(--color-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-id {
            font-weight: bold;
            color: var(--color-primary);
        }
        
        .order-date {
            color: var(--color-dark-variant);
            font-size: 0.9rem;
        }
        
        tr:not(:last-child) {
            border-bottom: 1px solid var(--color-info-light);
        }
        
        .status-pending {
            color: var(--color-warning) !important;
        }
        
        .status-cancelled {
            color: var(--color-danger) !important;
        }
        
        .status-completed {
            color: var(--color-success) !important;
        }
        
        .status-delivered {
            color: var(--color-primary) !important;
        }
    </style>
</head>

<body>

    <!-- Site header with navigation -->
    <header>
        <div class="top-header">
            <div class="logo">Fabulous Finds</div>

            <!-- Search bar -->
            <div class="search-bar">
                <span class="material-symbols-outlined"> search </span>
                <input type="text" placeholder="Search for items...">
            </div>

            <!-- User action links -->
            <div class="userlinks">
                <!-- Shopping cart link -->
                <a href="cart.php">
                    <span class="material-symbols-outlined"> shopping_cart </span>
                </a>

                <!-- Order history link (current page) -->
                <a href="orderlist.php">
                    <span class="material-symbols-outlined"> local_shipping </span>
                </a>

                <!-- Profile dropdown menu -->
                <div class="profile-dropdown">
                    <button id="profile-btn">
                        <span class="material-symbols-outlined"> account_circle </span>
                    </button>
                    <div class="dropdown-menu" id="dropdown-menu">
                        <a href="#"> Edit Profile </a>
                        <a href="#"> Add Address </a>
                        <a href="#"> Settings </a>
                        <a href="../logout.php"> Logout </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main navigation menu -->
        <nav class="menu">
            <a href="index.php"> Home </a>
            <a href="shop.php"> Product </a>
            <a href="contact.php"> Contact </a>
        </nav>
    </header>

    <!-- Main content container -->
    <div class="container">
        <h2>Your Orders</h2>

        <!-- Check if there are orders -->
        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 40px;">
                <p style="color: var(--color-dark-variant); font-size: 1.1rem;">
                    You haven't placed any orders yet.
                </p>
                <a href="shop.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; 
                   background: var(--color-primary); color: white; text-decoration: none; border-radius: 5px;">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Loop through each order group -->
            <?php foreach ($orders as $orderId => $orderData): ?>
                <div class="order-group">
                    <!-- Order header -->
                    <div class="order-group-header">
                        <div>
                            <span class="order-id">Order #<?php echo $orderId; ?></span>
                            <span class="order-date">
                            </span>
                        </div>
                        <div>
                            <span class="status-<?php echo strtolower($orderData['Status']); ?>">
                                <?php echo $orderData['Status']; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Orders table for this order -->
                    <table style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th> PRODUCT </th>
                                <th> PRICE </th>
                                <th> QUANTITY </th>
                                <th> ITEM TOTAL </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderData['items'] as $item): ?>
                                <tr>
                                    <!-- Product information -->
                                    <td>
                                        <div class="product-box">
                                            <img src="../assets/img/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['ProductName']); ?>">
                                            <div>
                                                <b><?php echo htmlspecialchars($item['ProductName']); ?></b>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Unit price -->
                                    <td>₱<?php echo number_format($item['Price'], 2); ?></td>
                                    
                                    <!-- Quantity purchased -->
                                    <td><?php echo $item['Quantity']; ?></td>
                                    
                                    <!-- Item total -->
                                    <td>₱<?php echo number_format($item['ItemTotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <!-- Order summary row -->
                            <tr style="background: var(--color-light); font-weight: bold;">
                                <td colspan="3" style="text-align: right;">Order Total:</td>
                                <td>₱<?php echo number_format($orderTotals[$orderId], 2); ?></td>
                            </tr>
                            
                            <!-- Order info and actions -->
                            <tr>
                                <td colspan="4" style="padding: 15px; border-top: 2px solid var(--color-info-light);">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div><strong>Shipping Address:</strong> <?php echo htmlspecialchars($orderData['shipping_address']); ?></div>
                                            <div><strong>Contact No:</strong> <?php echo htmlspecialchars($orderData['ContactNo']); ?></div>
                                        </div>
                                        <div>
                                            <?php if ($orderData['Status'] == 'Pending'): ?>
                                                <!-- Cancel order button -->
                                                <a href="cancel.php?id=<?php echo $orderId; ?>" 
                                                   onclick="return confirm('Are you sure you want to cancel this order?');"
                                                   style="text-decoration: none; margin-right: 10px;">
                                                    <button class="cancel-btn"> Cancel Order </button>
                                                </a>
                                                <!-- Mark as received button -->
                                                <a href="complete.php?id=<?php echo $orderId; ?>" 
                                                   style="text-decoration: none;">
                                                    <button class="order-received-btn"> Order Received </button>
                                                </a>
                                            <?php elseif ($orderData['Status'] == 'Cancelled' || $orderData['Status'] == 'Completed'): ?>
                                                <!-- View Invoice link -->
                                                <a href="view_invoice.php?order_id=<?php echo $orderId; ?>" 
                                                   style="text-decoration: none;">
                                                    <button class="cancel-btn" style="background: var(--color-primary);"> 
                                                        View Invoice 
                                                    </button>
                                                </a>
                                            <?php else: ?>
                                                <span class="status-<?php echo strtolower($orderData['Status']); ?>">
                                                    <?php echo $orderData['Status']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- JavaScript for interactive features -->
    <script>
        // Profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const profileBtn = document.getElementById('profile-btn');
            const dropdownMenu = document.getElementById('dropdown-menu');

            if (profileBtn && dropdownMenu) {
                // Toggle dropdown on button click
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                // Close dropdown when clicking elsewhere
                document.addEventListener('click', function() {
                    dropdownMenu.classList.remove('show');
                });
            }
        });
    </script>

</body>

</html>
