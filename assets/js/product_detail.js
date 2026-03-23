/**
 * product_detail.js
 * - Thumbnail swap with fade transition
 * - Size chip selection synced to hidden select
 * - Quantity stepper +/- buttons
 */

// ── Thumbnail swap ────────────────────────────────────────
function changeImg(thumbEl, src) {
  const mainImg = document.getElementById("main-image");
  if (!mainImg) return;

  // Fade out → swap src → fade in
  mainImg.style.transition = "opacity 0.15s ease";
  mainImg.style.opacity = "0";

  setTimeout(() => {
    mainImg.src = src;
    mainImg.style.opacity = "1";
  }, 150);

  // Update active thumb
  document
    .querySelectorAll(".thumb-item")
    .forEach((el) => el.classList.remove("active"));
  thumbEl.classList.add("active");
}

document.addEventListener("DOMContentLoaded", () => {
  // ── Size chips ────────────────────────────────────────
  const chips = document.querySelectorAll(".size-chip:not(.disabled)");
  const sizeSelect = document.getElementById("sizeSelect");

  chips.forEach((chip) => {
    chip.addEventListener("click", () => {
      chips.forEach((c) => c.classList.remove("selected"));
      chip.classList.add("selected");
      if (sizeSelect) sizeSelect.value = chip.dataset.size;
    });
  });

  // ── Quantity stepper ──────────────────────────────────
  const qtyInput = document.getElementById("qtyInput");
  const qtyMinus = document.getElementById("qtyMinus");
  const qtyPlus = document.getElementById("qtyPlus");

  if (qtyInput && qtyMinus && qtyPlus) {
    const max = parseInt(qtyInput.max) || 999;

    qtyMinus.addEventListener("click", () => {
      const val = parseInt(qtyInput.value) || 1;
      if (val > 1) qtyInput.value = val - 1;
    });

    qtyPlus.addEventListener("click", () => {
      const val = parseInt(qtyInput.value) || 1;
      if (val < max) qtyInput.value = val + 1;
    });

    qtyInput.addEventListener("change", () => {
      let val = parseInt(qtyInput.value) || 1;
      if (val < 1) val = 1;
      if (val > max) val = max;
      qtyInput.value = val;
    });
  }

  // ── Add to cart / Buy now actions ─────────────────────
  const addCartBtn = document.querySelector(".add-cart[data-id]");
  const buyNowBtn = document.querySelector(".buy[data-id]");

  const getQuantity = () => {
    if (!qtyInput) return 1;
    const max = parseInt(qtyInput.max) || 999;
    let val = parseInt(qtyInput.value) || 1;
    if (val < 1) val = 1;
    if (val > max) val = max;
    qtyInput.value = val;
    return val;
  };

  const goToCart = (mode) => {
    const sourceBtn = mode === "buy" ? buyNowBtn : addCartBtn;
    if (!sourceBtn) return;

    const id = parseInt(sourceBtn.dataset.id, 10);
    if (!id) return;

    const qty = getQuantity();
    const url = new URL("cart.php", window.location.href);
    url.searchParams.set("action", "add");
    url.searchParams.set("id", String(id));
    url.searchParams.set("qty", String(qty));
    if (mode === "buy") {
      url.searchParams.set("buy_now", "1");
    }

    window.location.href = url.toString();
  };

  if (addCartBtn) {
    addCartBtn.addEventListener("click", () => goToCart("add"));
  }

  if (buyNowBtn) {
    buyNowBtn.addEventListener("click", () => goToCart("buy"));
  }
});
