// Toggle the dropdown menu
document.querySelectorAll(".dropdown-toggle").forEach((toggle) => {
  toggle.addEventListener("click", function (e) {
    e.preventDefault();
    const parent = this.parentElement;
    parent.classList.toggle("open");

    // Close other open dropdowns
    document.querySelectorAll(".dropdown").forEach((dropdown) => {
      if (dropdown !== parent) {
        dropdown.classList.remove("open");
      }
    });
  });
});

// Close dropdowns when clicking outside
document.addEventListener("click", function (e) {
  if (!e.target.closest(".dropdown")) {
    document.querySelectorAll(".dropdown").forEach((dropdown) => {
      dropdown.classList.remove("open");
    });
  }
});

// Highlight the active link for dropdown items
const currentUrl = window.location.href;
const navLinks = document.querySelectorAll("#navMenu a");

navLinks.forEach((link) => {
  if (link.href === currentUrl) {
    link.classList.add("active");
  }
});
