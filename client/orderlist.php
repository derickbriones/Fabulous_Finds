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
        (p.Price * od.Quantity) as Total,
        u.Address as shipping_address
    FROM orders o
    JOIN orderdetails od ON o.OrderID = od.OrderID
    JOIN product p ON od.ProductID = p.ProductID
    JOIN user u ON o.UserID = u.UserID
    WHERE o.UserID = $user_id
";

// Execute the query
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
    <link rel="stylesheet" href="../assets/css/orderlist.css">
</head>
<style>
    /* Header styling */
    header {
        background: #fff;
        border-bottom: 1px solid #ddd;
        padding: 15px 5%;
    }

    .top-header {
        display: flex;
        align-items: center;
        gap: 20px;
    }
</style>

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
        <h2>Your Orders are Below</h2>

        <!-- Orders table -->
        <table>
            <tr>
                <th> PRODUCT </th>
                <th> PRICE </th>
                <th> QUANTITY </th>
                <th> TOTAL </th>
                <th> SHIPPING ADDRESS </th>
                <th> STATUS </th>
                <th> ACTION </th>
            </tr>

            <!-- Loop through each order from database -->
            <?php while ($order = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <!-- Product information -->
                    <td>
                        <div class="product-box">
                            <img src="../assets/img/<?php echo $order['image']; ?>" alt="Product">
                            <div>
                                <b><?php echo $order['ProductName']; ?></b><br><br>
                            </div>
                        </div>
                    </td>

                    <!-- Unit price -->
                    <td>₱<?php echo $order['Price']; ?></td>
                    
                    <!-- Quantity purchased -->
                    <td><?php echo $order['Quantity']; ?></td>
                    
                    <!-- Total cost (price × quantity) -->
                    <td>₱<?php echo $order['Total']; ?></td>
                    
                    <!-- Shipping address -->
                    <td><?php echo $order['shipping_address']; ?></td>
                    
                    <!-- Order status with color coding -->
                    <td>
                        <?php if ($order['Status'] == 'Pending'): ?>
                            <span style="color: #6b4b3e;"><?php echo $order['Status']; ?></span>
                        <?php elseif ($order['Status'] == 'Cancelled'): ?>
                            <span style="color: #a8a8a8;"><?php echo $order['Status']; ?></span>
                        <?php else: ?>
                            <span style="color: #4caf50;"><?php echo $order['Status']; ?></span>
                        <?php endif; ?>
                    </td>

                    <!-- Action buttons based on order status -->
                    <td>
                        <?php if ($order['Status'] == 'Pending'): ?>
                            <!-- Cancel order button -->
                            <a href="cancel.php?id=<?php echo $order['OrderID']; ?>" style="text-decoration: none;">
                                <button class="cancel-btn"> Cancel Order </button>
                            </a>
                            <a> | </a>
                            <!-- Mark as received button -->
                            <a href="complete.php?id=<?php echo $order['OrderID']; ?>" style="text-decoration: none;">
                                <button class="cancel-btn"> Order Received </button>
                            </a>
                        <?php elseif ($order['Status'] == 'Cancelled'): ?>
                            <!-- Cancelled order indicator -->
                            <span style="color: #a8a8a8;"> Cancelled </span>
                        <?php else: ?>
                            <!-- Completed order indicator -->
                            <span style="color: #4caf50;"> Completed </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
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