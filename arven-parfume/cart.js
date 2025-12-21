const STORAGE_KEY = 'arven_cart_v1';

const currency = n =>
  'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

function readCart() {
  return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
}

function writeCart(cart) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
  renderCart();
  updateBadge();
}

function updateBadge() {
  const badge = document.getElementById('cartBadge');
  const count = readCart().reduce((s, i) => s + i.qty, 0);
  badge.style.display = count ? 'inline-flex' : 'none';
  badge.textContent = count;
}

function renderCart() {
  const list = document.getElementById('cartList');
  const cart = readCart();

  if (!cart.length) {
    list.innerHTML = '<p>Keranjang kosong</p>';
    return;
  }

  list.innerHTML = cart.map(item => `
    <div class="cart-item">
      <div class="item-thumb">
        <img src="${item.image}">
      </div>
      <div>
        <div>${item.name}</div>
        <div>${currency(item.price)}</div>
      </div>
      <div>
        ${currency(item.price * item.qty)}
      </div>
    </div>
  `).join('');
}

document.getElementById('clearBtn').onclick = () => {
  if (confirm('Kosongkan keranjang?')) {
    localStorage.removeItem(STORAGE_KEY);
    renderCart();
    updateBadge();
  }
};

document.getElementById('checkoutBtn').onclick = () => {
  alert('Checkout simulasi');
  localStorage.removeItem(STORAGE_KEY);
  renderCart();
  updateBadge();
};

renderCart();
updateBadge();
