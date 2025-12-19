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
    SELECT 
        o.OrderID, 
        u.Name as CustomerName, 
        u.Email, 
        u.Address, 
        u.ContactNo,
        py.Amount as OrderTotal, 
        py.PaymentMethod, 
        o.OrderDate, 
        o.Status,
        GROUP_CONCAT(p.ProductName SEPARATOR '; ') as ProductNames,
        GROUP_CONCAT(p.Price SEPARATOR '; ') as Prices,
        GROUP_CONCAT(od.Quantity SEPARATOR '; ') as Quantities,
        GROUP_CONCAT((p.Price * od.Quantity) SEPARATOR '; ') as ItemTotals,
        GROUP_CONCAT(CONCAT(s.Name, ' - ', s.ContactInfo) SEPARATOR '; ') as SellerInfos
    FROM orders o
    JOIN user u ON o.UserID = u.UserID
    JOIN orderdetails od ON o.OrderID = od.OrderID
    JOIN product p ON od.ProductID = p.ProductID
    JOIN seller s ON p.SellerID = s.SellerID
    LEFT JOIN payment py ON o.OrderID = py.OrderID
    GROUP BY o.OrderID
    ORDER BY o.OrderDate DESC
    LIMIT 10
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
      margin-bottom: 2rem;
      page-break-inside: avoid;
    }

    /* Invoice header with company and invoice info */
    .invoice-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 2rem;
      border-bottom: 2px solid var(--color-light);
      padding-bottom: 1rem;
    }

    /* Single column layout for customer info only */
    .invoice-details {
      margin-bottom: 2rem;
    }

    /* Invoice items table styling */
    .invoice-items table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1rem;
    }

    .invoice-items th,
    .invoice-items td {
      padding: 0.8rem;
      border-bottom: 1px solid var(--color-light);
      text-align: left;
    }

    .invoice-items th {
      background-color: var(--color-light);
      font-weight: bold;
    }

    /* Seller info styling */
    .seller-info {
      font-size: 0.9rem;
      color: var(--color-dark-variant);
      margin-top: 0.2rem;
    }

    /* Total amount section */
    .invoice-total {
      text-align: right;
      margin-top: 2rem;
      font-size: 1.2rem;
      border-top: 2px solid var(--color-dark);
      padding-top: 1rem;
    }

    .invoice-total .grand-total {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--color-primary);
      margin-bottom: 1rem;
    }

    /* Status badges */
    .success {
      color: var(--color-success);
      font-weight: bold;
    }

    .warning {
      color: var(--color-warning);
      font-weight: bold;
    }

    .danger {
      color: var(--color-danger);
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
      float: right;
    }

    .print-btn:hover {
      background: var(--color-dark);
    }

    /* Item totals column */
    .item-total {
      font-weight: bold;
    }

    /* Clear float */
    .clearfix::after {
      content: "";
      clear: both;
      display: table;
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
      <?php while ($invoice = $invoices_result->fetch_assoc()):
        // Parse the concatenated strings into arrays
        $productNames = explode('; ', $invoice['ProductNames']);
        $prices = explode('; ', $invoice['Prices']);
        $quantities = explode('; ', $invoice['Quantities']);
        $itemTotals = explode('; ', $invoice['ItemTotals']);
        $sellerInfos = explode('; ', $invoice['SellerInfos']);

        // Calculate subtotal from individual items (kept for internal calculation only)
        $subtotal = 0;
        foreach ($itemTotals as $itemTotal) {
          $subtotal += floatval($itemTotal);
        }
      ?>
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

          <!-- Customer information only -->
          <div class="invoice-details">
            <!-- Customer (bill to) information -->
            <div>
              <h3>Bill To:</h3>
              <p><strong><?php echo $invoice['CustomerName']; ?></strong><br>
                <?php echo $invoice['Email']; ?><br>
                <?php echo $invoice['ContactNo'] ?? ''; ?><br>
                <?php echo $invoice['Address']; ?></p>
            </div>
          </div>

          <!-- Invoice items table -->
          <div class="invoice-items">
            <table>
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Description</th>
                  <th>Unit Price</th>
                  <th>Quantity</th>
                  <th>Seller</th>
                  <th class="item-total">Item Total</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $itemCount = count($productNames);
                for ($i = 0; $i < $itemCount; $i++):
                  // Parse seller info to separate name and contact
                  $sellerInfo = $sellerInfos[$i] ?? '';
                  $sellerParts = explode(' - ', $sellerInfo);
                  $sellerName = $sellerParts[0] ?? '';
                  $sellerContact = $sellerParts[1] ?? '';
                ?>
                  <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo $productNames[$i]; ?></td>
                    <td>₱<?php echo number_format($prices[$i], 2); ?></td>
                    <td><?php echo $quantities[$i]; ?></td>
                    <td>
                      <strong><?php echo $sellerName; ?></strong>
                      <?php if ($sellerContact): ?>
                        <div class="seller-info">Contact: <?php echo $sellerContact; ?></div>
                      <?php endif; ?>
                    </td>
                    <td class="item-total">₱<?php echo number_format($itemTotals[$i], 2); ?></td>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>

          <!-- Invoice total section - Subtotal removed -->
          <div class="invoice-total">
            <p class="grand-total">Order Total: ₱<?php echo number_format($invoice['OrderTotal'] ?? $subtotal, 2); ?></p>
            <p>Payment Method: <?php
                                if (isset($invoice['PaymentMethod'])) {
                                  if (strtolower($invoice['PaymentMethod']) == 'gcash') {
                                    echo 'GCash';
                                  } elseif (strtolower($invoice['PaymentMethod']) == 'paymaya') {
                                    echo 'PayMaya';
                                  } elseif (strtolower($invoice['PaymentMethod']) == 'cod') {
                                    echo 'Cash on Delivery';
                                  } else {
                                    echo $invoice['PaymentMethod'];
                                  }
                                } else {
                                  echo 'N/A';
                                }
                                ?></p>
            <p>Status: <span class="<?php echo $invoice['Status'] == 'Completed' ? 'success' : ($invoice['Status'] == 'Cancelled' ? 'danger' : 'warning'); ?>">
                <?php echo $invoice['Status']; ?>
              </span></p>
          </div>

          <!-- Print button for this invoice -->
          <div class="clearfix">
            <button class="print-btn" onclick="printInvoice(this)">Print Invoice</button>
          </div>
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
  <script>
    // Function to print individual invoice
    function printInvoice(button) {
      const invoiceContainer = button.closest('.invoice-container');
      const originalContents = document.body.innerHTML;

      // Create a new window for printing
      const printWindow = window.open('', '', 'height=600,width=800');
      printWindow.document.write('<html><head><title>Invoice Print</title>');
      printWindow.document.write('<style>');
      printWindow.document.write(`
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-container { border: 1px solid #ccc; padding: 20px; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .invoice-details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; }
        th { background-color: #f5f5f5; }
        .invoice-total { text-align: right; margin-top: 20px; border-top: 2px solid #333; padding-top: 10px; }
        .grand-total { font-size: 1.2em; font-weight: bold; margin-bottom: 10px; }
        .item-total { font-weight: bold; }
        .seller-info { font-size: 0.85em; color: #666; margin-top: 2px; }
        @media print {
          body { margin: 0; }
          .no-print { display: none; }
        }
      `);
      printWindow.document.write('</style></head><body>');
      printWindow.document.write(invoiceContainer.innerHTML);
      printWindow.document.write('</body></html>');
      printWindow.document.close();

      // Wait for content to load then print
      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 500);
    }

    // Global print function (for all invoices)
    window.printAll = function() {
      window.print();
    }
  </script>
</body>

</html>
<?php
// Close database connection
$conn->close();
?>
