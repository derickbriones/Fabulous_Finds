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

// Fetch all sellers from the database
$sellers_query = "SELECT SellerID, Name FROM seller ORDER BY Name";
$sellers_result = mysqli_query($conn, $sellers_query);
$sellers = [];
if ($sellers_result) {
  while ($row = mysqli_fetch_assoc($sellers_result)) {
    $sellers[] = $row;
  }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Get form data
  $product_name = $_POST['product_name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];
  $seller_type = $_POST['seller_type']; // 'existing' or 'new'

  // Initialize seller_id
  $seller_id = null;

  // Handle seller based on type
  if ($seller_type == 'existing') {
    $seller_id = $_POST['seller_id'];

    // Validate existing seller
    if (!is_numeric($seller_id) || $seller_id <= 0) {
      $error = "Please select a valid seller.";
    }
  } elseif ($seller_type == 'new') {
    $new_seller_name = trim($_POST['new_seller_name']);
    $new_seller_contact = trim($_POST['new_seller_contact']);
    $new_seller_price = trim($_POST['new_seller_price']);

    // Validate new seller name
    if (empty($new_seller_name)) {
      $error = "Please enter a seller name.";
    } else {
      // Check if seller already exists
      $check_seller_query = "SELECT SellerID FROM seller WHERE Name = ?";
      $check_stmt = $conn->prepare($check_seller_query);
      $check_stmt->bind_param("s", $new_seller_name);
      $check_stmt->execute();
      $check_stmt->store_result();

      if ($check_stmt->num_rows > 0) {
        // Seller exists, get their ID
        $check_stmt->bind_result($existing_seller_id);
        $check_stmt->fetch();
        $seller_id = $existing_seller_id;
      } else {
        // Insert new seller
        $new_seller_logo = ''; // Default empty for logo

        // Handle seller logo upload
        if (isset($_FILES['new_seller_logo']) && $_FILES['new_seller_logo']['error'] === UPLOAD_ERR_OK) {
          $logo_tmp = $_FILES['new_seller_logo']['tmp_name'];
          $logo_name = $_FILES['new_seller_logo']['name'];
          $logo_ext = strtolower(pathinfo($logo_name, PATHINFO_EXTENSION));

          // Allowed image extensions
          $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

          if (in_array($logo_ext, $allowedExtensions)) {
            // Create sellers directory if it doesn't exist
            $uploadDir = __DIR__ . '/images/sellers';
            if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename to avoid conflicts
            $new_logo_name = uniqid('seller_', true) . '.' . $logo_ext;
            $destPath = $uploadDir . '/' . $new_logo_name;

            if (move_uploaded_file($logo_tmp, $destPath)) {
              $new_seller_logo = $new_logo_name;
            }
          }
        }

        // Insert new seller into database
        $insert_seller_query = "INSERT INTO seller (Name, ContactInfo, AssignPrice, logo) VALUES (?, ?, ?, ?)";
        $seller_stmt = $conn->prepare($insert_seller_query);
        $seller_stmt->bind_param("ssss", $new_seller_name, $new_seller_contact, $new_seller_price, $new_seller_logo);

        if ($seller_stmt->execute()) {
          $seller_id = $conn->insert_id; // Get the new seller ID
        } else {
          $error = "Error adding new seller: " . $conn->error;
        }
      }
    }
  } else {
    $error = "Please select a seller type.";
  }

  // If no errors with seller, proceed with product
  if (!isset($error) && $seller_id) {
    // Handle optional product image upload
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
  <style>
    .seller-option {
      margin-bottom: 1rem;
      padding: 1rem;
      border: 1px solid #ddd;
      border-radius: 5px;
    }

    .seller-fields {
      margin-top: 1rem;
      padding: 1rem;
      background-color: #f9f9f9;
      border-radius: 5px;
      display: none;
    }

    .seller-fields.active {
      display: block;
    }

    .radio-group {
      display: flex;
      gap: 2rem;
      margin-bottom: 1rem;
    }

    .radio-group label {
      display: flex;
      align-items: center;
      cursor: pointer;
    }

    .radio-group input[type="radio"] {
      margin-right: 0.5rem;
    }

    .form-group small {
      display: block;
      margin-top: 0.25rem;
      color: #666;
      font-size: 0.85rem;
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
        <div style="color: var(--color-danger); margin-bottom: 1rem; padding: 1rem; background-color: #ffebee; border-radius: 5px;">
          <strong>Error:</strong> <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <!-- Product form container -->
      <div class="form-container">
        <form method="POST" action="" enctype="multipart/form-data">

          <!-- Product name input -->
          <div class="form-group">
            <label for="product_name">Product Name *</label>
            <input type="text" id="product_name" name="product_name" required>
          </div>

          <!-- Image upload (optional) -->
          <div class="form-group">
            <label for="product_image">Product Image</label>
            <input type="file" id="product_image" name="product_image" accept="image/*">
            <small>Optional. Will be stored with original filename (e.g., uniform.jpg). Existing files with same name will be replaced.</small>
          </div>

          <!-- Category input -->
          <div class="form-group">
            <label for="category">Category *</label>
            <input type="text" id="category" name="category" required>
          </div>

          <!-- Price input -->
          <div class="form-group">
            <label for="price">Price *</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
          </div>

          <!-- Stock quantity input -->
          <div class="form-group">
            <label for="stock">Stock Quantity *</label>
            <input type="number" id="stock" name="stock" min="0" required>
          </div>

          <!-- Seller Selection -->
          <div class="form-group">
            <label>Seller *</label>
            <div class="radio-group">
              <label>
                <input type="radio" name="seller_type" value="existing" id="existing_seller_radio" checked>
                Select Existing Seller
              </label>
              <label>
                <input type="radio" name="seller_type" value="new" id="new_seller_radio">
                Add New Seller
              </label>
            </div>

            <!-- Existing Seller Fields -->
            <div id="existing_seller_fields" class="seller-fields active">
              <label for="seller_id">Select Seller</label>
              <select id="seller_id" name="seller_id" required>
                <option value="">-- Select Seller --</option>
                <?php foreach ($sellers as $seller): ?>
                  <option value="<?php echo $seller['SellerID']; ?>">
                    <?php echo htmlspecialchars($seller['Name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small>Choose from existing sellers</small>
            </div>

            <!-- New Seller Fields -->
            <div id="new_seller_fields" class="seller-fields">
              <div class="form-group">
                <label for="new_seller_name">Seller Name *</label>
                <input type="text" id="new_seller_name" name="new_seller_name">
                <small>Enter the name of the new seller</small>
              </div>

              <div class="form-group">
                <label for="new_seller_contact">Contact Information</label>
                <input type="text" id="new_seller_contact" name="new_seller_contact" placeholder="e.g., 09123456789">
                <small>Phone number or email for the seller</small>
              </div>

              <div class="form-group">
                <label for="new_seller_price">Assign Price</label>
                <input type="text" id="new_seller_price" name="new_seller_price" placeholder="Optional pricing info">
                <small>Optional pricing arrangement or note</small>
              </div>

              <div class="form-group">
                <label for="new_seller_logo">Seller Logo</label>
                <input type="file" id="new_seller_logo" name="new_seller_logo" accept="image/*">
                <small>Optional logo for the seller (JPG, PNG, GIF, WEBP)</small>
              </div>
            </div>
          </div>

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
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const existingRadio = document.getElementById('existing_seller_radio');
      const newRadio = document.getElementById('new_seller_radio');
      const existingFields = document.getElementById('existing_seller_fields');
      const newFields = document.getElementById('new_seller_fields');

      // Function to toggle fields
      function toggleSellerFields() {
        if (existingRadio.checked) {
          existingFields.classList.add('active');
          newFields.classList.remove('active');
          // Make existing seller select required
          document.getElementById('seller_id').required = true;
          // Make new seller fields optional
          document.getElementById('new_seller_name').required = false;
        } else {
          existingFields.classList.remove('active');
          newFields.classList.add('active');
          // Make existing seller select optional
          document.getElementById('seller_id').required = false;
          // Make new seller name required
          document.getElementById('new_seller_name').required = true;
        }
      }

      // Add event listeners to radio buttons
      existingRadio.addEventListener('change', toggleSellerFields);
      newRadio.addEventListener('change', toggleSellerFields);

      // Initialize on page load
      toggleSellerFields();
    });
  </script>
</body>

</html>
<?php
// Close database connection
$conn->close();
?>
