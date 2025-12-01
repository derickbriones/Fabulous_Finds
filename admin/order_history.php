<?php
// Start session for admin authentication
session_start();

// Database connection configuration
$host = "localhost";
$user = "root";
$password = "";
$database = "fabulous_finds";

// Establish database connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

// Fetch complete order history with multiple table joins
$history_query = "
    SELECT o.OrderID, u.Name as CustomerName, s.Name as SellerName, 
           p.ProductName, od.Quantity, py.Amount, o.OrderDate, o.Status,
           py.PaymentMethod, py.PaymentDate
    FROM orders o
    JOIN user u ON o.UserID = u.UserID            -- Customer information
    JOIN seller s ON o.SellerID = s.SellerID      -- Seller information
    JOIN orderdetails od ON o.OrderID = od.OrderID -- Order line items
    JOIN product p ON od.ProductID = p.ProductID  -- Product details
    LEFT JOIN payment py ON o.OrderID = py.OrderID -- Payment info (optional)
    ORDER BY o.OrderDate DESC                     -- Show newest orders first
";
$history_result = $conn->query($history_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
  <link rel="stylesheet" href="../assets/css/admin-style.css" />
  <title>Order History - Fabulous Finds</title>
</head>

<body>
  <!-- Main admin container -->
  <div class="container">
    
    <!-- Left sidebar navigation -->
    <aside>
      <div class="top">
        <!-- Brand logo and name -->
        <div class="logo">
          <img src="../assets/img/Fabulous-finds.png" alt="Logo" class="site-logo" />
          <h2>FABULOUS <span class="primary">FINDS</span></h2>
        </div>
        <!-- Close button for mobile -->
        <div class="close" id="close-btn">
          <span class="material-icons-sharp">close</span>
        </div>
      </div>
      
      <!-- Navigation menu -->
      <div class="sidebar">
        <a href="index.php">
          <span class="material-icons-sharp">grid_view</span>
          <h3>Dashboard</h3>
        </a>
        <a href="products.php">
          <span class="material-icons-sharp">inventory</span>
          <h3>Products</h3>
        </a>
        <a href="orders.php">
          <span class="material-icons-sharp">receipt_long</span>
          <h3>Orders</h3>
        </a>
        <a href="order_summary.php">
          <span class="material-icons-sharp">summarize</span>
          <h3>Order Summary</h3>
        </a>
        <!-- Current page - active -->
        <a href="order_history.php" class="active">
          <span class="material-icons-sharp">history</span>
          <h3>Order History</h3>
        </a>
        <a href="invoice.php">
          <span class="material-icons-sharp">receipt</span>
          <h3>Invoice/Receipt</h3>
        </a>
        <a href="reports.php">
          <span class="material-icons-sharp">assessment</span>
          <h3>Reports</h3>
        </a>
        <a href="add_product.php">
          <span class="material-icons-sharp">add</span>
          <h3>Add Product</h3>
        </a>
        <!-- Logout link -->
        <a href="logout.php">
          <span class="material-icons-sharp">logout</span>
          <h3>Logout</h3>
        </a>
      </div>
    </aside>
    
    <!-- Main content area -->
    <main>
      <h1>Order History</h1>
      <div class="recent-orders">
        <h2>Complete Order History</h2>
        
        <!-- Order history table -->
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Product</th>
              <th>Quantity</th>
              <th>Amount</th>
              <th>Order Date</th>
              <th>Payment Method</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loop through each order in history -->
            <?php while ($order = $history_result->fetch_assoc()): ?>
              <tr>
                <!-- Order ID with hash prefix -->
                <td>#<?php echo $order['OrderID']; ?></td>
                
                <!-- Customer name -->
                <td><?php echo $order['CustomerName']; ?></td>
                
                <!-- Product name -->
                <td><?php echo $order['ProductName']; ?></td>
                
                <!-- Quantity ordered -->
                <td><?php echo $order['Quantity']; ?></td>
                
                <!-- Order amount with currency formatting -->
                <td>₱<?php echo number_format($order['Amount'] ?? 0, 2); ?></td>
                
                <!-- Order date and time -->
                <td><?php echo date('M j, Y H:i', strtotime($order['OrderDate'])); ?></td>
                
                <!-- Payment method with formatted display -->
                <td><?php 
                  if (isset($order['PaymentMethod'])) {
                    // Convert payment codes to readable names
                    if ($order['PaymentMethod'] == 'gcash') {
                      echo 'GCash';
                    } elseif ($order['PaymentMethod'] == 'paymaya') {
                      echo 'PayMaya';
                    } elseif ($order['PaymentMethod'] == 'cod') {
                      echo 'Cash on Delivery';
                    } else {
                      echo $order['PaymentMethod'];
                    }
                  } else {
                    echo 'N/A';
                  }
                ?></td>
                
                <!-- Order status with color-coded classes -->
                <td class="<?php
                            if ($order['Status'] == 'Completed') echo 'success';
                            elseif ($order['Status'] == 'Pending') echo 'warning';
                            else echo 'danger';
                            ?>">
                  <?php echo $order['Status']; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
    
    <!-- Right sidebar -->
    <div class="right">
      <div class="top">
        <!-- Mobile menu toggle -->
        <button id="menu-btn">
          <span class="primary material-icons-sharp">menu</span>
        </button>
        
        <!-- Theme toggle -->
        <div class="theme-toggler">
          <span class="material-icons-sharp active">light_mode</span>
          <span class="material-icons-sharp">dark_mode</span>
        </div>
        
        <!-- Admin profile section -->
        <div class="profile">
          <div class="info">
            <p>Hey, <b>Admin</b></p>
            <small class="text-muted">Administrator</small>
          </div>
          <div class="profile-photo">
            <img src="../assets/img/profile.jpg" />
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Admin dashboard JavaScript -->
  <script src="../assets/js/admin-js.js"></script>
</body>

</html>
<?php 
// Close database connection
$conn->close(); 
?>