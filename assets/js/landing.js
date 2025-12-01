// 🔸 Dropdown toggle logic
const profileBtn = document.getElementById("profile-btn");
const dropdownMenu = document.getElementById("dropdown-menu");

profileBtn.addEventListener("click", () => {
  dropdownMenu.classList.toggle("show");
});

// 🔸 Close dropdown kapag pinindot sa labas
window.addEventListener("click", (e) => {
  if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
    dropdownMenu.classList.remove("show");
  }
});
