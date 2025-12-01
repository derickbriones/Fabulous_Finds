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

// Redirect if no product ID is provided
if (!isset($_GET['id'])) {
  header("Location: products.php");
  exit();
}

// Get product ID from URL
$product_id = $_GET['id'];

// Fetch product details from database
$product_query = "SELECT * FROM product WHERE ProductID = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);  // "i" = integer parameter
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

// Redirect if product doesn't exist
if (!$product) {
  header("Location: products.php");
  exit();
}

// Handle form submission for product updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Get form data
  $product_name = $_POST['product_name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];

  // Force seller to Fabulous Finds (SellerID = 1) for admin edits
  $seller_id = 1;

  // Update query - set SellerID = 1 unconditionally
  $update_query = "UPDATE product SET ProductName = ?, Category = ?, Price = ?, StockQuantity = ?, SellerID = 1 WHERE ProductID = ?";
  $stmt = $conn->prepare($update_query);
  $stmt->bind_param("ssdii", $product_name, $category, $price, $stock, $product_id);

  // Execute update and redirect on success
  if ($stmt->execute()) {
    header("Location: products.php");
    exit();
  } else {
    $error = "Error updating product: " . $conn->error;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
  <link rel="stylesheet" href="../assets/css/admin-style.css" />
  <title>Edit Product - Fabulous Finds</title>
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
      <h1>Edit Product</h1>
      
      <!-- Display error message if update fails -->
      <?php if (isset($error)): ?>
        <div style="color: var(--color-danger); margin-bottom: 1rem;"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <!-- Product editing form -->
      <div class="form-container">
        <form method="POST" action="">
          
          <!-- Product name input -->
          <div class="form-group">
            <label for="product_name">Product Name</label>
            <input type="text" id="product_name" name="product_name" 
                   value="<?php echo htmlspecialchars($product['ProductName']); ?>" required>
          </div>
          
          <!-- Category input -->
          <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" 
                   value="<?php echo htmlspecialchars($product['Category']); ?>" required>
          </div>
          
          <!-- Price input -->
          <div class="form-group">
            <label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" 
                   value="<?php echo $product['Price']; ?>" required>
          </div>
          
          <!-- Stock quantity input -->
          <div class="form-group">
            <label for="stock">Stock Quantity</label>
            <input type="number" id="stock" name="stock" 
                   value="<?php echo $product['StockQuantity']; ?>" required>
          </div>
          
          <!-- Hidden seller field (fixed to Fabulous Finds) -->
          <input type="hidden" name="seller_id" value="1">
          
          <!-- Action buttons -->
          <button type="submit" class="btn btn-primary">Update Product</button>
          <a href="products.php" class="btn btn-danger" style="margin-left: 1rem;">Cancel</a>
        </form>
      </div>
    </main>
  </div>
  
  <!-- Admin dashboard JavaScript -->
  <script src="../assets/js/admin-js.js"></script>
</body>

</html>
<?php 
// Close database connection
$conn->close(); 
?>