const sideMenu = document.querySelector("aside");
const menuBtn = document.querySelector("#menu-btn");
const closeBtn = document.querySelector("#close-btn");
const themeToggler = document.querySelector(".theme-toggler");

// Function to set theme
function setTheme(theme) {
    if (theme === 'dark') {
        document.body.classList.add('dark-theme-variables');
        if (themeToggler) {
            themeToggler.querySelector('span:nth-child(1)').classList.remove('active');
            themeToggler.querySelector('span:nth-child(2)').classList.add('active');
        }
    } else {
        document.body.classList.remove('dark-theme-variables');
        if (themeToggler) {
            themeToggler.querySelector('span:nth-child(1)').classList.add('active');
            themeToggler.querySelector('span:nth-child(2)').classList.remove('active');
        }
    }
}

// Apply theme immediately based on PHP session
setTheme(document.body.dataset.theme);

// Show sidebar
if (menuBtn) {
    menuBtn.addEventListener('click', () => {
        if (sideMenu) sideMenu.style.display = 'block';
    });
}

// Close sidebar
if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        if (sideMenu) sideMenu.style.display = 'none';
    });
}

// Change theme
if (themeToggler) {
    themeToggler.addEventListener('click', () => {
        const isDark = document.body.classList.contains('dark-theme-variables');
        const newTheme = isDark ? 'light' : 'dark';
        
        // Update UI immediately
        setTheme(newTheme);
        
        // Save to localStorage as backup
        localStorage.setItem('theme', newTheme);
        
        // Send to PHP to save in session
        fetch('set_theme.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'theme=' + newTheme
        }).catch(error => {
            console.log('Theme save error:', error);
        });
    });
}

// Delete confirmation
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
});
