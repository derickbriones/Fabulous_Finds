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

// Fetch orders for invoice generation with multiple table joins
$invoices_query = "
    SELECT o.OrderID, u.Name as CustomerName, u.Email, u.Address,
           s.Name as SellerName, s.ContactInfo as SellerContact,
           p.ProductName, p.Price, od.Quantity,
           py.Amount, py.PaymentMethod, py.PaymentDate,
           o.OrderDate, o.Status
    FROM orders o
    JOIN user u ON o.UserID = u.UserID           -- Customer info
    JOIN seller s ON o.SellerID = s.SellerID     -- Seller info
    JOIN orderdetails od ON o.OrderID = od.OrderID -- Order items
    JOIN product p ON od.ProductID = p.ProductID  -- Product details
    LEFT JOIN payment py ON o.OrderID = py.OrderID -- Payment info (optional)
    ORDER BY o.OrderDate DESC                     -- Show newest first
    LIMIT 10                                      -- Limit to 10 invoices
";
$invoices_result = $conn->query($invoices_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
  <link rel="stylesheet" href="../assets/css/admin-style.css" />
  <title>Invoices - Fabulous Finds</title>
  
  <!-- Invoice-specific styling -->
  <style>
    /* Invoice container styling */
    .invoice-container {
      background: var(--color-white);
      padding: var(--card-padding);
      border-radius: var(--card-border-radius);
      box-shadow: var(--box-shadow);
      margin-top: 1rem;
    }

    /* Invoice header with company and invoice info */
    .invoice-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 2rem;
      border-bottom: 2px solid var(--color-light);
      padding-bottom: 1rem;
    }

    /* Two-column layout for customer and seller info */
    .invoice-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      margin-bottom: 2rem;
    }

    /* Invoice items table styling */
    .invoice-items table {
      width: 100%;
      border-collapse: collapse;
    }

    .invoice-items th,
    .invoice-items td {
      padding: 0.8rem;
      border-bottom: 1px solid var(--color-light);
      text-align: left;
    }

    /* Total amount section */
    .invoice-total {
      text-align: right;
      margin-top: 1rem;
      font-size: 1.2rem;
      font-weight: bold;
    }

    /* Print button styling */
    .print-btn {
      margin-top: 1rem;
      background: var(--color-primary);
      color: var(--color-white);
      padding: 0.8rem 1.5rem;
      border-radius: var(--border-radius-1);
      cursor: pointer;
      border: none;
    }
  </style>
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
        <a href="order_history.php">
          <span class="material-icons-sharp">history</span>
          <h3>Order History</h3>
        </a>
        <!-- Current page - active -->
        <a href="invoice.php" class="active">
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
      <h1>Invoice & Receipt Management</h1>

      <!-- Loop through each invoice result -->
      <?php while ($invoice = $invoices_result->fetch_assoc()): ?>
        <div class="invoice-container">
          
          <!-- Invoice header with company and invoice info -->
          <div class="invoice-header">
            <!-- Company information -->
            <div>
              <h2>FABULOUS FINDS</h2>
              <p>Polangui<br>Albay, 4505<br>Phone: (555) 123-4567</p>
            </div>
            <!-- Invoice details -->
            <div style="text-align: right;">
              <h2>INVOICE</h2>
              <p>Invoice #: FF<?php echo str_pad($invoice['OrderID'], 6, '0', STR_PAD_LEFT); ?></p>
              <p>Date: <?php echo date('M j, Y', strtotime($invoice['OrderDate'])); ?></p>
            </div>
          </div>

          <!-- Customer and seller information -->
          <div class="invoice-details">
            <!-- Customer (bill to) information -->
            <div>
              <h3>Bill To:</h3>
              <p><strong><?php echo $invoice['CustomerName']; ?></strong><br>
                <?php echo $invoice['Email']; ?><br>
                <?php echo $invoice['Address']; ?></p>
            </div>
            <!-- Seller (from) information -->
            <div>
              <h3>From:</h3>
              <p><strong><?php echo $invoice['SellerName']; ?></strong><br>
                <?php echo $invoice['SellerContact']; ?></p>
            </div>
          </div>

          <!-- Invoice items table -->
          <div class="invoice-items">
            <table>
              <thead>
                <tr>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Unit Price</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo $invoice['ProductName']; ?></td>
                  <td><?php echo $invoice['Quantity']; ?></td>
                  <td>₱<?php echo number_format($invoice['Price'], 2); ?></td>
                  <td>₱<?php echo number_format($invoice['Amount'] ?? 0, 2); ?></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Invoice totals and payment info -->
          <div class="invoice-total">
            <p>Total: ₱<?php echo number_format($invoice['Amount'] ?? 0, 2); ?></p>
            <p>Payment Method: <?php 
              // Format payment method for display
              if (isset($invoice['PaymentMethod'])) {
                if ($invoice['PaymentMethod'] == 'gcash') {
                  echo 'GCash';
                } elseif ($invoice['PaymentMethod'] == 'paymaya') {
                  echo 'PayMaya';
                } elseif ($invoice['PaymentMethod'] == 'cod') {
                  echo 'Cash on Delivery';
                } else {
                  echo $invoice['PaymentMethod'];
                }
              } else {
                echo 'N/A';
              }
            ?></p>
            <!-- Order status with color coding -->
            <p>Status: <span class="<?php echo $invoice['Status'] == 'Completed' ? 'success' : ($invoice['Status'] == 'Cancelled' ? 'danger' : 'warning'); ?>">
                <?php echo $invoice['Status']; ?>
              </span></p>
          </div>

          <!-- Print button for this invoice -->
          <button class="print-btn" onclick="window.print()">Print Invoice</button>
        </div>
      <?php endwhile; ?>
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