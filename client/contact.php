<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fabulous Finds – Contact</title>
    <link rel="icon" type="image/png" href="../assets/img/Fabulous-finds.png">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/contact-style.css">
</head>
<body>

<header>
    <div class="top-header">
      <div class="logo">Fabulous Finds</div>

      <div class="search-bar">
        <span class="material-symbols-outlined">search</span>
        <input type="text" placeholder="Search for items...">
      </div>

      <div class="userlinks">
        <a href="cart.php">
          <span class="material-symbols-outlined">shopping_cart</span>
        </a>

        <a href="orderlist.php">
          <span class="material-symbols-outlined">local_shipping</span>
        </a>

        <!-- 🔸 Profile dropdown -->
        <div class="profile-dropdown">
          <button id="profile-btn">
            <span class="material-symbols-outlined">account_circle</span>
          </button>
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
      <a href="contact.php" class="active">Contact</a>
    </nav>
</header>

<section class="contact-wrapper">

    <div class="big-card">

        <!-- LEFT CARD -->
        <div class="contact-info">
            <h2>Customer Service</h2>
            <p>If you have any questions, feel free to contact us anytime.</p>

            <p><strong>Email:</strong> support@fabulousfinds.com</p>
            <p><strong>Phone:</strong> +63 912 345 6789</p>
        </div>

        <!-- RIGHT CARD -->
        <form id="contactForm" class="contact-form" action="send_message.php" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" placeholder="Type your message here..." required></textarea>
            </div>

            <div class="form-group file-upload">
                <label for="file">Attach an image (optional)</label>
                <div class="file-container">
                    <input type="file" id="file" name="file" accept="image/*">
                    <label for="file" class="custom-file-btn">Choose File</label>
                    <span id="fileLabel">No file chosen</span>
                    <button type="button" id="removeFile" class="hidden">✖</button>
                </div>
            </div>

            <button type="submit" class="submit-btn">Send Message</button>

        </form>

    </div>

</section>

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
        
        // File upload functionality
        const fileInput = document.getElementById('file');
        const fileLabel = document.getElementById('fileLabel');
        const removeFileBtn = document.getElementById('removeFile');
        
        if (fileInput && fileLabel && removeFileBtn) {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileLabel.textContent = this.files[0].name;
                    removeFileBtn.classList.remove('hidden');
                } else {
                    fileLabel.textContent = 'No file chosen';
                    removeFileBtn.classList.add('hidden');
                }
            });
            
            removeFileBtn.addEventListener('click', function() {
                fileInput.value = '';
                fileLabel.textContent = 'No file chosen';
                removeFileBtn.classList.add('hidden');
            });
        }
    });
</script>

</body>
</html>