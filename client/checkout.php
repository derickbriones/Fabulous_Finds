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
  <link rel="stylesheet" href="../assets/css/checkout.css">
</head>
<body>

  <!-- Page title -->
  <h1 style="text-align:center; margin:30px 0;">🧾 Order Summary</h1>

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

        <!-- Shipping address input -->
        <textarea name="address" placeholder="Enter your complete shipping address" required><?php echo htmlspecialchars($address); ?></textarea>

        <!-- Hidden input to pass total amount -->
        <input type="hidden" name="total" value="<?php echo htmlspecialchars($total); ?>">

        <!-- Submit button to place order -->
        <button type="submit" class="btn-place-order" style="margin-top:15px;"> Place Order </button>
      </form>

    </div>

  </div>

</body>
</html>
<?php 
// Close database connection
mysqli_close($conn); 
?>