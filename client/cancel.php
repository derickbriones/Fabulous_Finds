<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "fabulous_finds";

$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get order ID from URL
if (isset($_GET['id'])) {
    $orderID = mysqli_real_escape_string($conn, $_GET['id']);
    
    // First, check if order exists and get its current status
    $check_sql = "SELECT Status FROM orders WHERE OrderID = '$orderID'";
    $result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
        
        // Only cancel if order is still pending
        if ($order['Status'] == 'Pending') {
            // Update order status to 'Cancelled'
            $update_sql = "UPDATE orders SET Status = 'Cancelled' WHERE OrderID = '$orderID'";
            
            if (mysqli_query($conn, $update_sql)) {
                // Restore product stock quantities
                $restore_stock_sql = "
                    UPDATE product p 
                    JOIN orderdetails od ON p.ProductID = od.ProductID 
                    SET p.StockQuantity = p.StockQuantity + od.Quantity 
                    WHERE od.OrderID = '$orderID'
                ";
                mysqli_query($conn, $restore_stock_sql);
                
                header("Location: orderlist.php?message=Order+cancelled+successfully");
                exit;
            } else {
                header("Location: orderlist.php?error=Failed+to+cancel+order");
                exit;
            }
        } else {
            header("Location: orderlist.php?error=Cannot+cancel+order+that+is+already+" . $order['Status']);
            exit;
        }
    } else {
        header("Location: orderlist.php?error=Order+not+found");
        exit;
    }
} else {
    header("Location: orderlist.php?error=No+order+ID+provided");
    exit;
}

mysqli_close($conn);
?>
