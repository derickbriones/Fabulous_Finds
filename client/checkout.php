<?php
// Start session to access cart and user data
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

// Redirect to cart if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Fetch product details for items in cart
$cart_items = [];
$total = 0;

// Create comma-separated list of product IDs
$ids = implode(',', array_keys($_SESSION['cart']));

// Query database for cart products
$query = "SELECT ProductID, ProductName, Price, image FROM product WHERE ProductID IN ($ids)";
$result = mysqli_query($conn, $query);

// Process each product and calculate totals
while($row = mysqli_fetch_assoc($result)){
    $id = $row['ProductID'];
    $qty = $_SESSION['cart'][$id];
    $subtotal = $row['Price'] * $qty;
    $total += $subtotal;

    // Store product details in array
    $cart_items[] = [
        'id' => $id,
        'name' => $row['ProductName'],
        'price' => $row['Price'],
        'image' => $row['image'],
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
}

// Initialize checkout form variables from session
$address = isset($_SESSION['address']) ? $_SESSION['address'] : '';
$payment_method = isset($_SESSION['payment_method']) ? $_SESSION['payment_method'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Fabulous Finds</title>
  <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/checkout.css">
  <style>
    .checkout-buttons {
      display: flex;
      gap: 10px;
      margin-top: 15px;
      justify-content: flex-end;
    }
    
    .btn-cancel {
      background: var(--color-danger);
      color: var(--color-white);
      padding: 12px 25px;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      transition: background 0.3s, transform 0.2s;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }
    
    .btn-cancel:hover {
      background: #e04a58;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

  <!-- HEADER (SAME AS INDEX.PHP) -->
  <header>
    <div class="top-header">
      <div class="logo">Fabulous Finds</div>

      <!-- Search bar with icon -->
      <div class="search-bar">
        <span class="material-symbols-outlined">search</span>
        <input type="text" placeholder="Search for items...">
      </div>

      <!-- User navigation links -->
      <div class="userlinks">
         <!-- Shopping cart link -->
        <a href="cart.php">
          <span class="material-symbols-outlined"> shopping_cart </span>
        </a>

        <!-- Order history link -->
        <a href="orderlist.php">
          <span class="material-symbols-outlined"> local_shipping </span>
        </a>

        <!-- Profile dropdown -->
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

  <!-- Page title -->
  <h1 style="text-align:center; margin:30px 0;">Order Summary</h1>

  <!-- Main checkout container -->
  <div class="checkout-container">

    <!-- Order items table -->
    <table>
      <tr>
        <th> Product </th>
        <th> Qty </th>
        <th> Price </th>
        <th> Subtotal </th>
      </tr>

      <!-- Loop through cart items -->
      <?php foreach($cart_items as $item): ?>
      <tr>
        <!-- Product image and name -->
        <td class="product-info">
          <img src="../assets/img/<?php echo $item['image']; ?>" alt="">
          <div>
            <?php echo $item['name']; ?>
          </div>
        </td>
        <!-- Quantity -->
        <td><?php echo $item['qty']; ?></td>
        <!-- Unit price -->
        <td>₱<?php echo number_format($item['price'], 2); ?></td>
        <!-- Item subtotal -->
        <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>

    <!-- Order summary and payment form -->
    <div class="checkout-total">
      <!-- Display total amount -->
      <h3>Total Amount: ₱<?php echo number_format($total, 2); ?></h3>

      <!-- Checkout form to process order -->
      <form method="post" action="process_order.php">
        <!-- Payment method selection -->
        <select name="payment_method" required>
          <option value="">-- Select Payment --</option>
          <option value="COD" <?php if($payment_method==='COD') echo 'selected'; ?>> Cash on Delivery </option>
          <option value="Gcash" <?php if($payment_method==='Gcash') echo 'selected'; ?>> Gcash </option>
          <option value="Paymaya" <?php if($payment_method==='Paymaya') echo 'selected'; ?>> PayMaya </option>
        </select>

        <!-- Hidden input to pass total amount -->
        <input type="hidden" name="total" value="<?php echo htmlspecialchars($total); ?>">

        <!-- Buttons container -->
        <div class="checkout-buttons">
          <!-- Cancel button -->
          <a href="cart.php" class="btn-cancel">Cancel Order</a>
          
          <!-- Submit button to place order -->
          <button type="submit" class="btn-place-order"> Place Order </button>
        </div>
      </form>

    </div>

  </div>

  <script>
    // Profile dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profile-btn');
        const dropdownMenu = document.getElementById('dropdown-menu');
        
        if (profileBtn && dropdownMenu) {
            profileBtn.addEventListener('click', function() {
                dropdownMenu.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!profileBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }
    });
  </script>

</body>
</html>
<?php 
// Close database connection
mysqli_close($conn); 
?>
