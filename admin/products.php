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

// Handle product deletion when delete_id parameter is present
if (isset($_GET['delete_id'])) {
  $delete_id = $_GET['delete_id'];
  $delete_query = "DELETE FROM product WHERE ProductID = ?";
  $stmt = $conn->prepare($delete_query);
  $stmt->bind_param("i", $delete_id);  // "i" = integer parameter
  $stmt->execute();
  // Redirect back to products page after deletion
  header("Location: products.php");
  exit();
}

// Fetch all products with seller information
$products_query = "
    SELECT p.*, s.Name as SellerName 
    FROM product p 
    LEFT JOIN seller s ON p.SellerID = s.SellerID  -- Include seller info
    ORDER BY p.ProductID DESC                      -- Show newest products first
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
                
                <!-- Seller name (or N/A if not associated) -->
                <td><?php echo $product['SellerName'] ?? 'N/A'; ?></td>
                
                <!-- Action buttons for product management -->
                <td class="action-buttons">
                  <!-- Edit button - links to edit_product.php with product ID -->
                  <a href="edit_product.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-primary">Edit</a>
                  
                  <!-- Delete button - includes confirmation via URL parameter -->
                  <a href="products.php?delete_id=<?php echo $product['ProductID']; ?>" class="btn btn-danger">Delete</a>
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