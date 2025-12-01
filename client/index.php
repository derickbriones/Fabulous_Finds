<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fabulous Finds</title>
  <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/client-homepage.css">
</head>
<body>
    
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

  <!-- Main banner/hero section -->
  <section class="banner">
    <div class="banner-text">
      <h1> Elevate Your Everyday Style </h1>
      <p> Discover luxury shirts, elegant pants, and timeless perfumes made for you. </p>
      <a href="shop.php" class="btn"> Shop Now </a>
    </div>
    <div class="banner-image">
      <img src="../assets/img/model.webp" alt="Model Image">
    </div>
  </section>

  <!-- Product categories section -->
  <section class="categories">
    <h3> Featured Categories </h3>

    <!-- Container for category cards -->
    <div class="category-list">
       <!-- Individual category card - Shirts -->
      <div class="category">
        <img src="../assets/img/lvshirt.jpg" alt="Shirts">
        <p> Shirts </p>
      </div>

       <!-- Individual category card - Pants -->
      <div class="category">
        <img src="../assets/img/pants.jpg" alt="Pants">
        <p> Pants </p>
      </div>

      <!-- Individual category card - Polo Shirts -->
      <div class="category">
        <img src="../assets/img/lacoste.webp" alt="Perfumes">
        <p> Polo Shirts </p>
      </div>

       <!-- Individual category card - Bags -->
      <div class="category">
        <img src="../assets/img/lvbag.webp" alt="Perfumes">
        <p> Bags </p>
      </div>

       <!-- Individual category card - Perfumes -->
      <div class="category">
        <img src="../assets/img/dior.avif" alt="Perfumes">
        <p> Perfumes </p>
      </div>

    </div>
  </section>
  
  <!-- Link to JavaScript file for interactive features -->
  <script src="../assets/js/landing.js"></script>
</body>
</html>
