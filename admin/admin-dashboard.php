<?php
// Start session to verify admin access
session_start();

// Direct database connection configuration
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

// Fetch total sales (excluding cancelled and pending orders)
$total_sales_query = "SELECT SUM(p.Amount) as total_sales 
                      FROM payment p 
                      JOIN orders o ON p.OrderID = o.OrderID 
                      WHERE o.Status != 'Cancelled'
                      AND o.Status != 'Pending'";
$total_sales_result = $conn->query($total_sales_query);
$total_sales = $total_sales_result->fetch_assoc()['total_sales'] ?? 0;

// Fetch total completed orders
$total_orders_query = "SELECT COUNT(*) as total_orders FROM orders WHERE Status != 'Cancelled'";
$total_orders_result = $conn->query($total_orders_query);
$total_orders = $total_orders_result->fetch_assoc()['total_orders'] ?? 0;

// Fetch total products in inventory
$total_products_query = "SELECT COUNT(*) as total_products FROM product";
$total_products_result = $conn->query($total_products_query);
$total_products = $total_products_result->fetch_assoc()['total_products'] ?? 0;

// Performance targets for percentage calculations
$sales_target = 20000;     // $1000 sales target
$orders_target = 100;     // 100 orders target  
$products_target = 100;   // 100 products target

// Calculate percentages against targets
$sales_percentage = $total_sales > 0 ? min(100, ($total_sales / $sales_target) * 100) : 0;
$orders_percentage = $total_orders > 0 ? min(100, ($total_orders / $orders_target) * 100) : 0;
$products_percentage = $total_products > 0 ? min(100, ($total_products / $products_target) * 100) : 0;

// Round percentages to whole numbers
$sales_percentage = round($sales_percentage);
$orders_percentage = round($orders_percentage);
$products_percentage = round($products_percentage);

// Fetch 5 most recent orders for the dashboard table
$recent_orders_query = "
    SELECT o.OrderID, u.Name as CustomerName, o.OrderDate, o.Status, p.Amount 
    FROM orders o 
    JOIN user u ON o.UserID = u.UserID 
    LEFT JOIN payment p ON o.OrderID = p.OrderID 
    ORDER BY o.OrderDate DESC 
    LIMIT 5
";
$recent_orders_result = $conn->query($recent_orders_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
  <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
  <link rel="stylesheet" href="../assets/css/admin-style.css" />
  <title>Dashboard - Fabulous Finds</title>
</head>

<body>
  <!-- Main dashboard container -->
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
        <a href="index.php" class="active">
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
      <h1>Dashboard</h1>
      <!-- Date selector -->
      <div class="date">
        <input type="date" value="<?php echo date('Y-m-d'); ?>" />
      </div>
      
      <!-- Key metrics section -->
      <div class="insights">
        <!-- Total Sales metric -->
        <div class="sales">
          <span class="material-icons-sharp">analytics</span>
          <div class="middle">
            <div class="left">
              <h3>Total Sales</h3>
              <h1>₱<?php echo number_format($total_sales, 2); ?></h1>
            </div>
            <!-- Circular progress chart -->
            <div class="progress">
              <svg viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" class="progress-circle"></circle>
                <?php if ($sales_percentage > 0): ?>
                  <circle cx="50" cy="50" r="45"
                    style="stroke-dashoffset: calc(283px - (283px * <?php echo $sales_percentage; ?>) / 100)"
                    class="progress-circle-fill sales-fill"></circle>
                <?php endif; ?>
              </svg>
              <div class="number">
                <p><?php echo $sales_percentage; ?>%</p>
              </div>
            </div>
          </div>
          <small class="text-muted">Last 24 Hours</small>
        </div>

        <!-- Total Orders metric -->
        <div class="expenses">
          <span class="material-icons-sharp">bar_chart</span>
          <div class="middle">
            <div class="left">
              <h3>Total Orders</h3>
              <h1><?php echo $total_orders; ?></h1>
            </div>
            <!-- Circular progress chart -->
            <div class="progress">
              <svg viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" class="progress-circle"></circle>
                <?php if ($orders_percentage > 0): ?>
                  <circle cx="50" cy="50" r="45"
                    style="stroke-dashoffset: calc(283px - (283px * <?php echo $orders_percentage; ?>) / 100)"
                    class="progress-circle-fill expenses-fill"></circle>
                <?php endif; ?>
              </svg>
              <div class="number">
                <p><?php echo $orders_percentage; ?>%</p>
              </div>
            </div>
          </div>
          <small class="text-muted">Last 24 Hours</small>
        </div>

        <!-- Total Products metric -->
        <div class="income">
          <span class="material-icons-sharp">stacked_line_chart</span>
          <div class="middle">
            <div class="left">
              <h3>Total Products</h3>
              <h1><?php echo $total_products; ?></h1>
            </div>
            <!-- Circular progress chart -->
            <div class="progress">
              <svg viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" class="progress-circle"></circle>
                <?php if ($products_percentage > 0): ?>
                  <circle cx="50" cy="50" r="45"
                    style="stroke-dashoffset: calc(283px - (283px * <?php echo $products_percentage; ?>) / 100)"
                    class="progress-circle-fill income-fill"></circle>
                <?php endif; ?>
              </svg>
              <div class="number">
                <p><?php echo $products_percentage; ?>%</p>
              </div>
            </div>
          </div>
          <small class="text-muted">Last 24 Hours</small>
        </div>
      </div>

      <!-- Recent orders table -->
      <div class="recent-orders">
        <h2>Recent Orders</h2>
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Order Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($recent_orders_result->num_rows > 0): ?>
              <?php while ($order = $recent_orders_result->fetch_assoc()): ?>
                <tr>
                  <td>#<?php echo $order['OrderID']; ?></td>
                  <td><?php echo htmlspecialchars($order['CustomerName']); ?></td>
                  <td>₱<?php echo number_format($order['Amount'] ?? 0, 2); ?></td>
                  <td><?php echo date('M j, Y', strtotime($order['OrderDate'])); ?></td>
                  <!-- Status with color coding -->
                  <td class="<?php echo $order['Status'] == 'Completed' ? 'success' : ($order['Status'] == 'Cancelled' ? 'danger' : 'warning'); ?>">
                    <?php echo $order['Status']; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" style="text-align: center;">No recent orders</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <!-- Link to full orders page -->
        <a href="orders.php">Show All</a>
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
        <!-- Admin profile -->
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
      
      <!-- Recent updates section -->
      <div class="recent-updates">
        <h2>Recent Updates</h2>
        <div class="updates">
          <?php
          // Fetch 3 most recent completed orders for updates
          $updates_query = "
                        SELECT u.Name, o.OrderDate, p.ProductName
                        FROM orders o 
                        JOIN user u ON o.UserID = u.UserID 
                        JOIN orderdetails od ON o.OrderID = od.OrderID
                        JOIN product p ON od.ProductID = p.ProductID
                        WHERE o.Status = 'Completed' 
                        ORDER BY o.OrderDate DESC 
                        LIMIT 3
                        ";
          $updates_result = $conn->query($updates_query);
          if ($updates_result->num_rows > 0):
            while ($update = $updates_result->fetch_assoc()):
          ?>
              <div class="update">
                <div class="profile-photo">
                  <img src="../assets/img/default_costumer_profile.png" />
                </div>
                <div class="message">
                  <p><b><?php echo htmlspecialchars($update['Name']); ?></b> received order of <?php echo htmlspecialchars($update['ProductName']); ?>.</p>
                  <small class="text-muted"><?php echo date('M j, Y', strtotime($update['OrderDate'])); ?></small>
                </div>
              </div>
          <?php
            endwhile; ?>
          <?php else: ?>
            <div class="update">
              <div class="profile-photo">
                <img src="../assets/img/default_costumer_profile.png" />
              </div>
              <div class="message">
                <p><b>No recent updates</b></p>
                <small class="text-muted">Check back later for new orders</small>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Sales analytics section -->
      <div class="sales-analytics">
        <h2>Sales Analytics</h2>
        <!-- Online orders -->
        <div class="item online">
          <div class="icon">
            <span class="material-icons-sharp">shopping_cart</span>
          </div>
          <div class="right">
            <div class="info">
              <h3>ONLINE ORDERS</h3>
              <small class="text-muted">Last 24 Hours</small>
            </div>
            <h5 class="success">+39%</h5>
            <h3>3695</h3>
          </div>
        </div>
        <!-- Offline orders -->
        <div class="item offline">
          <div class="icon">
            <span class="material-icons-sharp">local_mall</span>
          </div>
          <div class="right">
            <div class="info">
              <h3>OFFLINE ORDERS</h3>
              <small class="text-muted">Last 24 Hours</small>
            </div>
            <h5 class="danger">-17%</h5>
            <h3>1253</h3>
          </div>
        </div>
        <!-- New customers -->
        <div class="item customers">
          <div class="icon">
            <span class="material-icons-sharp">person</span>
          </div>
          <div class="right">
            <div class="info">
              <h3>NEW CUSTOMERS</h3>
              <small class="text-muted">Last 24 Hours</small>
            </div>
            <h5 class="success">+25%</h5>
            <h3>862</h3>
          </div>
        </div>
        <!-- Add product quick link -->
        <div class="item add-product">
          <div>
            <span class="material-icons-sharp">add</span>
            <h3>Add Product</h3>
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