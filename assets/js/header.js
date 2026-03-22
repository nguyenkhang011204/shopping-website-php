const toggle = document.querySelector(".menu-toggle");
const nav = document.querySelector(".nav-links");

// Hamburger toggle
toggle.addEventListener("click", () => {
  nav.classList.toggle("active");
});

// Dropdown click on mobile
const dropdowns = document.querySelectorAll(".dropdown > a");

dropdowns.forEach((item) => {
  item.addEventListener("click", function (e) {
    if (window.innerWidth <= 768) {
      e.preventDefault();

      const parentLi = this.parentElement;

      // Close all other open dropdowns
      document.querySelectorAll(".dropdown.active").forEach((el) => {
        if (el !== parentLi) el.classList.remove("active");
      });

      // Toggle this one
      parentLi.classList.toggle("active");
    }
  });
});

// Click outside to close everything
document.addEventListener("click", function (e) {
  if (window.innerWidth <= 768) {
    if (!nav.contains(e.target) && !toggle.contains(e.target)) {
      nav.classList.remove("active");
      document.querySelectorAll(".dropdown.active").forEach((el) => {
        el.classList.remove("active");
      });
    }
  }
});
