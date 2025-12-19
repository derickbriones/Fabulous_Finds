<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "fabulous_finds";

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get order_id from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch specific order with MULTIPLE items using GROUP_CONCAT
$invoice_query = "
    SELECT 
        o.OrderID, 
        u.Name as CustomerName, 
        u.Email, 
        u.Address, 
        u.ContactNo,
        o.OrderDate, 
        o.Status,
        py.Amount as OrderTotal,
        py.PaymentMethod, 
        py.PaymentDate,
        GROUP_CONCAT(DISTINCT CONCAT(s.Name, ' - ', s.ContactInfo) SEPARATOR '; ') as SellerInfos,
        GROUP_CONCAT(p.ProductName SEPARATOR '; ') as ProductNames,
        GROUP_CONCAT(p.Price SEPARATOR '; ') as Prices,
        GROUP_CONCAT(od.Quantity SEPARATOR '; ') as Quantities,
        GROUP_CONCAT((p.Price * od.Quantity) SEPARATOR '; ') as ItemTotals
    FROM orders o
    JOIN user u ON o.UserID = u.UserID
    JOIN orderdetails od ON o.OrderID = od.OrderID
    JOIN product p ON od.ProductID = p.ProductID
    JOIN seller s ON p.SellerID = s.SellerID
    LEFT JOIN payment py ON o.OrderID = py.OrderID
    WHERE o.OrderID = $order_id AND o.UserID = $user_id
    GROUP BY o.OrderID, u.Name, u.Email, u.Address, u.ContactNo, 
             o.OrderDate, o.Status, py.Amount, py.PaymentMethod, py.PaymentDate
";

$result = $conn->query($invoice_query);
$invoice = $result->fetch_assoc();

// Check if order exists and belongs to user
if (!$invoice) {
    die("Invoice not found or access denied.");
}

// Parse the concatenated strings into arrays
$productNames = explode('; ', $invoice['ProductNames'] ?? '');
$prices = explode('; ', $invoice['Prices'] ?? '');
$quantities = explode('; ', $invoice['Quantities'] ?? '');
$itemTotals = explode('; ', $invoice['ItemTotals'] ?? '');
$sellerInfos = explode('; ', $invoice['SellerInfos'] ?? '');

// Calculate subtotal from individual items
$subtotal = 0;
foreach ($itemTotals as $itemTotal) {
    $subtotal += floatval($itemTotal);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png" />
    <link rel="stylesheet" href="../assets/css/view_invoice.css">
    <title>Invoice #FF<?php echo str_pad($invoice['OrderID'], 6, '0', STR_PAD_LEFT); ?> - Fabulous Finds</title>
</head>
<body>

<!-- Header-->
<header>
    <div class="top-header">
        <div class="logo">Fabulous Finds</div>
        <div class="search-bar">
            <span class="material-symbols-outlined">search</span>
            <input type="text" placeholder="Search for items...">
        </div>
        <div class="userlinks">
            <a href="cart.php"><span class="material-symbols-outlined">shopping_cart</span></a>
            <a href="orderlist.php"><span class="material-symbols-outlined">local_shipping</span></a>
            <div class="profile-dropdown">
                <button id="profile-btn"><span class="material-symbols-outlined">account_circle</span></button>
                <div class="dropdown-menu" id="dropdown-menu">
                    <a href="#">Edit Profile</a>
                    <a href="#">Add Address</a>
                    <a href="#">Settings</a>
                    <a href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <nav class="menu">
        <a href="index.php">Home</a>
        <a href="shop.php">Product</a>
        <a href="contact.php">Contact</a>
    </nav>
</header>

<!-- Compact Invoice -->
<div class="invoice-wrapper">
    <div class="invoice-card">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h2>FABULOUS FINDS</h2>
                <p>Polangui, Albay, 4505<br>
                (555) 123-4567<br>
                info@fabulousfinds.com</p>
            </div>
            <div class="invoice-meta">
                <h2>INVOICE</h2>
                <p>#FF<?php echo str_pad($invoice['OrderID'], 6, '0', STR_PAD_LEFT); ?><br>
                Date: <?php echo date('M j, Y', strtotime($invoice['OrderDate'])); ?><br>
                Status: <span class="status-<?php echo strtolower($invoice['Status']); ?> status-badge"><?php echo $invoice['Status']; ?></span></p>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-sections">
            <div class="billing-section">
                <h4>Bill To:</h4>
                <p><strong><?php echo htmlspecialchars($invoice['CustomerName']); ?></strong><br>
                <?php echo htmlspecialchars($invoice['Email']); ?><br>
                <?php echo htmlspecialchars($invoice['ContactNo'] ?? 'N/A'); ?><br>
                <?php echo htmlspecialchars($invoice['Address']); ?></p>
            </div>
        </div>

        <!-- Invoice Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Description</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Seller</th>
                    <th>Item Total</th>
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
                    <td><?php echo htmlspecialchars($productNames[$i]); ?></td>
                    <td>₱<?php echo number_format($prices[$i], 2); ?></td>
                    <td><?php echo $quantities[$i]; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($sellerName); ?></strong>
                        <?php if ($sellerContact): ?>
                        <div class="seller-info">Contact: <?php echo htmlspecialchars($sellerContact); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>₱<?php echo number_format($itemTotals[$i], 2); ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <p class="grand-total">Order Total: ₱<?php echo number_format($invoice['OrderTotal'] ?? $subtotal, 2); ?></p>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
            <p><strong>Payment Method:</strong> 
                <?php 
                if (isset($invoice['PaymentMethod'])) {
                    if ($invoice['PaymentMethod'] == 'gcash') echo 'GCash';
                    elseif ($invoice['PaymentMethod'] == 'paymaya') echo 'PayMaya';
                    elseif ($invoice['PaymentMethod'] == 'cod') echo 'Cash on Delivery';
                    else echo htmlspecialchars($invoice['PaymentMethod']);
                } else {
                    echo 'N/A';
                }
                ?>
                <?php if (isset($invoice['PaymentDate'])): ?>
                <br><strong>Paid on:</strong> <?php echo date('M j, Y', strtotime($invoice['PaymentDate'])); ?>
                <?php endif; ?>
            </p>
        </div>

        <!-- Buttons -->
        <div class="button-container">
            <button class="print-btn" onclick="window.print()">
                <span class="material-symbols-outlined">print</span>
                Print Invoice
            </button>
            <a href="orderlist.php" class="back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
                Back to Orders
            </a>
        </div>

        <!-- Footer Note -->
        <div class="invoice-footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profile-btn');
        const dropdownMenu = document.getElementById('dropdown-menu');
        if (profileBtn && dropdownMenu) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });
            document.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
            });
        }
    });
</script>

</body>
</html>

<?php 
$conn->close(); 
?>
