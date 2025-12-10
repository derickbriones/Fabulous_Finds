<?php
// Start session for user authentication
session_start();

// Database connection for featured products
try {
  $pdo = new PDO("mysql:host=localhost;dbname=fabulous_finds", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Fetch 4 featured products
  $stmt = $pdo->prepare("SELECT * FROM product ORDER BY ProductID DESC LIMIT 4");
  $stmt->execute();
  $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch total products count
  $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM product");
  $stmt->execute();
  $product_count = $stmt->fetch(PDO::FETCH_ASSOC);

  // Fetch total orders count
  $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE Status != 'Cancelled'");
  $stmt->execute();
  $order_count = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  // If database connection fails, use empty arrays
  $featured_products = [];
  $product_count = ['total' => 0];
  $order_count = ['total' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fabulous Finds - Luxury Fashion Store</title>
  <link rel="icon" type="image/png" href="assets/img/Fabulous-finds.png" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>
  <!-- Header & Navigation -->
  <header>
    <div class="container">
      <nav class="navbar">
        <div class="logo">
          <img src="assets/img/Fabulous-finds.png" alt="Fabulous Finds Logo">
          <div class="logo-text">Fabulous<span>Finds</span></div>
        </div>

        <div class="nav-links">
          <a href="#home" class="active">Home</a>
          <a href="#products">Products</a>
          <a href="#about">About</a>
          <a href="#contact">Contact</a>

          <?php if (isset($_SESSION['user_id'])): ?>
            <div class="auth-buttons">
              <a href="client/index.php" class="btn btn-outline">
                <i class="fas fa-user-circle"></i> Dashboard
              </a>
              <a href="logout.php" class="btn btn-primary">
                <i class="fas fa-sign-out-alt"></i> Logout
              </a>
            </div>
          <?php else: ?>
            <div class="auth-buttons">
              <a href="login.php" class="btn btn-outline">
                <i class="fas fa-sign-in-alt"></i> Login
              </a>
              <a href="register.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Register
              </a>
            </div>
          <?php endif; ?>
        </div>

        <div class="mobile-menu-btn">
          <i class="fas fa-bars"></i>
        </div>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section id="home" class="hero">
    <div class="container">
      <div class="hero-content">
        <div class="hero-text">
          <h1>Discover <span>Luxury</span> Fashion & Timeless Style</h1>
          <p>Explore our exclusive collection of premium clothing and accessories from world-renowned brands. Experience elegance, quality, and sophistication in every piece.</p>
          <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="#products" class="btn btn-primary">
              <i class="fas fa-shopping-bag"></i> Shop Now
            </a>
            <a href="#about" class="btn btn-outline">
              <i class="fas fa-play-circle"></i> Learn More
            </a>
          </div>
        </div>
        <div class="hero-image">
          <img src="assets/img/Models.png" alt="Luxury Fashion Model">
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="stats">
    <div class="container">
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-number"><?php echo $product_count['total']; ?>+</div>
          <div class="stat-label">Premium Products</div>
        </div>
        <div class="stat-item">
          <div class="stat-number"><?php echo $order_count['total']; ?>+</div>
          <div class="stat-label">Orders Delivered</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">100%</div>
          <div class="stat-label">Authentic Brands</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">24/7</div>
          <div class="stat-label">Customer Support</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Products -->
  <section id="products" class="featured-products">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Featured Collection</h2>
        <p class="section-subtitle">Handpicked luxury items from our exclusive collection</p>
      </div>

      <div class="products-grid">
        <?php if (count($featured_products) > 0): ?>
          <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
              <img src="assets/img/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>" class="product-image">
              <div class="product-info">
                <span class="product-category"><?php echo $product['Category']; ?></span>
                <h3 class="product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h3>
                <div class="product-price">₱<?php echo number_format($product['Price'], 2); ?></div>
                <div class="product-stock <?php echo $product['StockQuantity'] < 10 ? 'low' : ''; ?>">
                  <i class="fas fa-box"></i>
                  <?php echo $product['StockQuantity']; ?> in stock
                </div>
                <div class="product-actions">
                  <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="client/product-detail.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-primary btn-small">View Details</a>
                    <a href="client/add-to-cart.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-outline btn-small">
                      <i class="fas fa-cart-plus"></i> Add to Cart
                    </a>
                  <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-small" style="flex: 1;">Login to Purchase</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: var(--color-white); border-radius: var(--border-radius-3);">
            <i class="fas fa-box-open" style="font-size: 3rem; color: var(--color-light); margin-bottom: 1rem;"></i>
            <h3 style="color: var(--color-dark); margin-bottom: 1rem;">No Products Available</h3>
            <p style="color: var(--color-dark-variant);">Check back soon for our latest collection!</p>
          </div>
        <?php endif; ?>
      </div>

      <div style="text-align: center; margin-top: 3rem;">
        <a href="login.php" class="btn btn-primary" style="padding: 1rem 3rem;">
          <i class="fas fa-store"></i> View All Products
        </a>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="about">
    <div class="container">
      <div class="about-content">
        <div class="about-text">
          <h2>About <span>Fabulous Finds</span></h2>
          <p>Welcome to Fabulous Finds, your premier destination for luxury fashion and timeless elegance. We curate exclusive collections from the world's most prestigious brands, bringing you unparalleled quality and style.</p>
          <p>Our mission is to provide an exceptional shopping experience where every piece tells a story of craftsmanship, sophistication, and individuality.</p>

          <div class="features">
            <div class="feature-item">
              <div class="feature-icon">
                <i class="fas fa-award"></i>
              </div>
              <div class="feature-text">
                <h4>Premium Quality</h4>
                <p>Authentic luxury brands</p>
              </div>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
              </div>
              <div class="feature-text">
                <h4>Secure Shopping</h4>
                <p>100% safe transactions</p>
              </div>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <i class="fas fa-truck-fast"></i>
              </div>
              <div class="feature-text">
                <h4>Fast Delivery</h4>
                <p>Nationwide shipping</p>
              </div>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <i class="fas fa-headset"></i>
              </div>
              <div class="feature-text">
                <h4>24/7 Support</h4>
                <p>Always here to help</p>
              </div>
            </div>
          </div>
        </div>
        <div class="about-image">
          <img src="assets/img/fabulous-finds.png" alt="About Fabulous Finds">
        </div>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="cta">
    <div class="container">
      <h2>Ready to Experience Luxury?</h2>
      <p>Join thousands of satisfied customers who have discovered their perfect style with Fabulous Finds. Start shopping today and elevate your wardrobe.</p>
      <a href="register.php" class="btn btn-light" style="padding: 1rem 3rem;">
        <i class="fas fa-user-plus"></i> Create Free Account
      </a>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact">
    <div class="container">
      <div class="footer-content">
        <div class="footer-about">
          <div class="footer-logo">
            <img src="assets/img/Fabulous-finds.png" alt="Fabulous Finds Logo">
            <span>Fabulous Finds</span>
          </div>
          <p>Your trusted partner for luxury fashion. We bring you exclusive collections from world-renowned brands with exceptional customer service.</p>
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fab fa-pinterest"></i></a>
          </div>
        </div>

        <div class="footer-links">
          <h3>Quick Links</h3>
          <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#products">Products</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
          </ul>
        </div>

        <div class="footer-links">
          <h3>Categories</h3>
          <ul>
            <li><a href="#">Men's Fashion</a></li>
            <li><a href="#">Women's Fashion</a></li>
            <li><a href="#">Accessories</a></li>
            <li><a href="#">Perfumes</a></li>
            <li><a href="#">Footwear</a></li>
          </ul>
        </div>

        <div class="footer-links">
          <h3>Contact Info</h3>
          <ul class="contact-info">
            <li>
              <div class="contact-icon">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <span>Polangui, Albay 4505<br>Philippines</span>
            </li>
            <li>
              <div class="contact-icon">
                <i class="fas fa-phone"></i>
              </div>
              <span>(+63) 912 345 6789</span>
            </li>
            <li>
              <div class="contact-icon">
                <i class="fas fa-envelope"></i>
              </div>
              <span>info@fabulousfinds.com</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="copyright">
        <p>&copy; 2025 Fabulous Finds. All rights reserved. | Designed with <i class="fas fa-heart" style="color: var(--color-danger);"></i></p>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
  <script>
    // Mobile Menu Toggle
    document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
      const navLinks = document.querySelector('.nav-links');
      navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
    });

    // Smooth Scrolling for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');

        // Skip if href is just "#"
        if (href === '#') return;

        e.preventDefault();

        // If it's a section link
        if (href.startsWith('#')) {
          const targetId = href;
          const targetElement = document.querySelector(targetId);

          if (targetElement) {
            window.scrollTo({
              top: targetElement.offsetTop - 80,
              behavior: 'smooth'
            });

            // Close mobile menu if open
            if (window.innerWidth <= 768) {
              document.querySelector('.nav-links').style.display = 'none';
            }
          }
        }
      });
    });

    // Active Navigation Link on Scroll
    window.addEventListener('scroll', function() {
      const sections = document.querySelectorAll('section[id]');
      const navLinks = document.querySelectorAll('.nav-links a');

      let current = '';

      sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;

        if (pageYOffset >= sectionTop - 100) {
          current = section.getAttribute('id');
        }
      });

      navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
          link.classList.add('active');
        }
      });
    });

    // Auto-hide mobile menu on window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        document.querySelector('.nav-links').style.display = 'flex';
      } else {
        document.querySelector('.nav-links').style.display = 'none';
      }
    });

    // Initialize - show nav links on desktop, hide on mobile
    window.addEventListener('DOMContentLoaded', function() {
      if (window.innerWidth <= 768) {
        document.querySelector('.nav-links').style.display = 'none';
      }
    });

    // Add hover effects to product cards
    document.querySelectorAll('.product-card').forEach(card => {
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px)';
        this.style.boxShadow = '0 20px 40px rgba(132, 139, 200, 0.3)';
      });

      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'var(--box-shadow)';
      });
    });
  </script>
</body>

</html>
