<?php
// Start session to manage cart and user data
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

// Initialize cart in session if it doesn't exist
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Handle "Add to Cart" with stock validation
$message = "";
$remainingStock = [];

// Process form submission when adding item to cart
if(isset($_POST['add_to_cart'])){
    $productID = $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    if($quantity < 1) $quantity = 1;

    // Fetch current stock from database
    $stockQuery = "SELECT StockQuantity FROM product WHERE ProductID = '$productID'";
    $stockResult = mysqli_query($conn, $stockQuery);
    $row = mysqli_fetch_assoc($stockResult);
    $availableStock = (int) $row['StockQuantity'];

    // Check how many are already in cart
    $currentQtyInCart = isset($_SESSION['cart'][$productID]) ? $_SESSION['cart'][$productID] : 0;
    $totalRequestedQty = $currentQtyInCart + $quantity;

    // Validate if requested quantity exceeds available stock
    if($totalRequestedQty > $availableStock){
        $message = "❌ Cannot add $quantity item(s). Only ".($availableStock - $currentQtyInCart)." left in stock.";
        $remainingStock[$productID] = $availableStock - $currentQtyInCart;
    } else {
        // Add item to cart
        $_SESSION['cart'][$productID] = $totalRequestedQty;
        $message = "✅ Product added to cart!";
        $remainingStock[$productID] = $availableStock - $totalRequestedQty;
    }
}

// Calculate remaining stock for all products
$query = "SELECT ProductID, StockQuantity FROM product";
$resultStock = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($resultStock)){
    $pid = $row['ProductID'];
    $remainingStock[$pid] = $row['StockQuantity'] - (isset($_SESSION['cart'][$pid]) ? $_SESSION['cart'][$pid] : 0);
}

// Get total number of items in cart
$cart_count = array_sum($_SESSION['cart']);

// Fetch all products from database
$query = "SELECT ProductID, ProductName, Category, Price, StockQuantity, image FROM product";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop - Fabulous Finds</title>
  <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/shop-style.css">
</head>
<body>

  <!-- Site header with navigation -->
  <header>
    <div class="top-header">
      <div class="logo">Fabulous Finds</div>
      
      <!-- Search bar -->
      <div class="search-bar">
        <span class="material-symbols-outlined"> search </span>
        <input type="text" placeholder="Search for items...">
      </div>
      
      <!-- User action links -->
      <div class="userlinks">
        <!-- Shopping cart link with item count badge -->
        <a href="cart.php">
          <span class="material-symbols-outlined"> shopping_cart </span>
          <span class="cart-badge"><?php echo $cart_count; ?></span>
        </a>
        
        <!-- Order history link -->
        <a href="orderlist.php">
          <span class="material-symbols-outlined"> local_shipping </span>
        </a>
        
        <!-- Profile dropdown menu -->
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

  <!-- Modal for cart notifications -->
  <div id="cartModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <p id="modalMessage"><?php echo $message; ?></p>
    </div>
  </div>

  <!-- Product listing container -->
  <div class="product-container">
    <!-- Loop through each product from database -->
    <?php while($row = mysqli_fetch_assoc($result)): 
      $pid = $row['ProductID'];
      $stockLeft = $remainingStock[$pid];
      $stockText = $stockLeft > 0 ? "Stock: $stockLeft" : "❌ Sorry, the item is out of stock";
    ?>
    <div class="product-card">
      <!-- Product image -->
      <img src="../assets/img/<?php echo $row['image']; ?>" alt="<?php echo $row['ProductName']; ?>">
      
      <!-- Product name -->
      <h2><?php echo $row['ProductName']; ?></h2>
      
      <!-- Product category -->
      <p class="category"><?php echo $row['Category']; ?></p>
      
      <!-- Product price -->
      <p class="price">₱<?php echo number_format($row['Price'],2); ?></p>
      
      <!-- Stock information -->
      <p class="stock" id="stock-<?php echo $pid; ?>"><?php echo $stockText; ?></p>

      <!-- Add to cart form -->
      <form method="post" action="" class="cart-form">
        <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
        
        <!-- Quantity controls and add to cart button -->
        <div class="quantity-group">
          <button type="button" class="qty-btn" onclick="decreaseQty(this)">−</button>
          <input type="number" name="quantity" value="1" min="1" class="qty-input" 
                 max="<?php echo $stockLeft; ?>" <?php echo $stockLeft == 0 ? 'disabled' : ''; ?>>
          <button type="button" class="qty-btn" onclick="increaseQty(this, <?php echo $stockLeft; ?>)" 
                  <?php echo $stockLeft == 0 ? 'disabled' : ''; ?>>+</button>
          <button type="submit" name="add_to_cart" class="btn-cart" 
                  <?php echo $stockLeft == 0 ? 'disabled' : ''; ?>>🛒 Add to Cart</button>
        </div>
      </form>
    </div>
    <?php endwhile; ?>
  </div>

  <!-- JavaScript for interactive features -->
  <script>
    // Quantity control functions
    function decreaseQty(btn) {
      let input = btn.parentElement.querySelector('.qty-input');
      let value = parseInt(input.value);
      if (value > 1) input.value = value - 1;
    }
    
    function increaseQty(btn, stock) {
      let input = btn.parentElement.querySelector('.qty-input');
      let value = parseInt(input.value);
      if(value < stock) input.value = value + 1;
    }

    // Profile dropdown functionality
    const profileBtn = document.getElementById("profile-btn");
    const dropdownMenu = document.getElementById("dropdown-menu");
    profileBtn.addEventListener("click", () => { 
      dropdownMenu.classList.toggle("show"); 
    });
    
    // Close dropdown when clicking outside
    window.addEventListener("click", (e) => {
      if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove("show");
      }
    });

    // Modal and stock management
    const modal = document.getElementById("cartModal");
    const closeBtn = document.getElementById("closeModal");
    const remainingStock = <?php echo json_encode($remainingStock); ?>;

    // Update stock display for a product
    function updateStockDisplay(productID){
      const stockEl = document.getElementById('stock-' + productID);
      const card = stockEl.closest('.product-card');
      const inputEl = card.querySelector('.qty-input');
      const btnEl = card.querySelector('.btn-cart');
      
      if(remainingStock[productID] > 0){
        stockEl.textContent = 'Stock: ' + remainingStock[productID];
        inputEl.disabled = false;
        btnEl.disabled = false;
      } else {
        stockEl.textContent = '❌ Sorry, the item is out of stock';
        inputEl.disabled = true;
        btnEl.disabled = true;
      }
    }

    // Show modal if there's a message from PHP
    <?php if($message && isset($_POST['product_id'])): ?>
      modal.style.display = "block";
      setTimeout(() => { 
        modal.style.display = "none"; 
        updateStockDisplay(<?php echo $_POST['product_id']; ?>);
      }, 2500);
    <?php endif; ?>

    // Close modal on button click
    closeBtn.onclick = () => { 
      modal.style.display = "none"; 
      <?php if(isset($_POST['product_id'])): ?>
        updateStockDisplay(<?php echo $_POST['product_id']; ?>);
      <?php endif; ?>
    }
    
    // Close modal when clicking outside
    window.onclick = (event) => { 
      if(event.target == modal) modal.style.display = "none"; 
    }
  </script>
</body>
</html>