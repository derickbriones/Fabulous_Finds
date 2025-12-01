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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Get form data
  $product_name = $_POST['product_name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];

  // Force seller to Fabulous Finds (SellerID = 1) since admin is adding
  $seller_id = 1;

  // Handle optional image upload
  $image_name = ''; // Default empty string to match database NOT NULL constraint
  if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['product_image']['tmp_name'];
    $fileName = $_FILES['product_image']['name'];
    $fileSize = $_FILES['product_image']['size'];
    $fileType = $_FILES['product_image']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Allowed image extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($fileExtension, $allowedExtensions)) {
      // Create products directory if it doesn't exist
      $uploadDir = __DIR__ . '/images/products';
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Use original filename exactly as uploaded
      $newFileName = $fileName;
      $destPath = $uploadDir . '/' . $newFileName;

      // Remove existing file if it exists (overwrite)
      if (file_exists($destPath)) {
        unlink($destPath);
      }

      // Move uploaded file to destination (will overwrite if same name)
      if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Store just the filename in database (not the full path)
        $image_name = $newFileName;
      } else {
        $error = "There was an error moving the uploaded file.";
      }
    } else {
      $error = "Upload failed. Allowed file types: " . implode(", ", $allowedExtensions);
    }
  }

  // If no errors, insert product into database
  if (!isset($error)) {
    $insert_query = "INSERT INTO product (ProductName, Category, Price, StockQuantity, SellerID, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);

    // Bind parameters: s = string, d = double, i = integer
    $stmt->bind_param("ssdiis", $product_name, $category, $price, $stock, $seller_id, $image_name);

    // Execute query and redirect on success
    if ($stmt->execute()) {
      header("Location: products.php");
      exit();
    } else {
      $error = "Error adding product: " . $conn->error;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
  <link rel="icon" type="image/png" href="..assets/img/Fabulous-finds.png" />
  <link rel="stylesheet" href="../assets/css/admin-style.css" />
  <title>Add Product - Fabulous Finds</title>
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
        <!-- Current page - active -->
        <a href="add_product.php" class="active">
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
      <h1>Add New Product</h1>

      <!-- Display error message if any -->
      <?php if (isset($error)): ?>
        <div style="color: var(--color-danger); margin-bottom: 1rem;"><?php echo $error; ?></div>
      <?php endif; ?>

      <!-- Product form container -->
      <div class="form-container">
        <form method="POST" action="" enctype="multipart/form-data">

          <!-- Product name input -->
          <div class="form-group">
            <label for="product_name">Product Name</label>
            <input type="text" id="product_name" name="product_name" required>
          </div>

          <!-- Image upload (optional) -->
          <div class="form-group">
            <label for="product_image">Image</label>
            <input type="file" id="product_image" name="product_image" accept="image/*">
            <small>Will be stored with original filename (e.g., uniform.jpg). Existing files with same name will be replaced.</small>
          </div>

          <!-- Category input -->
          <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" required>
          </div>

          <!-- Price input -->
          <div class="form-group">
            <label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" required>
          </div>

          <!-- Stock quantity input -->
          <div class="form-group">
            <label for="stock">Stock Quantity</label>
            <input type="number" id="stock" name="stock" required>
          </div>

          <!-- Hidden seller field (fixed to Fabulous Finds) -->
          <input type="hidden" name="seller_id" value="1">

          <!-- Submit button -->
          <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
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
