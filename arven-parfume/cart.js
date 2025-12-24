// =========================================
// CONFIG & UTILS
// =========================================
const STORAGE_KEY = 'arven_cart_v1';

// Format Rupiah: Rp 1.000.000
const currency = (n) => {
  return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

// =========================================
// DATA MANAGEMENT
// =========================================
function readCart() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch (e) {
    return [];
  }
}

function writeCart(cart) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
  renderCart();
  updateBadge();
}

function getCartCount() {
  return readCart().reduce((s, i) => s + (i.qty || 0), 0);
}

// =========================================
// ACTIONS (Global Functions for HTML onClick)
// =========================================

// Tambah/Kurang jumlah item
window.changeQty = function(id, delta) {
  const cart = readCart();
  const idx = cart.findIndex(i => i.id == id);
  if (idx >= 0) {
    // Min qty 1
    cart[idx].qty = Math.max(1, (cart[idx].qty || 1) + delta);
    writeCart(cart);
  }
};

// Hapus item spesifik
window.removeItem = function(id) {
  const cart = readCart().filter(i => i.id != id);
  writeCart(cart);
};

// Kosongkan seluruh keranjang
function clearCart() {
  localStorage.removeItem(STORAGE_KEY);
  renderCart();
  updateBadge();
}

// =========================================
// UI UPDATES
// =========================================

function updateBadge() {
  const count = getCartCount();
  const badge = document.getElementById('cartBadge');
  
  if (badge) {
    if (count > 0) {
      badge.style.display = 'flex';
      badge.textContent = count;
    } else {
      badge.style.display = 'none';
    }
  }
}

function renderCart() {
  const container = document.getElementById('cartList');
  if (!container) return;

  const cart = readCart();

  // 1. Handle Kosong
  if (!cart.length) {
    container.innerHTML = `
      <div class="empty-state">
        <h3 style="color:#fff; margin-bottom:10px;">Keranjang Anda Kosong</h3>
        <p>Temukan koleksi eksklusif kami sekarang.</p>
        <a href="koleksi.html" class="btn btn-primary" style="display:inline-block; width:auto; margin-top:20px; padding:10px 30px;">Belanja Sekarang</a>
      </div>`;
    
    // Reset Summary
    updateSummary(0);
    return;
  }

  // 2. Render Items
  container.innerHTML = cart.map(item => {
    const subtotal = item.price * item.qty;
    // Gunakan escape untuk mencegah XSS jika nama produk aneh
    const safeName = item.name.replace(/"/g, '&quot;'); 

    return `
      <div class="cart-item">
        <div class="item-thumb">
          ${item.image ? `<img src="${item.image}" alt="${safeName}">` : ''}
        </div>
        
        <div class="item-info">
          <h3>${safeName}</h3>
          <div class="price-row">
            ${currency(item.price)}
          </div>
          
          <div class="qty-controls">
            <button onclick="changeQty('${item.id}', -1)">−</button>
            <input type="text" readonly value="${item.qty}">
            <button onclick="changeQty('${item.id}', 1)">+</button>
          </div>
        </div>

        <div class="item-remove" style="text-align:right">
          <button class="remove-btn" onclick="removeItem('${item.id}')" title="Hapus">✕</button>
          <div class="subtotal" style="margin-top:20px;">${currency(subtotal)}</div>
        </div>
      </div>
    `;
  }).join('');

  // 3. Update Summary
  const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
  updateSummary(subtotal);
}

function updateSummary(subtotal) {
  const tax = subtotal * 0.0; // Pajak 0% (ubah jika perlu)
  const total = subtotal + tax;

  const elSub = document.getElementById('subtotalText');
  const elTax = document.getElementById('taxText');
  const elTot = document.getElementById('totalText');

  if(elSub) elSub.textContent = currency(subtotal);
  if(elTax) elTax.textContent = currency(tax);
  if(elTot) elTot.textContent = currency(total);
}

// =========================================
// INITIALIZATION & LISTENERS
// =========================================

document.addEventListener('DOMContentLoaded', () => {
  // Render awal
  renderCart();
  updateBadge();

  // 1. Header Scroll Effect
  window.addEventListener("scroll", function() {
    const header = document.getElementById("mainHeader");
    if (header) {
      if (window.scrollY > 50) {
        header.classList.add("scrolled");
      } else {
        header.classList.remove("scrolled");
      }
    }
  });

  // 2. Button Listeners
  const clearBtn = document.getElementById('clearBtn');
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      if(confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) clearCart();
    });
  }

  const checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
       const cart = readCart();
       if(cart.length === 0) return alert("Keranjang kosong");
       
       if(confirm("Lanjutkan ke pembayaran?")) {
         alert("Terima kasih! Pesanan Anda sedang diproses.");
         clearCart(); // Kosongkan cart setelah checkout simulasi
       }
    });
  }
});