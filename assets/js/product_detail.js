/**
 * product_detail.js
 * Handles main image swap on thumbnail click
 */

function changeImg(thumbEl, src) {
  // Swap main image
  const mainImg = document.getElementById("main-image");
  if (mainImg) mainImg.src = src;

  // Update active thumb border
  document
    .querySelectorAll(".thumb-item")
    .forEach((el) => el.classList.remove("active"));
  thumbEl.classList.add("active");
}
