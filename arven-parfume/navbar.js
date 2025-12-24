document.addEventListener("DOMContentLoaded", function () {
  // 1. LOGIKA BADGE CART
  const cartData = JSON.parse(localStorage.getItem("arven_cart_v1")) || [];
  const totalQty = cartData.reduce((sum, item) => sum + (item.qty || 0), 0);

  const badge = document.getElementById("cartBadge");
  // Pastikan elemen badge ada di halaman tersebut sebelum diubah
  if (badge) {
    if (totalQty > 0) {
      badge.style.display = "inline-flex"; // Gunakan inline-flex agar angka di tengah
      badge.style.justifyContent = "center";
      badge.style.alignItems = "center";
      badge.innerText = totalQty;
    } else {
      badge.style.display = "none";
    }
  }

  // 2. HEADER SCROLL EFFECT
  window.addEventListener("scroll", function () {
    const header = document.getElementById("siteHeader"); // Pastikan ID header di HTML adalah 'siteHeader'
    // Jika di halaman lain ID-nya cuma 'header', sesuaikan selector ini
    const targetHeader = header || document.querySelector("header");

    if (targetHeader) {
      if (window.scrollY > 50) {
        targetHeader.classList.add("scrolled");
      } else {
        targetHeader.classList.remove("scrolled");
      }
    }
  });
});
