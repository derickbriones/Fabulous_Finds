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

// Handle product status toggle when toggle_id parameter is present
if (isset($_GET['toggle_id'])) {
  $toggle_id = $_GET['toggle_id'];

  // First, get the current status
  $check_query = "SELECT Status FROM product WHERE ProductID = ?";
  $stmt = $conn->prepare($check_query);
  $stmt->bind_param("i", $toggle_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $product = $result->fetch_assoc();

  // Toggle the status
  $new_status = ($product['Status'] == 'A') ? 'D' : 'A';

  $update_query = "UPDATE product SET Status = ? WHERE ProductID = ?";
  $stmt = $conn->prepare($update_query);
  $stmt->bind_param("si", $new_status, $toggle_id);
  $stmt->execute();

  // Redirect back to products page after update
  header("Location: products.php");
  exit();
}

// Fetch all products with seller information
$products_query = "
    SELECT p.*, s.Name as SellerName 
    FROM product p 
    LEFT JOIN seller s ON p.SellerID = s.SellerID
    ORDER BY p.ProductID DESC
";
$products_result = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
  <link rel="stylesheet" href="../assets/css/admin-style.css" />
  <title>Products - Fabulous Finds</title>
  <style>
    /* Additional styles for status badges */
    .status-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .status-active {
      background-color: #d4edda;
      color: #155724;
    }

    .status-disabled {
      background-color: #f8d7da;
      color: #000000ff;
    }

    .toggle-btn {
      padding: 4px 12px;
      border-radius: 4px;
      border: none;
      cursor: pointer;
      font-weight: bold;
      font-size: 12px;
      text-transform: uppercase;
    }

    .toggle-disable {
      background-color: #ff0000ff;
      color: black;
    }

    .toggle-enable {
      background-color: #28a745;
      color: white;
    }

    .status-cell {
      text-align: center;
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
        <!-- Current page - active -->
        <a href="products.php" class="active">
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
      <h1>Products Management</h1>
      <div class="recent-orders">
        <h2>All Products</h2>

        <!-- Products table -->
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Seller</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loop through each product in the database -->
            <?php while ($product = $products_result->fetch_assoc()): ?>
              <tr>
                <!-- Product ID -->
                <td><?php echo $product['ProductID']; ?></td>

                <!-- Product name -->
                <td><?php echo $product['ProductName']; ?></td>

                <!-- Product category -->
                <td><?php echo $product['Category']; ?></td>

                <!-- Product price with currency formatting -->
                <td>₱<?php echo number_format($product['Price'], 2); ?></td>

                <!-- Stock quantity -->
                <td><?php echo $product['StockQuantity']; ?></td>

                <!-- Status -->
                <td class="status-cell">
                  <?php if ($product['Status'] == 'A'): ?>
                    <span class="status-badge status-active">Active</span>
                  <?php else: ?>
                    <span class="status-badge status-disabled" >Disabled</span>
                  <?php endif; ?>
                </td>

                <!-- Seller name (or N/A if not associated) -->
                <td><?php echo $product['SellerName'] ?? 'N/A'; ?></td>

                <!-- Action buttons for product management -->
                <td class="action-buttons">
                  <!-- Edit button -->
                  <a href="edit_product.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-primary">Edit</a>

                  <!-- Toggle Status button -->
                  <?php if ($product['Status'] == 'A'): ?>
                    <a href="products.php?toggle_id=<?php echo $product['ProductID']; ?>"
                      class="toggle-btn toggle-disable"
                      onclick="return confirm('Are you sure you want to disable this product? It will not be visible to customers.')">
                      Disable
                    </a>
                  <?php else: ?>
                    <a href="products.php?toggle_id=<?php echo $product['ProductID']; ?>"
                      class="toggle-btn toggle-enable"
                      onclick="return confirm('Are you sure you want to activate this product? It will be visible to customers.')">
                      Activate
                    </a>
                  <?php endif; ?>
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
