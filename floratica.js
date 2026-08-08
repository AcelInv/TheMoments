// ═══════════════════════════ GSAP SETUP ═══════════════════════════
// Register semua plugin GSAP hanya sekali di awal — mencegah duplikasi & memory leak
gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

// Konfigurasi global ScrollTrigger untuk performa mobile
ScrollTrigger.config({
  limitCallbacks: true,
  syncInterval: 40
});

// ═══════════════════════════ DATA ═══════════════════════════

const BRANCHES = [
  { id: 1, name: 'The Moments Bandung', addr: 'Bandung, Jawa Barat', phone: '0856-0300-2024', ig: '@TheMoments.Bdg', hours: 'Setiap hari 08.00–20.00', desc: 'Cabang utama The Moments di Kota Bandung.', lat: -6.9348612, lng: 107.6307139, embedId: '0x2e68e7b62d35bf83:0xfd2ed6885690787e' },
  { id: 2, name: 'The Moments Bandung 2', addr: 'Bandung, Jawa Barat', phone: '08783-2767-999', ig: '@TheMoments.Bdg2', hours: 'Setiap hari 09.00–20.00', desc: 'Cabang kedua The Moments di Bandung.', lat: -6.9641536, lng: 107.5638217, embedId: '0x2e68efa0abde3805:0xe7b6ba8d55abc0de' },
  { id: 3, name: 'The Moments Cimahi', addr: 'Cimahi, Jawa Barat', phone: '08597-427-8887', ig: '@TheMoments.Cmh', hours: 'Setiap hari 08.00–19.00', desc: 'Melayani area Cimahi dan sekitarnya.', lat: -6.8736748, lng: 107.5526823, embedId: '0x2e68e5310e53a239:0x5cc2bc9c7f9d81f3' },
  { id: 4, name: 'The Moments Cianjur', addr: 'Cianjur, Jawa Barat', phone: '0822-9999-0696', ig: '@TheMoments.id', hours: 'Setiap hari 08.00–20.00', desc: 'Cabang The Moments di Cianjur.', lat: -6.8111576, lng: 107.140954, embedId: '0x2e68532a98329097:0x7ebb92c9f5dea572' },
  { id: 5, name: 'The Moments Centrum', addr: 'Bandung, Jawa Barat', phone: '0819-4433-4446', ig: '@TheMoments.centrum', hours: 'Setiap hari 09.00–18.00', desc: 'Cabang The Moments Centrum.', lat: -6.744506, lng: 107.0473838, embedId: '0x2e69b3dfff28052b:0x11ad7384b700a3b1' },
  { id: 6, name: 'The Moments Cibiru', addr: 'Bandung, Jawa Barat', phone: '0877-6277-9009', ig: '@TheMoments.cibiru', hours: 'Setiap hari 08.00–19.00', desc: 'Cabang The Moments di Cibiru.', lat: -6.9296552, lng: 107.7122066, embedId: '0x2e68c30018413a07:0x4b53f182d574aa67' },
  { id: 7, name: 'The Moments Sukabumi', addr: 'Sukabumi, Jawa Barat', phone: '08111-242-774', ig: '@TheMoments.smi', hours: 'Setiap hari 08.00–20.00', desc: 'Cabang The Moments di Kota Sukabumi.', lat: -6.9523323, lng: 106.9217769, embedId: '0x2e6849efe7605019:0xf579468067a4e91' },
  { id: 8, name: 'The Moments Bogor', addr: 'Bogor, Jawa Barat', phone: '0858-14-666-999', ig: '@TheMoments.Bgr', hours: 'Setiap hari 08.00–20.00', desc: 'Cabang The Moments di Kota Bogor.', lat: -6.5463787, lng: 106.8065052, embedId: '0x2e69c3a191343345:0x81344d07b5c10b21' },
];

// ═══════════════════════════ STATE ═══════════════════════════
let state = {
  user: null,
  products: [], // Akan diisi dari API Database
  categories: [],
  users: [],    // Akan diisi dari API Database
  cart: [],
  wishlist: [],
  orders: [],
  currentFilter: 'semua',
  showAll: false,
  detailProduct: null,
  detailQty: 1,
  coStep: 1,
  payMethod: 'transfer',
  shipCost: 0,
  promoDiscount: 0,
  editingProdId: null,
};

const PREVIEW_COUNT = 6;
let cartSyncQueue = Promise.resolve();
let cartSyncDebounceTimer = null;
let checkoutSubmitting = false;

// ═══════════════════════════ UTILS ═══════════════════════════
const fmt = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const $ = id => document.getElementById(id);
const validateEmail = email => /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/.test(email);
const esc = str => {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
};

// Semua perubahan data API membawa token CSRF sesi yang diterbitkan oleh index.php.
const nativeFetch = window.fetch.bind(window);
let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
async function refreshCsrfToken() {
  try {
    const response = await nativeFetch('backend/api/csrf.php', {
      credentials: 'same-origin',
      cache: 'no-store'
    });
    const data = await response.json();
    if (data.status === 'success' && typeof data.token === 'string') csrfToken = data.token;
  } catch (error) {
    // Token dari halaman dipertahankan sebagai fallback bila koneksi sementara bermasalah.
  }
  return csrfToken;
}

let csrfReady = refreshCsrfToken();
window.fetch = (input, init = {}) => {
  const request = typeof input === 'string' ? input : input.url;
  const method = String(init.method || (typeof input !== 'string' && input.method) || 'GET').toUpperCase();
  const isApiMutation = method !== 'GET' && method !== 'HEAD' && /^backend\/api\//.test(request);
  if (!isApiMutation) return nativeFetch(input, init);

  const sendWithCsrf = async (retried = false) => {
    await csrfReady;
    const headers = new Headers(init.headers || (typeof input !== 'string' ? input.headers : undefined));
    headers.set('X-CSRF-Token', csrfToken);
    const response = await nativeFetch(input, { ...init, headers, credentials: 'same-origin' });

    // Sesi bisa berubah setelah logout atau login. Respons CSRF 403 tidak pernah
    // memproses perubahan data, sehingga aman menyegarkan token dan mencoba ulang sekali.
    if (!retried && response.status === 403) {
      const body = await response.clone().json().catch(() => null);
      if (body && typeof body.message === 'string' && body.message.includes('Token keamanan tidak valid')) {
        csrfReady = refreshCsrfToken();
        await csrfReady;
        return sendWithCsrf(true);
      }
    }
    return response;
  };

  return sendWithCsrf();
};

function toast(msg, type = '', dur = 2800) {
  const el = $('toastEl');
  if (!el) return;
  el.textContent = msg;
  el.className = `toast show ${type}`;
  clearTimeout(toast._t);
  toast._t = setTimeout(() => {
    el.className = 'toast';
    el.textContent = '';
  }, dur);
}




// ═══════════════════════════ API FETCHERS ═══════════════════════════
async function fetchCategories() {
  try {
    const res = await fetch('backend/api/categories.php');
    const json = await res.json();
    if (json.status === 'success' && json.data) {
      state.categories = json.data;
    } else {
      state.categories = [];
    }
  } catch (e) {
    state.categories = [];
  }
}

function getDeletedProductIds() {
  try {
    const saved = localStorage.getItem('floratica_deleted_product_ids');
    return saved ? JSON.parse(saved) : [];
  } catch (e) {
    return [];
  }
}

function saveDeletedProductId(id) {
  const ids = getDeletedProductIds();
  const num = Number(id);
  if (!ids.includes(num)) {
    ids.push(num);
    localStorage.setItem('floratica_deleted_product_ids', JSON.stringify(ids));
  }
}


async function fetchCart() {
  const saved = localStorage.getItem('floratica_cart');
  if (saved) {
    try { state.cart = JSON.parse(saved); } catch (e) { state.cart = []; }
  }
}

async function fetchWishlist() {
  const saved = localStorage.getItem('floratica_wishlist');
  if (saved) {
    try { state.wishlist = JSON.parse(saved); } catch (e) { state.wishlist = []; }
  }
}

async function fetchUsers() {
  // Daftar pengguna adalah data khusus admin. Jangan meminta endpoint ini untuk
  // pengunjung atau pelanggan, karena respons 401 sebelumnya memicu logout dan
  // mereset sesi/CSRF token tepat sebelum formulir daftar dikirim.
  if (!state.user || state.user.role !== 'admin') {
    state.users = [];
    return;
  }

  try {
    const res = await fetch('backend/api/get_users.php');
    const json = await res.json();
    if (json.status === 'success' && json.data) {
      state.users = json.data;
    } else {
      _initDemoUsers();
    }
  } catch (e) {
    _initDemoUsers();
  }
}

function _initDemoUsers() {
  if (!state.users || state.users.length === 0) {
    state.users = [
      { id: 1, name: 'Admin Floratica', email: 'admin@themoments.id', role: 'admin', phone: '082100000000', created_at: '2025-01-01' },
      { id: 2, name: 'Budi Santoso', email: 'user@demo.com', role: 'user', phone: '081234567890', created_at: '2025-02-14' },
      { id: 3, name: 'Siti Rahma', email: 'siti@demo.com', role: 'user', phone: '081399887766', created_at: '2025-03-20' },
      { id: 4, name: 'Dewi Lestari', email: 'dewi@demo.com', role: 'user', phone: '085712345678', created_at: '2025-05-10' }
    ];
  }
}

async function fetchOrders() {
  try {
    const res = await fetch('backend/api/get_orders.php');
    const json = await res.json();
    if (json.status === 'success' && json.data) {
      state.orders = json.data.map(o => ({
        id: o.id,
        num: o.invoice_number || o.num || 'INV-' + o.id,
        customerName: o.customer_name || o.name || 'Pelanggan',
        customerEmail: o.customer_email || o.email || '',
        total: Number(o.total_amount || o.total || 0),
        status: o.status || 'menunggu',
        payStatus: o.payment_status || o.payStatus || 'belum_bayar',
        payMethod: o.payment_method || o.payMethod || 'qris',
        date: o.created_at ? o.created_at.split(' ')[0] : '2026-08-07',
        items: o.items || [],
        userId: o.user_id || o.userId || 1
      }));
    } else {
      _initDemoOrders();
    }
  } catch (e) {
    _initDemoOrders();
  }
}

function _initDemoOrders() {
  if (!state.orders || state.orders.length === 0) {
    state.orders = [
      { id: 101, num: 'INV-20260801', customerName: 'Budi Santoso', customerEmail: 'budi@demo.com', total: 285000, status: 'selesai', payStatus: 'lunas', payMethod: 'QRIS', date: '2026-08-01', items: [], userId: 2 },
      { id: 102, num: 'INV-20260803', customerName: 'Siti Rahma', customerEmail: 'siti@demo.com', total: 120000, status: 'diproses', payStatus: 'lunas', payMethod: 'Transfer BCA', date: '2026-08-03', items: [], userId: 3 },
      { id: 103, num: 'INV-20260805', customerName: 'Dewi Lestari', customerEmail: 'dewi@demo.com', total: 450000, status: 'dikonfirmasi', payStatus: 'lunas', payMethod: 'QRIS', date: '2026-08-05', items: [], userId: 4 },
      { id: 104, num: 'INV-20260807', customerName: 'Andi Wijaya', customerEmail: 'andi@demo.com', total: 85000, status: 'menunggu', payStatus: 'belum_bayar', payMethod: 'QRIS', date: '2026-08-07', items: [], userId: 5 }
    ];
  }
}

function scrollSection(id) {
  const home = $('homeScreen');
  const wasNotHome = !home.classList.contains('active');

  const doScroll = () => {
    const el = $(id);
    if (!el) return;
    const offset = 80;
    const target = el.getBoundingClientRect().top + window.pageYOffset - offset;
    // Gunakan GSAP ScrollToPlugin untuk animasi scroll yang smooth & dioptimasi
    gsap.to(window, {
      scrollTo: { y: target, autoKill: true },
      duration: 0.7,
      ease: 'power3.inOut'
    });
  };

  if (wasNotHome) {
    // Kembali ke home screen lalu scroll setelah transisi selesai
    transitionToScreen('homeScreen', doScroll);
  } else {
    doScroll();
  }
}

function openModal(id) {
  const el = $(id);
  if (el) {
    el.classList.add('on');
    document.body.style.overflow = 'hidden';
  }
}

function openAuthModal() {
  if (state.user) { openDashboard(); return; }
  resetAuthFields();
  switchAuthTab('login');
  openModal('authModal');
}

function closeModal(id, e) {
  if (e && e.target !== e.currentTarget) return;
  const el = $(id);
  if (el) el.classList.remove('on');
  if (id === 'authModal') {
    resetPasswordVisibility('loginPass');
    resetPasswordVisibility('regPass');
  }
  setTimeout(() => {
    const activeModals = document.querySelectorAll('.modal-bg.on');
    if (activeModals.length === 0) {
      document.body.style.overflow = '';
    }
  }, 50);
}

function toggleMob() {
  $('mobNav').classList.toggle('open');
}

function goHome(scroll = true) {
  transitionToScreen('homeScreen');
  if (scroll) window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Transisi antar screen dengan GSAP.
 * @param {string} targetId - ID screen tujuan
 * @param {Function} [onEnterComplete] - Callback setelah animasi enter selesai
 */
function transitionToScreen(targetId, onEnterComplete) {
  const target = $(targetId);
  const active = document.querySelector('.screen.active');

  if (!target || active === target) {
    if (onEnterComplete) onEnterComplete();
    return;
  }

  // Kill semua ScrollTrigger dari screen yang sedang aktif untuk mencegah memory leak
  if (active) {
    ScrollTrigger.getAll().forEach(st => {
      if (active.contains(st.trigger)) st.kill();
    });
  }

  // Animasi keluar: transform + opacity (GPU-accelerated, hemat resource)
  gsap.to(active, {
    opacity: 0,
    y: -12,
    duration: 0.25,
    ease: 'power2.in',
    clearProps: 'none',   // jangan reset props sebelum selesai
    onComplete: () => {
      if (active) {
        active.classList.remove('active');
        gsap.set(active, { display: 'none', y: 0, opacity: 0 });
      }

      // Siapkan screen tujuan
      gsap.set(target, { display: 'block', opacity: 0, y: 14 });
      target.classList.add('active');

      // Animasi masuk: transform + opacity
      gsap.to(target, {
        opacity: 1,
        y: 0,
        duration: 0.35,
        ease: 'power2.out',
        clearProps: 'transform',  // Bersihkan transform setelah selesai supaya layout normal
        onComplete: () => {
          // Refresh ScrollTrigger agar posisi trigger akurat di screen baru
          ScrollTrigger.refresh();
          if (typeof onEnterComplete === 'function') onEnterComplete();
        }
      });
    }
  });
}

// ═══════════════════════════ AUTH ═══════════════════════════
function toggleDashSidebar() {
  $('dashSidebar').classList.toggle('open');
  $('dashOverlay').classList.toggle('on');
}

// ═══════════════════════════ CUSTOM BOUQUET ═══════════════════════════
let customSelections = {};

function openCustomModal(e) {
  if (e) e.stopPropagation();
  if (!state.user) return openAuthModal();
  customSelections = {};
  const list = $('customFlowersList');
  const flowers = state.products.filter(p => (p.tags && p.tags.includes('Satuan')) || p.db_cat == 3 || p.cat === 'satuan' || (p.slug && p.slug.endsWith('-satuan')));

  if (flowers.length === 0) return toast('Belum ada pilihan bunga satuan', 'rose');

  list.innerHTML = flowers.map(f => {
    const stock = (f.stock !== undefined && f.stock !== null) ? Number(f.stock) : 99999;
    const isOut = stock <= 0;
    return `
    <div class="flower-sel-row" style="display:flex;align-items:center;gap:16px;padding:16px 0;border-bottom:1px solid var(--cream3)">
      <div style="width:50px;height:50px;background:var(--cream2);display:flex;align-items:center;justify-content:center;font-size:12px">Produk</div>
      <div style="flex:1">
        <div style="font-weight:500;font-size:14px;color:var(--ink)">${esc(f.name)}</div>
        <div style="font-size:12px;color:var(--muted)">${fmt(f.price)} / tangkai <span style="font-size:11px;color:${isOut ? 'var(--rose)' : 'var(--sage)'};margin-left:6px">${isOut ? '(Stok Habis)' : `(Stok: ${stock})`}</span></div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <button class="qbtn" onclick="updateCustomQty(${f.id}, -1)">−</button>
        <input type="number" class="qnum-input" id="cQty${f.id}" value="0" min="0" max="${stock}" ${isOut ? 'disabled' : ''} oninput="setCustomQtyInput(${f.id}, this.value)" onblur="validateCustomQtyInput(${f.id}, this.value)">
        <button class="qbtn" onclick="updateCustomQty(${f.id}, 1)">+</button>
      </div>
    </div>
  `;
  }).join('');

  updateCustomPrice();
  openModal('customModal');
}

function updateCustomQty(id, delta) {
  const p = state.products.find(x => Number(x.id) === Number(id));
  if (!p) return;
  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  if (stock <= 0) return toast('Stok bunga ini sedang habis!', 'rose');

  const current = customSelections[id] || 0;
  let next = current + delta;

  if (next < 0) next = 0;
  if (next > stock) {
    next = stock;
    toast(`Stok tidak mencukupi! Maksimal: ${stock}`, 'rose');
  }

  customSelections[id] = next;
  const inputEl = $(`cQty${id}`);
  if (inputEl) inputEl.value = next;
  updateCustomPrice();
}

function setCustomQtyInput(id, val) {
  const p = state.products.find(x => Number(x.id) === Number(id));
  if (!p) return;
  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  if (stock <= 0) return;
  if (val === '' || val === null) return;

  let num = parseInt(val, 10);
  if (isNaN(num) || num < 0) num = 0;
  if (num > stock) {
    num = stock;
    toast(`Stok tidak mencukupi! Maksimal: ${stock}`, 'rose');
    const inputEl = $(`cQty${id}`);
    if (inputEl) inputEl.value = num;
  }

  customSelections[id] = num;
  updateCustomPrice();
}

function validateCustomQtyInput(id, val) {
  const p = state.products.find(x => Number(x.id) === Number(id));
  if (!p) return;
  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  let num = parseInt(val, 10);
  if (isNaN(num) || num < 0) num = 0;
  if (num > stock) num = stock;
  customSelections[id] = num;
  const inputEl = $(`cQty${id}`);
  if (inputEl) inputEl.value = num;
  updateCustomPrice();
}


function updateCustomPrice() {
  const customProduct = state.products.find(p => p.slug === 'custom-bouquet');
  const basePrice = customProduct ? Number(customProduct.price) : 0;
  let total = 0;
  for (const id in customSelections) {
    const p = state.products.find(x => x.id == id);
    if (p) total += p.price * customSelections[id];
  }
  const final = total > 0 ? total + basePrice : 0;
  $('customTotalPrice').textContent = fmt(final);
  $('addCustomBtn').disabled = total === 0;
}

function addCustomToCart() {
  let total = 0;
  let items = [];
  for (const id in customSelections) {
    if (customSelections[id] > 0) {
      const p = state.products.find(x => x.id == id);
      if (p) {
        total += p.price * customSelections[id];
        items.push(`${p.name} (${customSelections[id]}x)`);
      }
    }
  }

  if (items.length === 0) return toast('Pilih bunga dulu!', 'rose');

  const customProduct = state.products.find(p => p.slug === 'custom-bouquet');
  const finalPrice = total + (customProduct ? Number(customProduct.price) : 0);
  const customItem = {
    id: 'custom-' + Date.now(),
    name: 'Buket Custom: ' + items.join(', '),
    price: finalPrice,
    emoji: '',
    qty: 1,
    bg: '#FFF0F0',
    components: { ...customSelections }
  };

  state.cart.push(customItem);
  updateCartUI();
  syncCart();
  closeModal('customModal');
  toast('Buket Custom berhasil ditambah!', 'green');
}

function switchAuthTab(tab) {
  $('loginForm').style.display = tab === 'login' ? '' : 'none';
  $('registerForm').style.display = tab === 'register' ? '' : 'none';
  $('tabLogin').classList.toggle('on', tab === 'login');
  $('tabRegister').classList.toggle('on', tab === 'register');
  resetPasswordVisibility(tab === 'login' ? 'loginPass' : 'regPass');
}

async function doLogin() {
  const email = $('loginEmail').value.trim();
  const pass = $('loginPass').value;
  if (!email || !pass) return toast('Masukkan email dan password!', 'rose');
  if (!validateEmail(email)) return toast('Format email tidak valid (contoh: email@kamu.com)!', 'rose');

  try {
    const res = await fetch('backend/api/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
      body: JSON.stringify({ email, password: pass })
    });
    const data = await res.json();

    if (res.ok && data.status === 'success') {
      loginSuccess(data.user);
    } else {
      // Check demo users if not found in DB (backward compatibility)
      toast(data.message || 'Email atau password salah', 'rose');
    }
  } catch (error) {
    console.error('Login error:', error);
    // Fallback to demo for offline use
    toast('Terjadi kesalahan jaringan', 'rose');
  }
}

function resetAuthFields() {
  const fields = ['loginEmail', 'loginPass', 'regName', 'regEmail', 'regPass', 'regPhone'];
  fields.forEach(f => { if ($(f)) $(f).value = ''; });
  resetPasswordVisibility('loginPass');
  resetPasswordVisibility('regPass');
}

async function doRegister() {
  const name = $('regName').value.trim();
  const email = $('regEmail').value.trim();
  const pass = $('regPass').value;
  const phone = $('regPhone').value.trim();

  if (!name || !email || !pass) return toast('Lengkapi semua field!', 'rose');
  if (!validateEmail(email)) return toast('Format email tidak valid (contoh: email@kamu.com)!', 'rose');
  if (pass.length < 10) return toast('Password minimal 10 karakter', 'rose');

  try {
    const res = await fetch('backend/api/register.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ name, email, password: pass, phone })
    });

    const data = await res.json();

    if (res.ok && data.status === 'success') {
      const newUser = {
        id: data.user.id,
        name: data.user.name,
        email: data.user.email,
        role: (data.user.role === 'customer' || data.user.role === 'pelanggan') ? 'user' : data.user.role,
        phone: data.user.phone
      };
      loginSuccess(newUser);
      toast(data.message, 'green');
    } else {
      toast(data.message || 'Gagal mendaftar', 'rose');
    }
  } catch (error) {
    console.error('Registration error:', error);
    toast('Terjadi kesalahan jaringan atau server mati', 'rose');
  }
}

function demoLogin(role) {
  toast('Gunakan akun yang terdaftar di database untuk masuk.', 'gold');
}

function loginSuccess(user) {
  if (user && user.role === 'pelanggan') {
    user.role = 'user';
  }
  if (user && user.id && typeof user.id === 'string' && !isNaN(parseInt(user.id, 10))) {
    user.id = parseInt(user.id, 10);
  }
  // Reset wishlist sebelum load wishlist akun baru
  state.wishlist = [];
  state.user = user;
  localStorage.setItem('floratica_user', JSON.stringify(user));
  closeModal('authModal');
  updateNavAuth();
  fetchCart();
  fetchOrders();
  if (user.role !== 'admin') {
    fetchWishlist();
  } else {
    renderProducts(); // semua hati putih untuk admin
  }
  toast(`Selamat datang di The Moments, ${user.name.split(' ')[0]}!`, 'green');
}

async function logout() {
  await syncCart();
  try {
    await fetch('backend/api/logout.php', { method: 'POST' });
  } catch (e) {
    console.error('Logout error:', e);
  }
  // Logout menghancurkan sesi PHP lama; segera buat token sesi anonim yang baru
  // agar pengguna dapat masuk atau mendaftar kembali tanpa memuat ulang halaman.
  csrfReady = refreshCsrfToken();
  await csrfReady;
  const wishKey = _wishKey(); // ambil key sebelum user di-null
  state.user = null; state.cart = []; state.wishlist = [];
  localStorage.removeItem('floratica_user');
  if (wishKey) localStorage.removeItem(wishKey); // bersihkan wishlist lokal
  updateNavAuth(); updateCartUI();
  renderProducts(); // reset semua ikon hati ke putih
  goHome();
  toast(' Sampai jumpa!');
}

function updateNavAuth() {
  const u = state.user;
  $('navGuest').style.display = u ? 'none' : '';
  $('navUser').style.display = u ? 'flex' : 'none';
  $('mobLoginBtn').style.display = u ? 'none' : '';
  $('mobDashBtn').style.display = u ? '' : 'none';
  if (u) {
    $('navAvatar').textContent = u.name[0].toUpperCase();
    $('navAvatar').className = 'nav-avatar' + (u.role === 'admin' ? ' admin-av' : '');
    $('navUserName').textContent = u.name.split(' ')[0];
    $('cartNavBtn').style.display = u.role === 'user' ? '' : 'none';
  }
}

// ═══════════════════════════ DASHBOARD ═══════════════════════════
function openDashboard() {
  if (!state.user) { openAuthModal(); return; }
  transitionToScreen('dashScreen');
  window.scrollTo({ top: 0 });
  const u = state.user;
  // Sidebar info
  $('dashAvatar').textContent = u.name[0].toUpperCase();
  $('dashUname').textContent = u.name;
  $('dashUemail').textContent = u.email;
  $('dashRole').textContent = u.role === 'admin' ? 'Admin' : 'User';
  $('dashRole').className = `dash-role ${u.role}`;
  // Toggle nav
  $('userNav').style.display = u.role === 'user' ? '' : 'none';
  $('adminNav').style.display = u.role === 'admin' ? '' : 'none';
  // Profile
  $('profileAv').textContent = u.name[0].toUpperCase();
  $('profileAv').className = 'profile-av' + (u.role === 'admin' ? ' admin-av' : '');
  $('profileName').textContent = u.name;
  $('profileEmail').textContent = u.email;
  $('profileRole').textContent = u.role === 'admin' ? 'Admin' : 'User';
  $('profileRole').className = `dash-role ${u.role}`;
  $('editName').value = u.name;
  $('editEmail').value = u.email;
  $('editPhone').value = u.phone || '';
  // Show correct panel
  showPanel(u.role === 'admin' ? 'adminOverview' : 'overview');
}

function showPanel(name) {
  if (window.innerWidth <= 768) {
    $('dashSidebar').classList.remove('open');
    $('dashOverlay').classList.remove('on');
  }
  document.querySelectorAll('.dash-panel').forEach(p => p.classList.remove('on'));
  document.querySelectorAll('.dash-nav-btn').forEach(b => b.classList.remove('on'));
  const panelMap = {
    overview: 'panelOverview', orders: 'panelOrders', wishlist: 'panelWishlist',
    profile: 'panelProfile', adminOverview: 'panelAdminOverview',
    adminOrders: 'panelAdminOrders', adminProducts: 'panelAdminProducts',
    adminUsers: 'panelAdminUsers', adminReports: 'panelAdminReports'
  };
  const p = $(panelMap[name]);
  if (p) p.classList.add('on');
  // Highlight nav
  document.querySelectorAll('.dash-nav-btn').forEach(b => {
    if (b.textContent.toLowerCase().includes(getNavLabel(name))) b.classList.add('on');
  });
  // Render panel
  if (name === 'overview') { fetchOrders(); renderUserOverview(); }
  else if (name === 'orders') { fetchOrders(); renderOrdersPanel(); }
  else if (name === 'wishlist') { fetchWishlist().then(renderWishlistPanel); }
  else if (name === 'adminOverview') { fetchOrders(); fetchUsers(); renderAdminOverview(); }
  else if (name === 'adminOrders') { fetchOrders(); renderAdminOrders(); }
  else if (name === 'adminProducts') renderAdminProducts();
  else if (name === 'adminUsers') { fetchUsers().then(renderAdminUsers); }
  else if (name === 'adminReports') fetchAndRenderReports();
}

function getNavLabel(name) {
  const m = { overview: 'ringkasan', orders: 'pesanan', wishlist: 'wishlist', profile: 'profil', adminOverview: 'dashboard', adminOrders: 'pesanan', adminProducts: 'produk', adminUsers: 'pengguna', adminReports: 'laporan' };
  return m[name] || '';
}

// ── USER Panels ──
function renderUserOverview() {
  const u = state.user;
  $('overviewGreeting').textContent = `Halo, ${u.name.split(' ')[0]}!`;
  const myOrders = state.orders.filter(o => Number(o.userId) === Number(u.id));
  const totalSpend = myOrders.reduce((s, o) => s + o.total, 0);
  $('uStatOrders').textContent = myOrders.length;
  $('uStatWish').textContent = state.wishlist.length;
  $('uStatSpend').textContent = myOrders.length > 0 ? fmt(totalSpend) : 'Rp 0';
  const recent = myOrders.slice(-2).reverse();
  $('recentOrders').innerHTML = recent.length ? recent.map(renderOrderCard).join('') : '<div style="text-align:center;padding:32px;color:var(--muted)"><p>Belum ada pesanan</p></div>';
}

function renderOrdersPanel() {
  const myOrders = state.orders.filter(o => Number(o.userId) === Number(state.user.id)).reverse();
  $('ordersList').innerHTML = myOrders.length ? myOrders.map(renderOrderCard).join('') : '';
  $('ordersEmpty').style.display = myOrders.length ? 'none' : 'block';
}

function renderOrderCard(order) {
  const itemsHtml = order.items.map(i => `
    <div class="oi-row">
      <span class="oi-name">${esc(i.name)}</span>
      <span class="oi-price">${fmt(i.price)}</span>
    </div>`).join('');
  const canCancel = ['menunggu', 'dikonfirmasi', 'pending', 'confirmed'].includes(order.status);
  const canReview = String(order.status) === 'selesai';
  const reviewActions = canReview && order.items.length
    ? `<div class="order-review-actions"><span>Bagikan pengalaman Anda</span>${order.items.map(item => `<button type="button" class="review-order-btn" onclick="openReviewModal(${Number(item.product_id)})">Beri ulasan: ${esc(item.name)}</button>`).join('')}</div>`
    : '';
  return `
    <div class="order-card">
      <div class="order-head">
        <div>
          <div class="order-num">${order.num}</div>
          <div class="order-date">${order.date} · ${order.payMethod}</div>
        </div>
        <span class="status-chip ${order.status}">${statusLabel(order.status)}</span>
      </div>
      <div class="order-body">
        <div class="order-items-mini">${itemsHtml}</div>
        ${reviewActions}
        <div class="order-total-row">
          <div>
            <span style="font-size:12px;color:var(--muted)">Total Pembayaran</span>
            <div style="font-weight:700;font-size:16px;color:var(--sage)">${fmt(order.total)}</div>
          </div>
          ${canCancel ? `<button class="cancel-btn" onclick="cancelOrder('${order.id}')">Batalkan</button>` : `<span style="font-size:12px;color:var(--muted)">${order.shippingCity || '—'}</span>`}
        </div>
      </div>
    </div>`;
}

function statusLabel(s) { const m = { menunggu: ' Menunggu', dikonfirmasi: 'Dikonfirmasi', diproses: 'Diproses', selesai: ' Selesai', dibatalkan: 'Dibatalkan', pending: 'Pending', confirmed: 'Dikonfirmasi', processing: 'Diproses', delivered: 'Terkirim', cancelled: 'Dibatalkan', paid: 'Lunas', success: 'Lunas', unpaid: 'Belum Bayar', belum_bayar: 'Belum Bayar', lunas: 'Lunas', gagal: 'Gagal' }; return m[s] || s }

function openReviewModal(productId) {
  if (!state.user) return openAuthModal();
  if (state.user.role === 'admin') return toast('Admin tidak dapat mengirim ulasan.', 'rose');
  const product = state.products.find(item => Number(item.id) === Number(productId));
  $('reviewProductId').value = String(productId);
  $('reviewProductName').textContent = product?.name || 'Produk pilihan';
  $('reviewComment').value = '';
  setReviewRating(0);
  openModal('reviewModal');
}

function setReviewRating(rating) {
  const normalized = Math.max(0, Math.min(5, Number(rating) || 0));
  document.querySelectorAll('.review-star-btn').forEach(button => {
    const active = Number(button.dataset.rating) <= normalized;
    button.classList.toggle('on', active);
    button.setAttribute('aria-pressed', String(active));
  });
  $('reviewModal').dataset.rating = String(normalized);
  $('reviewRatingNote').textContent = normalized ? `Rating ${normalized} dari 5` : 'Pilih rating untuk produk ini.';
}

async function submitReview() {
  const productId = Number($('reviewProductId').value);
  const rating = Number($('reviewModal').dataset.rating || 0);
  const comment = $('reviewComment').value.trim();
  if (!productId || rating < 1) return toast('Pilih rating terlebih dahulu.', 'rose');

  try {
    const res = await fetch('backend/api/save_review.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId, rating, comment })
    });
    const data = await res.json();
    if (!res.ok || data.status !== 'success') throw new Error(data.message || 'Ulasan gagal disimpan.');
    closeModal('reviewModal');
    await Promise.all([fetchTestimonials(), fetchProducts()]);
    toast('Ulasan berhasil disimpan.', 'green');
  } catch (error) {
    toast(error.message || 'Ulasan gagal disimpan.', 'rose');
  }
}

async function cancelOrder(id) {
  // Tampilkan custom modal konfirmasi
  openModal('confirmCancelModal');
  $('confirmCancelBtn').onclick = async function () {
    closeModal('confirmCancelModal');
    try {
      const res = await fetch('backend/api/update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status: 'dibatalkan' })
      });
      const data = await res.json();
      if (data.status === 'success') {
        const o = state.orders.find(x => Number(x.id) === Number(id));
        if (o) o.status = 'dibatalkan';
        renderOrdersPanel(); renderUserOverview();
        toast('Pesanan dibatalkan', 'rose');
      }
    } catch (e) { console.error('Cancel order error:', e); }
  };
}

function renderWishlistPanel() {
  const list = state.wishlist;
  const matched = list
    .map(id => state.products.find(x => Number(x.id) === Number(id)))
    .filter(Boolean);

  const isEmpty = matched.length === 0;
  $('wishEmpty').style.display = isEmpty ? 'block' : 'none';
  $('wishlistGrid').innerHTML = isEmpty ? '' : matched.map(p => prodCardHTML(p, true)).join('');

  // Animasi langsung tanpa ScrollTrigger (kartu sudah in-view di dalam dashboard)
  if (!isEmpty) {
    $('wishlistGrid').querySelectorAll('.reveal').forEach((el, i) => {
      gsap.fromTo(el,
        { opacity: 0, y: 20 },
        { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out', delay: i * 0.06, clearProps: 'transform' }
      );
    });
  }
}


async function saveProfile() {
  const n = $('editName').value.trim();
  const ph = $('editPhone').value.trim();
  if (!n) return toast('Nama tidak boleh kosong', 'rose');

  try {
    const res = await fetch('backend/api/update_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: state.user.id, name: n, phone: ph })
    });
    const data = await res.json();
    if (data.status === 'success') {
      state.user.name = n; state.user.phone = ph;
      localStorage.setItem('floratica_user', JSON.stringify(state.user));
      updateNavAuth(); openDashboard();
      toast('Profil berhasil disimpan!', 'green');
    } else {
      toast('' + data.message, 'rose');
    }
  } catch (e) {
    console.error('Save profile error:', e);
    toast('Kesalahan jaringan', 'rose');
  }
}

// ── ADMIN Panels ──
function renderAdminOverview() {
  $('aStat1').textContent = state.orders.length;
  const rev = state.orders.filter(o => o.status !== 'cancelled').reduce((s, o) => s + o.total, 0);
  $('aStat2').textContent = rev > 0 ? fmt(rev) : 'Rp 0';
  $('aStat3').textContent = state.users.length;
  $('aStat4').textContent = state.products.length;
  // Orders dari API sudah ORDER BY created_at DESC, ambil 5 pertama (terbaru)
  const recent = state.orders.slice(0, 5);
  $('adminRecentOrdersBody').innerHTML = recent.map(o => `
    <tr>
      <td style="font-weight:600">${esc(o.num)}</td>
      <td>${esc(o.customerName)}</td>
      <td>${fmt(o.total)}</td>
      <td><span class="status-chip ${o.status}">${statusLabel(o.status)}</span></td>
      <td style="color:var(--muted)">${esc(o.date)}</td>
    </tr>`).join('') || '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">Belum ada pesanan</td></tr>';
}

function renderAdminOrders() {
  const q = ($('orderSearch') || {}).value || '';
  const orders = state.orders.filter(o =>
    !q || o.num.toLowerCase().includes(q.toLowerCase()) || o.customerName.toLowerCase().includes(q.toLowerCase())
  );
  $('adminOrdersBody').innerHTML = orders.map(o => `
    <tr>
      <td style="font-weight:600;font-size:13px">${esc(o.num)}</td>
      <td>${esc(o.customerName)}<div style="font-size:11px;color:var(--muted)">${esc(o.customerEmail)}</div></td>
      <td>${o.items.length} item</td>
      <td style="font-weight:600;color:var(--sage)">${fmt(o.total)}</td>
      <td>
        <select style="font-size:12px;border:1.5px solid var(--cream3);border-radius:8px;padding:4px 8px;background:#fff" onchange="changePayStatus('${o.id}',this.value)">
          <option value="belum_bayar" ${o.payStatus === 'belum_bayar' ? 'selected' : ''}>Belum Bayar</option>
          <option value="lunas" ${o.payStatus === 'lunas' ? 'selected' : ''}>Lunas</option>
          <option value="gagal" ${o.payStatus === 'gagal' ? 'selected' : ''}>Gagal</option>
        </select>
      </td>
      <td>
        <select style="font-size:12px;border:1.5px solid var(--cream3);border-radius:8px;padding:4px 8px;background:#fff" onchange="changeOrderStatus('${o.id}',this.value)">
          ${['menunggu', 'dikonfirmasi', 'diproses', 'selesai', 'dibatalkan'].map(s => `<option value="${s}" ${o.status === s ? 'selected' : ''}>${statusLabel(s)}</option>`).join('')}
        </select>
      </td>
    </tr>`).join('') || '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">Tidak ada pesanan</td></tr>';
}

async function changeOrderStatus(id, status) {
  try {
    const res = await fetch('backend/api/update_order_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, status })
    });
    const data = await res.json();
    if (data.status === 'success') {
      const o = state.orders.find(x => Number(x.id) === Number(id));
      if (o) { o.status = status; if (status === 'selesai') o.payStatus = 'lunas'; }
      toast(`Status diperbarui: ${statusLabel(status)}`, 'green');
      renderAdminOrders(); renderAdminOverview();
    } else {
      toast('Gagal update status: ' + data.message, 'rose');
    }
  } catch (e) {
    console.error('Update status error:', e);
    toast('Kesalahan jaringan', 'rose');
  }
}

async function changePayStatus(id, status) {
  try {
    const res = await fetch('backend/api/update_payment_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, status })
    });
    const data = await res.json();
    if (data.status === 'success') {
      const o = state.orders.find(x => Number(x.id) === Number(id));
      if (o) o.payStatus = status;
      toast(`Status pembayaran: ${status === 'lunas' ? 'Lunas' : status === 'belum_bayar' ? 'Belum Bayar' : 'Gagal'}`, 'green');
      renderAdminOrders(); renderAdminOverview();
    } else {
      toast('Gagal update: ' + data.message, 'rose');
    }
  } catch (e) {
    console.error('Update pay status error:', e);
    toast('Kesalahan jaringan', 'rose');
  }
}

function ensureAdminProductsHeader() {
  let button = $('addProductBtn');
  if (!button) {
    const tableHead = document.querySelector('#panelAdminProducts .admin-products-table-head');
    if (!tableHead) return;
    button = document.createElement('button');
    button.id = 'addProductBtn';
    button.className = 'add-prod-btn';
    button.type = 'button';
    button.textContent = '+ Tambah Produk';
    tableHead.append(button);
  }
  button.style.setProperty('display', 'inline-flex', 'important');
  button.onclick = openAddProduct;
}

function renderAdminProducts() {
  ensureAdminProductsHeader();
  // Urutkan produk: terbaru (id terbesar) di atas — LIFO
  const sortedProds = [...state.products].sort((a, b) => b.id - a.id);
  const addProductRow = `
    <tr class="admin-products-action-row">
      <td colspan="6">
        <div class="admin-products-table-head">
          <span>Kelola Produk</span>
          <button class="add-prod-btn" type="button" onclick="openAddProduct()">+ Tambah Produk</button>
        </div>
      </td>
    </tr>`;
  $('adminProdsBody').innerHTML = addProductRow + sortedProds.map(p => {
    const imgStr = (p.image_url || p.img)
      ? `<img src="${esc(p.image_url || p.img)}" alt="${esc(p.name)}" style="width:28px; height:28px; object-fit:cover; border-radius:6px; vertical-align:middle; margin-right:8px; display:inline-block;">`
      : '';
    return `
    <tr>
      <td>${imgStr} <strong style="font-size:13px">${esc(p.name)}</strong></td>
      <td style="text-transform:capitalize">${esc(p.cat)}</td>
      <td style="color:var(--sage);font-weight:600">${fmt(p.price)}</td>
      <td>${p.stock !== undefined && p.stock !== null ? p.stock : 100}</td>
      <td>${p.badge ? `<span class="status-chip ${p.badge === 'sale' ? 'cancelled' : p.badge === 'new' ? 'processing' : 'confirmed'}">${p.badge.toUpperCase()}</span>` : '—'}</td>
      <td>
        <div class="action-btns">
          <button class="ab edit" onclick="openEditProduct(${p.id})">Edit</button>
          <button class="ab del" onclick="deleteProduct(${p.id})">Hapus</button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

function renderAdminUsers() {
  const users = state.users;
  $('adminUsersBody').innerHTML = users.map(u => `
    <tr>
      <td style="font-weight:500">${esc(u.name)}</td>
      <td style="color:var(--muted);font-size:12px">${esc(u.email)}</td>
      <td><span class="status-chip ${u.role === 'admin' ? 'cancelled' : 'confirmed'}">${u.role}</span></td>
      <td>${state.orders.filter(o => o.userId === u.id).length}</td>
      <td style="color:var(--muted);font-size:12px">${u.created_at ? u.created_at.split(' ')[0] : '2025-01-01'}</td>
      <td>
        <div class="action-btns">
          <button class="ab view" onclick="toast('Detail: ${esc(u.name)} — ${esc(u.phone)}','gold')">Lihat</button>
          ${u.role !== 'admin' ? `<button class="ab del" onclick="deleteUser('${u.id}')">Hapus</button>` : ''}
        </div>
      </td>
    </tr>`).join('');
}

async function deleteUser(uid) {
  if (uid === state.user.id) return toast('Tidak bisa hapus diri sendiri', 'rose');
  if (confirm('Yakin ingin menghapus user ini?')) {
    try {
      const res = await fetch('backend/api/delete_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: uid })
      });
      const data = await res.json();
      if (res.ok && data.status === 'success') {
        toast(data.message, 'green');
        fetchUsers().then(renderAdminUsers);
        renderAdminOverview();
      } else {
        toast(data.message, 'rose');
      }
    } catch (error) {
      console.error('Delete error:', error);
      toast('Gagal menghubungi server', 'rose');
    }
  }
}


let _reportData = [];
let _trendYear = 2026;
let _monthlyData = [];

const SEASONAL_EVENTS = [
  { monthIdx: 1, name: "Valentine's Day", date: "14 Feb", color: "#E63946", desc: "Stok Mawar Merah & Buket Romantis +150%. Siapkan pita & custom box." },
  { monthIdx: 4, name: "Hari Ibu Intl & Wisuda Awal", date: "Mei", color: "#457B9D", desc: "Bunga Matahari, Lily & Buket Wisuda. Siapkan boneka & pita kelulusan." },
  { monthIdx: 6, name: "Musim Pernikahan (Wedding)", date: "Juli", color: "#9B5DE5", desc: "Buket Wedding Eksklusif & Mawar Putih Import. Pre-order H-14." },
  { monthIdx: 9, name: "Wisuda Gelombang II", date: "Okt - Nov", color: "#2A9D8F", desc: "Buket Kombinasi & Mawar Pink. Permintaan produk custom meningkat." },
  { monthIdx: 11, name: "Hari Ibu Nasional & Akhir Tahun", date: "22 Des", color: "#F4A261", desc: "Lonjakan Anggrek Bulan, Mawar & Buket Kasih Ibu +200%." }
];

const MONTH_NAMES = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function _kpiCard(label, val, cls = 'green') {
  return `<div class="rep-kpi-card">
    <div class="rep-kpi-icon ${cls}"></div>
    <div>
      <div class="rep-kpi-val">${val}</div>
      <div class="rep-kpi-label">${label}</div>
    </div>
  </div>`;
}

async function fetchAndRenderReports() {
  try {
    const res = await fetch('backend/api/get_reports.php');
    const json = await res.json();
    if (json.status === 'success' && json.data) {
      _reportData = json.data;
    } else _reportData = [];
  } catch (e) {
    _reportData = [];
  }
  switchReport('sales');
}

function switchReport(tab) {
  ['sales', 'finance', 'trend'].forEach(t => {
    const el = $('rep-' + t);
    const btn = $('rtab-' + t);
    if (el) el.style.display = t === tab ? '' : 'none';
    if (btn) btn.classList.toggle('on', t === tab);
  });
  if (tab === 'sales') renderRepSales();
  if (tab === 'finance') renderRepFinance();
  if (tab === 'trend') initTrendTab();
}

function renderRepSales() {
  const d = _reportData;
  const completed = d.filter(o => o.status === 'selesai');
  const revenue = completed.reduce((acc, o) => acc + Number(o.total || o.total_amount || 0), 0);

  if ($('repSalesKpi')) {
    $('repSalesKpi').innerHTML =
      _kpiCard('Total Pesanan', d.length, 'blue') +
      _kpiCard('Pesanan Selesai', completed.length, 'green') +
      _kpiCard('Pendapatan Selesai', fmt(revenue), 'rose');
  }

  const statuses = ['menunggu', 'dikonfirmasi', 'diproses', 'selesai', 'dibatalkan'];
  const statusColors = { menunggu: 'gold', dikonfirmasi: 'blue', diproses: 'blue', selesai: 'green', dibatalkan: 'rose' };
  const counts = {};
  statuses.forEach(s => counts[s] = d.filter(o => o.status === s).length);
  const maxCount = Math.max(...Object.values(counts), 1);

  if ($('repSalesChart')) {
    $('repSalesChart').innerHTML = statuses.map(s => {
      const cnt = counts[s];
      const pct = Math.round((cnt / maxCount) * 100);
      return `<div class="rep-bar-item">
        <div class="rep-bar-label">${statusLabel(s)}</div>
        <div class="rep-bar-track">
          <div class="rep-bar-fill ${statusColors[s] || 'green'}" style="width:${pct}%"></div>
        </div>
        <div class="rep-bar-count">${cnt}</div>
      </div>`;
    }).join('');
  }

  if ($('repSalesBody')) {
    $('repSalesBody').innerHTML = completed.length
      ? completed.map(o => `<tr>
          <td style="font-weight:600;font-size:13px">${o.num || o.invoice_number || '—'}</td>
          <td>${esc(o.customer_name || o.customerName || 'Pelanggan')}</td>
          <td>${(o.items || []).length || 1} item</td>
          <td style="font-weight:600;color:var(--sage)">${fmt(o.total || o.total_amount || 0)}</td>
          <td style="color:var(--muted);font-size:12px">${o.date || (o.created_at ? o.created_at.split(' ')[0] : '—')}</td>
        </tr>`).join('')
      : '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">Belum ada pesanan selesai</td></tr>';
  }
}

function renderRepFinance() {
  const d = _reportData;
  const paid = d.filter(o => o.payStatus === 'lunas' || o.payStatus === 'paid' || o.payStatus === 'success' || o.status === 'selesai');
  const unpaid = d.filter(o => (o.payStatus === 'belum_bayar' || o.payStatus === 'unpaid') && o.status !== 'selesai' && o.status !== 'dibatalkan');
  const totalPaid = paid.reduce((acc, o) => acc + Number(o.total || o.total_amount || 0), 0);

  if ($('repFinKpi')) {
    $('repFinKpi').innerHTML =
      _kpiCard('Pembayaran Lunas', paid.length, 'green') +
      _kpiCard('Belum Dibayar', unpaid.length, 'gold') +
      _kpiCard('Total Diterima', fmt(totalPaid), 'rose');
  }

  if ($('repFinChart')) {
    const totalOrders = d.length || 1;
    const paidPct = Math.round((paid.length / totalOrders) * 100);
    const unpaidPct = Math.round((unpaid.length / totalOrders) * 100);
    const failed = d.filter(o => o.payStatus === 'gagal' || o.status === 'dibatalkan');
    const failedPct = Math.round((failed.length / totalOrders) * 100);

    $('repFinChart').innerHTML = `
      <div class="rep-bar-item">
        <div class="rep-bar-label">Lunas</div>
        <div class="rep-bar-track"><div class="rep-bar-fill green" style="width:${paidPct}%"></div></div>
        <div class="rep-bar-count">${paid.length}</div>
      </div>
      <div class="rep-bar-item">
        <div class="rep-bar-label">Belum Bayar</div>
        <div class="rep-bar-track"><div class="rep-bar-fill gold" style="width:${unpaidPct}%"></div></div>
        <div class="rep-bar-count">${unpaid.length}</div>
      </div>
      <div class="rep-bar-item">
        <div class="rep-bar-label">Gagal / Dibatalkan</div>
        <div class="rep-bar-track"><div class="rep-bar-fill rose" style="width:${failedPct}%"></div></div>
        <div class="rep-bar-count">${failed.length}</div>
      </div>
    `;
  }

  if ($('repFinBody')) {
    $('repFinBody').innerHTML = d.length
      ? d.map(o => `<tr>
          <td style="font-weight:600;font-size:13px">${o.num || o.invoice_number || '—'}</td>
          <td>${esc(o.customer_name || o.customerName || 'Pelanggan')}</td>
          <td style="font-weight:600;color:var(--sage)">${fmt(o.total || o.total_amount || 0)}</td>
          <td><span class="status-chip ${o.payStatus === 'lunas' || o.status === 'selesai' ? 'confirmed' : 'cancelled'}">${statusLabel(o.payStatus || 'belum_bayar')}</span></td>
          <td style="text-transform:uppercase;font-size:12px">${o.payMethod || 'QRIS'}</td>
          <td style="color:var(--muted);font-size:12px">${o.date || (o.created_at ? o.created_at.split(' ')[0] : '—')}</td>
        </tr>`).join('')
      : '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Tidak ada data pembayaran</td></tr>';
  }
}

// ── SEASONAL TREND FUNCTIONS ──
function initTrendTab() {
  loadSalesTrend(_trendYear);
}

async function loadSalesTrend(year) {
  _trendYear = parseInt(year) || 2026;
  try {
    const res = await fetch(`backend/api/get_sales_trend.php?year=${_trendYear}`);
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message);
    _monthlyData = json.monthly.map(row => ({
      month: row.nama_bulan,
      monthIdx: Number(row.bulan) - 1,
      jumlah_pesanan: Number(row.jumlah_pesanan),
      jumlah_item: Number(row.jumlah_item),
      total_penjualan: Number(row.total_penjualan)
    }));
    const select = $('trendYearSelect');
    if (select) select.innerHTML = json.available_years.map(y => `<option value="${y}" ${Number(y) === _trendYear ? 'selected' : ''}>Tahun ${y}</option>`).join('');
    window._topProducts = json.top_products || [];
  } catch (error) {
    _monthlyData = [];
    window._topProducts = [];
    toast('Tren penjualan belum dapat dimuat.', 'rose');
  }

  renderTrendKpi();
  renderTrendChart();
  renderTrendInsights();
  renderTrendTopProducts();
}

function renderTrendKpi() {
  if (!$('trendKpi')) return;
  const totalOrders = _monthlyData.reduce((acc, m) => acc + m.jumlah_pesanan, 0);
  const totalRev = _monthlyData.reduce((acc, m) => acc + m.total_penjualan, 0);

  const peakMonth = [..._monthlyData].sort((a, b) => b.total_penjualan - a.total_penjualan)[0];

  $('trendKpi').innerHTML =
    _kpiCard('Total Omset ' + _trendYear, fmt(totalRev), 'rose') +
    _kpiCard('Total Pesanan', totalOrders, 'green') +
    _kpiCard('Puncak Lonjakan', peakMonth.month + ' (' + fmt(peakMonth.total_penjualan) + ')', 'gold');
}

function renderTrendChart() {
  const canvas = $('trendCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  // Handle High DPI displays
  const dpr = window.devicePixelRatio || 1;
  const width = 900;
  const height = 420;
  canvas.width = width * dpr;
  canvas.height = height * dpr;
  canvas.style.width = width + 'px';
  canvas.style.height = height + 'px';
  ctx.scale(dpr, dpr);

  ctx.clearRect(0, 0, width, height);

  const metric = ($('trendMetricSelect') ? $('trendMetricSelect').value : 'jumlah_pesanan');
  const values = _monthlyData.map(m => m[metric]);
  const maxValue = Math.max(...values, 1) * 1.15;

  const padLeft = 70;
  const padRight = 40;
  const padTop = 60;
  const padBottom = 50;
  const chartW = width - padLeft - padRight;
  const chartH = height - padTop - padBottom;

  // 1. Draw Grid lines & Y-Axis labels
  ctx.strokeStyle = '#E0DDD5';
  ctx.lineWidth = 1;
  ctx.fillStyle = '#8E7F74';
  ctx.font = '11px Jost, sans-serif';
  ctx.textAlign = 'right';

  const gridSteps = 4;
  for (let i = 0; i <= gridSteps; i++) {
    const yVal = (maxValue / gridSteps) * i;
    const yPos = padTop + chartH - (chartH * (i / gridSteps));

    ctx.beginPath();
    ctx.setLineDash([4, 4]);
    ctx.moveTo(padLeft, yPos);
    ctx.lineTo(padLeft + chartW, yPos);
    ctx.stroke();
    ctx.setLineDash([]);

    let labelStr = yVal.toLocaleString('id-ID');
    if (metric === 'total_penjualan') {
      if (yVal >= 1000000) labelStr = (yVal / 1000000).toFixed(1) + ' Jt';
      else labelStr = (yVal / 1000).toFixed(0) + ' Rb';
    }
    ctx.fillText(labelStr, padLeft - 10, yPos + 4);
  }

  // Calculate X coordinates for 12 months
  const points = _monthlyData.map((m, idx) => {
    const x = padLeft + (chartW / 11) * idx;
    const y = padTop + chartH - ((m[metric] / maxValue) * chartH);
    return { x, y, val: m[metric], month: m.month, monthIdx: idx };
  });

  // 2. Draw Seasonal Event Vertical Bands & Markers
  SEASONAL_EVENTS.forEach(ev => {
    const pt = points[ev.monthIdx];
    if (!pt) return;

    // Highlight vertical line
    ctx.beginPath();
    ctx.setLineDash([3, 3]);
    ctx.strokeStyle = ev.color;
    ctx.lineWidth = 1.5;
    ctx.moveTo(pt.x, padTop - 15);
    ctx.lineTo(pt.x, padTop + chartH);
    ctx.stroke();
    ctx.setLineDash([]);

    // Event Badge pill on top
    ctx.fillStyle = ev.color;
    ctx.beginPath();
    ctx.roundRect(pt.x - 42, padTop - 45, 84, 24, 12);
    ctx.fill();

    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 10px Jost, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(ev.date, pt.x, padTop - 29);
  });

  // 3. Draw Line Gradient Fill
  const grad = ctx.createLinearGradient(0, padTop, 0, padTop + chartH);
  grad.addColorStop(0, 'rgba(93, 91, 58, 0.25)');
  grad.addColorStop(1, 'rgba(93, 91, 58, 0.01)');

  ctx.beginPath();
  ctx.moveTo(points[0].x, padTop + chartH);
  points.forEach((pt, i) => {
    if (i === 0) ctx.lineTo(pt.x, pt.y);
    else {
      const prev = points[i - 1];
      const cx = (prev.x + pt.x) / 2;
      ctx.bezierCurveTo(cx, prev.y, cx, pt.y, pt.x, pt.y);
    }
  });
  ctx.lineTo(points[points.length - 1].x, padTop + chartH);
  ctx.closePath();
  ctx.fillStyle = grad;
  ctx.fill();

  // 4. Draw Main Smooth Line Chart
  ctx.beginPath();
  ctx.lineWidth = 3;
  ctx.strokeStyle = '#5D5B3A';
  points.forEach((pt, i) => {
    if (i === 0) ctx.moveTo(pt.x, pt.y);
    else {
      const prev = points[i - 1];
      const cx = (prev.x + pt.x) / 2;
      ctx.bezierCurveTo(cx, prev.y, cx, pt.y, pt.x, pt.y);
    }
  });
  ctx.stroke();

  // 5. Draw Points & Month X-labels
  ctx.textAlign = 'center';
  points.forEach(pt => {
    // X label
    ctx.fillStyle = '#1A1512';
    ctx.font = '500 12px Jost, sans-serif';
    ctx.fillText(pt.month, pt.x, padTop + chartH + 24);

    // Outer circle
    const isEvent = SEASONAL_EVENTS.some(ev => ev.monthIdx === pt.monthIdx);
    ctx.beginPath();
    ctx.arc(pt.x, pt.y, isEvent ? 7 : 5, 0, Math.PI * 2);
    ctx.fillStyle = isEvent ? '#8A5A3C' : '#5D5B3A';
    ctx.fill();

    // Inner white dot
    ctx.beginPath();
    ctx.arc(pt.x, pt.y, isEvent ? 3 : 2, 0, Math.PI * 2);
    ctx.fillStyle = '#FFFFFF';
    ctx.fill();

    // Value text above point
    ctx.fillStyle = '#1A1512';
    ctx.font = '600 11px Jost, sans-serif';
    let valStr = pt.val.toLocaleString('id-ID');
    if (metric === 'total_penjualan') valStr = (pt.val / 1000000).toFixed(1) + 'M';
    ctx.fillText(valStr, pt.x, pt.y - 12);
  });

  renderTrendLegend();
}

function renderTrendLegend() {
  if (!$('trendLegend')) return;
  $('trendLegend').innerHTML = `
    <div class="trend-legend-item">
      <div class="trend-legend-line" style="background:#5D5B3A"></div>
      <span>Garis Tren Penjualan</span>
    </div>
    ${SEASONAL_EVENTS.map(ev => `
      <div class="trend-legend-item">
        <div class="trend-legend-dot" style="background:${ev.color}"></div>
        <span>${ev.name} (${ev.date})</span>
      </div>
    `).join('')}
  `;
}

function renderTrendInsights() {
  if (!$('trendInsights')) return;
  const peak = [..._monthlyData].sort((a, b) => b.total_penjualan - a.total_penjualan)[0];
  const lowest = [..._monthlyData].sort((a, b) => a.total_penjualan - b.total_penjualan)[0];

  const nowMonth = new Date().getMonth();
  let upcoming = SEASONAL_EVENTS.find(ev => ev.monthIdx >= nowMonth) || SEASONAL_EVENTS[0];

  $('trendInsights').innerHTML = `
    <div class="trend-insight-card peak">
      <div class="trend-insight-title">Puncak Penjualan Tertinggi</div>
      <div class="trend-insight-desc">Bulan ${peak.month} mencatat omset terbanyak mencapai ${fmt(peak.total_penjualan)} (${peak.jumlah_pesanan} pesanan).</div>
      <div class="trend-insight-value">${peak.month} — High Demand</div>
    </div>
    <div class="trend-insight-card upcoming">
      <div class="trend-insight-title">Persiapan Momen Mendatang</div>
      <div class="trend-insight-desc"><strong>${upcoming.name} (${upcoming.date})</strong>: ${upcoming.desc}</div>
      <div class="trend-insight-value">Rekomendasi Stok Bunga</div>
    </div>
    <div class="trend-insight-card low">
      <div class="trend-insight-title">Strategi Bulan Sepi</div>
      <div class="trend-insight-desc">Bulan ${lowest.month} omset berada di titik terendah (${fmt(lowest.total_penjualan)}). Disarankan buat promo bundle / diskon hampers.</div>
      <div class="trend-insight-value">${lowest.month} — Off Peak</div>
    </div>
  `;
}

function renderTrendTopProducts() {
  if (!$('trendTopBody')) return;
  const topProducts = window._topProducts || [];

  $('trendTopBody').innerHTML = topProducts.length ? topProducts.map(p => `
    <tr>
      <td><strong>${p.name}</strong></td>
      <td style="font-weight:600">${p.total_qty} tangkai/buket</td>
      <td style="font-weight:600;color:var(--sage)">${fmt(p.total_rev)}</td>
    </tr>
  `).join('') : '<tr><td colspan="3" style="text-align:center;color:var(--muted);padding:20px">Belum ada transaksi pada tahun ini.</td></tr>';
}


function handleProdImgUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  if ($('pmUploadHint')) $('pmUploadHint').textContent = `File dipilih: ${file.name}`;
  if ($('pmImgFileName')) $('pmImgFileName').textContent = file.name;

  const reader = new FileReader();
  reader.onload = function (e) {
    const preview = $('pmImgPreview');
    const card = $('pmImgPreviewCard');
    const urlInput = $('pmImageUrl');
    if (preview) preview.src = e.target.result;
    if (card) card.style.display = 'flex';
    if (urlInput) urlInput.value = e.target.result;
  };
  reader.readAsDataURL(file);

}

function removeProdImage() {
  if ($('pmImgFile')) $('pmImgFile').value = '';
  if ($('pmImageUrl')) $('pmImageUrl').value = '';
  if ($('pmImgPreview')) $('pmImgPreview').src = '';
  if ($('pmImgPreviewCard')) $('pmImgPreviewCard').style.display = 'none';
  if ($('pmUploadHint')) $('pmUploadHint').textContent = 'Belum ada foto dipilih (Format: PNG, JPG, WEBP)';
}

// ── ADMIN Product CRUD ──
function openAddProduct() {
  state.editingProdId = null;
  if ($('pmTitle')) $('pmTitle').textContent = 'Tambah Produk Baru';
  if ($('pmSubtitle')) $('pmSubtitle').textContent = 'Isi formulir di bawah untuk menambahkan produk baru ke katalog';
  $('pmId').value = ''; $('pmName').value = ''; $('pmCat').value = 'mawar';
  $('pmPrice').value = ''; $('pmOldPrice').value = '';
  $('pmBadge').value = ''; $('pmDesc').value = ''; $('pmTags').value = ''; $('pmStock').value = '100';
  removeProdImage();
  openModal('prodModal');
}

function openEditProduct(id) {
  const numId = Number(id);
  const p = state.products.find(x => Number(x.id) === numId);
  if (!p) return;
  state.editingProdId = numId;
  if ($('pmTitle')) $('pmTitle').textContent = 'Edit Produk';
  if ($('pmSubtitle')) $('pmSubtitle').textContent = `Mengubah detail data "${p.name}"`;
  $('pmId').value = p.id; $('pmName').value = p.name; $('pmCat').value = p.cat || 'buket';
  $('pmPrice').value = p.basePrice !== undefined ? p.basePrice : p.price;
  $('pmOldPrice').value = p.oldPrice ? p.price : '';
  $('pmBadge').value = p.badge || ''; $('pmDesc').value = p.desc || ''; $('pmTags').value = Array.isArray(p.tags) ? p.tags.join(', ') : (p.tags || '');
  $('pmStock').value = p.stock !== undefined && p.stock !== null ? p.stock : 100;

  const imgUrl = p.image_url || p.img || '';
  if ($('pmImageUrl')) $('pmImageUrl').value = imgUrl;
  if (imgUrl && $('pmImgPreviewCard') && $('pmImgPreview')) {
    $('pmImgPreview').src = imgUrl;
    $('pmImgPreviewCard').style.display = 'flex';
    if ($('pmImgFileName')) $('pmImgFileName').textContent = p.name;
    if ($('pmUploadHint')) $('pmUploadHint').textContent = 'Foto produk terpasang';
  } else {
    removeProdImage();
  }

  openModal('prodModal');
}


// Mapping slug kategori → category_id (sesuai database)
async function saveProd() {
  const name = $('pmName').value.trim();
  const price = parseInt($('pmPrice').value);
  const desc = $('pmDesc').value.trim();
  const cat = $('pmCat').value;
  const stockInput = $('pmStock').value;
  const stock = (stockInput !== '' && !isNaN(parseInt(stockInput))) ? parseInt(stockInput) : 100;
  const image_url = $('pmImageUrl') ? $('pmImageUrl').value : '';

  if (!name || !Number.isFinite(price) || price < 0 || !desc) return toast('Lengkapi field wajib!', 'rose');

  const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') + '-' + Date.now();
  const category = state.categories.find(c => c.slug === cat);
  if (!category) return toast('Kategori tidak tersedia di database.', 'rose');
  const category_id = Number(category.id);

  const payload = {
    name, slug, description: desc, price, stock, emoji: '', category_id, image_url,
    promo_price: $('pmOldPrice').value === '' ? null : Number($('pmOldPrice').value),
    badge: $('pmBadge').value || null,
    tags: $('pmTags').value.split(',').map(t => t.trim()).filter(Boolean)
  };

  if (state.editingProdId) {
    // ── Mode EDIT ──
    const prod = state.products.find(x => Number(x.id) === Number(state.editingProdId));
    const isDbProd = prod && Number(prod.id) < 100000;

    if (isDbProd) {
      try {
        const res = await fetch('backend/api/update_product.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: state.editingProdId, ...payload })
        });
        const data = await res.json();
        if (data.status !== 'success') return toast('Gagal update: ' + data.message, 'rose');
      } catch (e) {
        console.error('Update product error:', e);
        return toast('Kesalahan jaringan saat update produk', 'rose');
      }
    }
    await fetchProducts();
    toast('Produk diperbarui!', 'green');

  } else {
    // ── Mode TAMBAH BARU ──
    try {
      const res = await fetch('backend/api/create_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      let data;
      try {
        data = await res.json();
      } catch (_) {
        return toast(`Server error (HTTP ${res.status}). Coba login ulang sebagai admin.`, 'rose');
      }
      if (data.status === 'success') {
        toast('Produk berhasil ditambahkan!', 'green');
        await fetchProducts();
        closeModal('prodModal');
        return;
      } else {
        return toast('Gagal: ' + (data.message || 'Terjadi kesalahan di server'), 'rose');
      }
    } catch (e) {
      console.error('Create product error:', e);
      return toast('Tidak dapat terhubung ke server. Pastikan XAMPP aktif.', 'rose');
    }
  }

  closeModal('prodModal');
  renderAdminProducts(); if (typeof renderProducts === 'function') renderProducts();
}


function getDeletedProductIds() {
  try {
    return JSON.parse(localStorage.getItem('floratica_deleted_products') || '[]').map(Number);
  } catch (e) {
    return [];
  }
}

function saveDeletedProductId(id) {
  const list = getDeletedProductIds();
  const numId = Number(id);
  if (!list.includes(numId)) {
    list.push(numId);
    localStorage.setItem('floratica_deleted_products', JSON.stringify(list));
  }
}

function deleteProduct(id) {
  const numId = Number(id);
  const prod = state.products.find(x => Number(x.id) === numId);
  const prodName = prod ? prod.name : 'produk ini';

  const msgEl = $('confirmDeleteProdMsg');
  if (msgEl) {
    msgEl.textContent = `Apakah kamu yakin ingin menghapus "${prodName}" dari katalog? Tindakan ini tidak dapat dibatalkan.`;
  }

  openModal('confirmDeleteProdModal');

  const btn = $('confirmDeleteProdBtn');
  if (btn) {
    btn.onclick = async function () {
      closeModal('confirmDeleteProdModal');
      await executeDeleteProduct(numId);
    };
  }
}

async function executeDeleteProduct(numId) {
  numId = Number(numId);
  try {
    const res = await fetch('backend/api/delete_product.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: numId })
    });
    const text = await res.text();
    let data = {};
    try { data = JSON.parse(text); } catch (e) { }

    if (!res.ok || data.status !== 'success') return toast(data.message || 'Produk gagal dihapus.', 'rose');
  } catch (e) {
    console.warn('Backend delete network issue:', e);
    return toast('Gagal terhubung ke server.', 'rose');
  }
  await fetchProducts();
  await fetchCart();
  toast('Produk berhasil dihapus!', 'green');
}



function prodCardHTML(p, inWishlist = false) {
  const wishlisted = state.wishlist.some(id => Number(id) === Number(p.id));
  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  const isOut = stock <= 0;

  let badgeHTML = '';
  if (isOut) {
    badgeHTML = `<span class="prod-badge badge-sale" style="background:var(--rose);color:#fff">STOK HABIS</span>`;
  } else if (p.badge === 'new' || p.badge === 'sale') {
    badgeHTML = `<span class="prod-badge badge-${p.badge}">${p.badge.toUpperCase()}</span>`;
  }

  const mediaHTML = (p.image_url || p.img)
    ? `<img src="${esc(p.image_url || p.img)}" alt="${esc(p.name)}" style="width:100%; height:100%; object-fit:cover; border-radius:inherit; display:block;">`
    : '';

  const btnText = p.slug === 'custom-bouquet'
    ? 'Build Your Bouquet'
    : (isOut ? 'Stok Habis' : 'Quick Add');

  return `
    <div class="prod-card reveal ${isOut ? 'out-of-stock' : ''}" onclick="openDetail(${p.id})">
      <div class="prod-img" style="background:${p.bg || '#FAF9F6'}">
        ${mediaHTML}
        ${badgeHTML}
        <button type="button" class="wish-btn ${wishlisted ? 'on' : ''}" onclick="toggleWish(event,${p.id})" aria-label="${wishlisted ? 'Hapus dari wishlist' : 'Tambah ke wishlist'}" aria-pressed="${wishlisted}" title="${wishlisted ? 'Hapus dari wishlist' : 'Tambah ke wishlist'}">
          <svg class="wish-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.6a5.5 5.5 0 0 0-.1-7.8Z"></path></svg>
        </button>
      </div>
      <div class="prod-body">
        <div class="prod-cat">${esc(p.cat)}</div>
        <div class="prod-name">${esc(p.name)}</div>
        <div class="prod-price">
          ${p.slug === 'custom-bouquet' ? 'From Rp 50.000' : fmt(p.price)}
          ${p.oldPrice ? `<span class="prod-price-old">${fmt(p.oldPrice)}</span>` : ''}
        </div>
        <button class="add-circle" ${isOut ? 'disabled style="opacity:0.6;cursor:not-allowed;background:var(--muted)"' : ''} onclick="${p.slug === 'custom-bouquet' ? 'openCustomModal(event)' : `addCart(event,${p.id})`}">
          ${btnText}
        </button>
      </div>
    </div>`;
}


function renderProducts() {
  const all = state.products.filter(p => p.cat !== 'satuan' && (state.currentFilter === 'semua' || p.cat === state.currentFilter));
  const shown = all.slice(0, PREVIEW_COUNT);
  $('prodGrid').innerHTML = shown.map(p => prodCardHTML(p)).join('');

  // Tombol "Lihat Semua" selalu tampil selama ada produk, selalu arahkan ke katalog lengkap
  $('viewAllBtn').style.display = all.length === 0 ? 'none' : '';
  $('viewAllTxt').textContent = 'Lihat Semua';
  $('viewAllIcon').textContent = '→';
  $('viewAllBtn').onclick = () => {
    state.fullFilter = 'semua'; // selalu tampilkan semua bunga di katalog
    openFullCatalog();
  };

  // Scope ke prodGrid saja agar tidak re-trigger animasi elemen statis di luar grid
  initRevealAnimations($('prodGrid') || document);
}

function filterProd(cat, btn) {
  state.currentFilter = cat; state.showAll = false;
  document.querySelectorAll('.fbtn').forEach(b => b.classList.remove('on'));
  if (btn) btn.classList.add('on');
  else document.querySelectorAll('.fbtn').forEach(b => { if (b.textContent.toLowerCase().includes(cat.slice(0, 4))) b.classList.add('on') });
  if ($('prodGrid')) $('prodGrid').scrollLeft = 0;
  renderProducts();
}

function openFullCatalog() {
  transitionToScreen('fullCatalogScreen');
  window.scrollTo({ top: 0, behavior: 'instant' });
  // Sinkronkan tombol filter sesuai state.fullFilter sebelum render
  renderCategoryFilters();
  renderFullCatalog();
}

function renderFullCatalog() {
  const cat = state.fullFilter || 'semua';
  const all = state.products.filter(p => p.cat !== 'satuan' && (cat === 'semua' || p.cat === cat));
  $('fullProdGrid').innerHTML = all.map(p => prodCardHTML(p)).join('');
  // Scope ke fullProdGrid agar hanya kartu baru yang dianimasikan
  initRevealAnimations($('fullProdGrid') || document);
}

function filterProdFull(cat, btn) {
  state.fullFilter = cat;
  document.querySelectorAll('#fullFilterBar .fbtn').forEach(b => b.classList.remove('on'));
  if (btn) btn.classList.add('on');
  renderFullCatalog();
}

function toggleWish(e, id) {
  e.stopPropagation();
  id = Number(id);
  if (!state.user) { openAuthModal(); return toast('Login dulu untuk wishlist', 'rose'); }
  if (state.user.role === 'admin') { return toast('Admin tidak bisa menambah wishlist', 'rose'); }

  const idx = state.wishlist.findIndex(x => Number(x) === id);
  const isAdding = idx === -1;

  // 1. Update state
  if (isAdding) { state.wishlist.push(id); toast('Ditambahkan ke wishlist!', 'rose'); }
  else { state.wishlist.splice(idx, 1); toast('Dihapus dari wishlist'); }

  // 2. Update UI
  renderProducts();
  if ($('wishlistGrid') && $('panelWishlist').classList.contains('on')) renderWishlistPanel();
  $('uStatWish').textContent = state.wishlist.length;

  // 3. Simpan ke database, lalu tarik ulang status otoritatif dari server.
  fetch('backend/api/toggle_wishlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: state.user.id, product_id: id })
  }).then(async res => {
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message);
    await fetchWishlist();
  }).catch(() => {
    toast('Wishlist gagal disimpan ke server.', 'rose');
    fetchWishlist();
  });
}

function openDetail(id) {
  const p = state.products.find(x => Number(x.id) === Number(id));
  if (!p) return;
  state.detailProduct = p;

  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  const isOutOfStock = stock <= 0;

  state.detailQty = isOutOfStock ? 0 : 1;

  const imgHtml = (p.image_url || p.img)
    ? `<img src="${esc(p.image_url || p.img)}" alt="${esc(p.name)}" style="width:100%; height:100%; object-fit:cover; border-radius:12px; display:block;">`
    : '';
  $('detailImgWrap').innerHTML = imgHtml;
  $('detailImgWrap').style.background = p.bg || '#faf6ef';
  $('dCat').textContent = p.cat.toUpperCase();
  $('dName').textContent = p.name;
  $('dPrice').textContent = fmt(p.price);
  $('dTags').innerHTML = (p.tags || []).map(t => `<span class="tag">${esc(t)}</span>`).join('');
  $('dDesc').textContent = p.desc;

  if ($('dQty')) {
    $('dQty').value = state.detailQty;
    $('dQty').disabled = isOutOfStock;
  }

  const badgeEl = $('dStockBadge');
  if (badgeEl) {
    if (isOutOfStock) {
      badgeEl.textContent = 'Stok Habis';
      badgeEl.className = 'detail-stock-badge empty';
    } else {
      badgeEl.textContent = `Stok: ${stock}`;
      badgeEl.className = 'detail-stock-badge';
    }
  }

  const addBtn = $('dAddBtn');
  if (addBtn) {
    addBtn.disabled = isOutOfStock;
    addBtn.textContent = isOutOfStock ? 'Stok Habis' : 'Tambah ke Keranjang';
    addBtn.onclick = () => {
      if (isOutOfStock) return toast('Stok produk ini sedang habis!', 'rose');
      addCart(null, p.id, state.detailQty);
      closeModal('detailModal');
    };
  }

  openModal('detailModal');
}

function detQty(d) {
  const p = state.detailProduct;
  if (!p) return;
  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  if (stock <= 0) return toast('Stok produk ini sedang habis!', 'rose');

  let target = state.detailQty + d;
  if (target < 1) target = 1;
  if (target > stock) {
    target = stock;
    toast(`Stok tidak mencukupi! Maksimal: ${stock}`, 'rose');
  }
  state.detailQty = target;
  if ($('dQty')) $('dQty').value = target;
}

function setDetailQtyFromInput(val) {
  const p = state.detailProduct;
  if (!p) return;
  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  if (stock <= 0) return;
  if (val === '' || val === null) return;

  let num = parseInt(val, 10);
  if (isNaN(num) || num < 1) num = 1;
  if (num > stock) {
    num = stock;
    toast(`Stok tidak mencukupi! Maksimal: ${stock}`, 'rose');
    if ($('dQty')) $('dQty').value = num;
  }
  state.detailQty = num;
}

function validateDetailQtyInput(val) {
  const p = state.detailProduct;
  if (!p) return;
  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  if (stock <= 0) {
    state.detailQty = 0;
    if ($('dQty')) $('dQty').value = 0;
    return;
  }
  let num = parseInt(val, 10);
  if (isNaN(num) || num < 1) num = 1;
  if (num > stock) num = stock;
  state.detailQty = num;
  if ($('dQty')) $('dQty').value = num;
}

function addCart(e, id, qty = 1) {
  if (e) e.stopPropagation();
  id = Number(id);
  if (!state.user) { openAuthModal(); return; }
  if (state.user.role === 'admin') { toast('Admin tidak bisa berbelanja', 'rose'); return; }
  const p = state.products.find(x => Number(x.id) === id);
  if (!p) return;
  if (p.slug === 'custom-bouquet') {
    openCustomModal();
    return;
  }

  const stock = (p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;
  if (stock <= 0) {
    toast('Stok produk ini sedang habis!', 'rose');
    return;
  }

  const ex = state.cart.find(x => Number(x.id) === id);
  const existingQty = ex ? ex.qty : 0;

  if (existingQty + qty > stock) {
    if (existingQty >= stock) {
      toast(`Anda sudah mencapai batas stok maksimum (${stock}) untuk produk ini!`, 'rose');
      return;
    }
    if (ex) ex.qty = stock;
    else state.cart.push({ ...p, qty: stock });
    updateCartUI();
    syncCart();
    toast(`Jumlah disesuaikan dengan sisa stok yang tersedia (Maks: ${stock})`, 'rose');
    $('scMessage').textContent = `${p.name} ditambahkan (Maksimal stok: ${stock})`;
    openModal('successCartModal');
    bumpBadge();
    return;
  }

  if (ex) ex.qty += qty;
  else state.cart.push({ ...p, qty });
  updateCartUI();
  syncCart();

  $('scMessage').textContent = `${p.name} berhasil ditambahkan ke keranjang!`;
  openModal('successCartModal');
  bumpBadge();
}

function removeCart(id) {
  state.cart = state.cart.filter(x => String(x.id) !== String(id));
  updateCartUI();
  syncCart();
}

function changeQty(id, d) {
  const it = state.cart.find(x => String(x.id) === String(id));
  if (!it) return;
  const p = state.products.find(x => String(x.id) === String(id));
  const stock = (p && p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;

  let target = it.qty + d;
  if (target > stock) {
    target = stock;
    toast(`Stok tidak mencukupi! Maksimal: ${stock}`, 'rose');
  }

  it.qty = target;
  if (it.qty <= 0) removeCart(id);
  else {
    updateCartUI();
    syncCart();
  }
}

function setCartQtyInput(id, val) {
  const it = state.cart.find(x => String(x.id) === String(id));
  if (!it) return;
  const p = state.products.find(x => String(x.id) === String(id));
  const stock = (p && p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;

  let num = parseInt(val, 10);
  if (isNaN(num) || num < 1) num = 1;
  if (num > stock) {
    num = stock;
    toast(`Stok tidak mencukupi! Maksimal: ${stock}`, 'rose');
  }

  it.qty = num;
  updateCartUI();
  syncCart();
}

function setCartQtyInputOnInput(id, inputEl) {
  if (!inputEl) return;
  const val = inputEl.value;
  if (val === '') return;
  const it = state.cart.find(x => String(x.id) === String(id));
  if (!it) return;
  const p = state.products.find(x => String(x.id) === String(id));
  const stock = (p && p.stock !== undefined && p.stock !== null) ? Number(p.stock) : 99999;

  let num = parseInt(val, 10);
  if (isNaN(num) || num < 1) return;
  if (num > stock) {
    num = stock;
    inputEl.value = num;
    toast(`Stok tidak mencukupi! Maksimal: ${stock}`, 'rose');
  }
  it.qty = num;
  // Jangan kirim satu request database pada setiap karakter yang diketik.
  // Nilai terakhir saja yang disinkronkan setelah pengguna berhenti mengetik.
  clearTimeout(cartSyncDebounceTimer);
  cartSyncDebounceTimer = setTimeout(() => {
    syncCart();
    cartSyncDebounceTimer = null;
  }, 350);
  if ($('cpTotal')) $('cpTotal').textContent = fmt(getTotal());
  if ($('cartBadge')) $('cartBadge').textContent = state.cart.reduce((s, i) => s + i.qty, 0);
}

function getSubtotal() { return state.cart.reduce((s, i) => s + i.price * i.qty, 0); }
function getTotal() { return getSubtotal() + state.shipCost - state.promoDiscount; }

function updateCartUI() {
  const count = state.cart.reduce((s, i) => s + i.qty, 0);
  $('cartBadge').textContent = count;
  $('cpTotal').textContent = fmt(getTotal());
  $('checkoutBtn').disabled = state.cart.length === 0;
  const el = $('cpItems');
  if (!state.cart.length) {
    el.innerHTML = `<div class="cp-empty"><p>Keranjangmu masih kosong.<br>Yuk pilih bunga!</p></div>`;
    return;
  }
  el.innerHTML = state.cart.map(it => {
    const cartMedia = (it.image_url || it.img)
      ? `<img src="${esc(it.image_url || it.img)}" alt="${esc(it.name)}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block;">`
      : '';
    return `
    <div class="cart-item">
      <div class="ci-img" style="background:${it.bg || '#faf6ef'}">${cartMedia}</div>
      <div class="ci-info">
        <div class="ci-name">${esc(it.name)}</div>
        <div class="ci-price">${fmt(it.price)}</div>
        <div class="ci-qty">
          <button class="qbtn" onclick='changeQty(${JSON.stringify(String(it.id))},-1)'>−</button>
          <input type="number" class="qnum-input" value="${it.qty}" min="1" onchange='setCartQtyInput(${JSON.stringify(String(it.id))}, this.value)' oninput='setCartQtyInputOnInput(${JSON.stringify(String(it.id))}, this)'>
          <button class="qbtn" onclick='changeQty(${JSON.stringify(String(it.id))},1)'>+</button>
        </div>
      </div>
      <button class="ci-del" onclick='removeCart(${JSON.stringify(String(it.id))})'>Hapus</button>
    </div>`;
  }).join('');
}


function toggleCart() {
  const ov = $('cartOverlay'), pn = $('cartPanel');
  ov.classList.toggle('on'); pn.classList.toggle('on');
  document.body.style.overflow = pn.classList.contains('on') ? 'hidden' : '';
}

function bumpBadge() {
  $('cartBadge').classList.add('pop');
  setTimeout(() => $('cartBadge').classList.remove('pop'), 300);
}

function openCheckout() {
  if (!state.user) { openAuthModal(); return; }
  toggleCart();
  transitionToScreen('checkoutScreen');

  // Populate user info card from logged-in session
  const u = state.user;
  $('coUserAvatar').textContent = u.name ? u.name[0].toUpperCase() : 'U';
  $('coUserName').textContent = u.name || '—';
  $('coUserPhone').textContent = u.phone || '—';
  $('coUserEmail').textContent = u.email || '—';

  // Hidden fields for doPaymentNew()
  $('coName').value = u.name || '';
  $('coPhone').value = u.phone || '';
  $('coEmail').value = u.email || '';

  renderCheckoutItems();
  window.scrollTo(0, 0);
}


function renderCheckoutItems() {
  $('coItemsBody').innerHTML = state.cart.map(it => {
    const coMedia = (it.image_url || it.img)
      ? `<img src="${esc(it.image_url || it.img)}" alt="${esc(it.name)}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;display:block;">`
      : '';
    return `
    <tr>
      <td><div class="co-item-img" style="background:${it.bg || '#faf6ef'}; font-size: 20px;">${coMedia}</div></td>
      <td style="font-weight:500;font-size:14px;color:var(--sage)">${esc(it.name)}</td>
      <td style="font-size:14px">${fmt(it.price)}</td>
      <td style="font-size:14px">${it.qty}</td>
      <td style="font-weight:600;color:var(--gold);font-size:14px">${fmt(it.price * it.qty)}</td>
    </tr>`;
  }).join('');

  const sub = getSubtotal();
  $('coSubtotal').textContent = fmt(sub);
  $('coSubtotalDisc').textContent = fmt(sub);
  if ($('coShipCost')) $('coShipCost').textContent = 'Konsultasi via WA';
  $('coTotal').textContent = fmt(sub);
}




async function doPaymentNew() {
  if (checkoutSubmitting) return;
  const fName = $('coName').value.trim();
  const phone = $('coPhone').value.trim();
  const date = $('coDate').value;

  if (!fName) return toast('Lengkapi nama penerima!', 'rose');
  if (!phone) return toast('Lengkapi nomor telepon!', 'rose');
  if (!date) return toast('Lengkapi jadwal pengiriman!', 'rose');

  // Validate cart item stock before payment
  for (const it of state.cart) {
    if (it.slug === 'custom-bouquet' || String(it.id).startsWith('custom-')) continue;
    const p = state.products.find(x => Number(x.id) === Number(it.id));
    if (p && p.stock !== undefined && p.stock !== null) {
      const stock = Number(p.stock);
      if (stock <= 0) {
        return toast(`Produk "${it.name}" stoknya sedang habis. Silakan hapus dari keranjang.`, 'rose');
      }
      if (it.qty > stock) {
        return toast(`Jumlah "${it.name}" (${it.qty}) melebihi stok yang tersedia (${stock}). Silakan ubah jumlah.`, 'rose');
      }
    }
  }

  const addr = $('coAddress') ? $('coAddress').value.trim() : '';
  const note = $('coNote') ? $('coNote').value.trim() : '';
  const payMethod = 'qris';

  const orderId = 'FLR-' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '-' + Math.random().toString(36).slice(2, 6).toUpperCase();

  const order = {
    total: getTotal(),
    shippingCity: addr || 'Dikonfirmasi via WhatsApp',
    payMethod,
    items: state.cart.map(i => ({ ...i }))
  };

  const rawUserId = state.user ? state.user.id : null;
  const userId = parseInt(rawUserId, 10);
  if (!userId || isNaN(userId)) return toast('Silakan login ulang untuk membuat pesanan.', 'rose');

  const submitButton = $('coSubmitBtn');
  const originalButtonText = submitButton ? submitButton.textContent : 'Buat Pesanan';
  const setSubmitting = (isSubmitting, text = originalButtonText) => {
    checkoutSubmitting = isSubmitting;
    if (!submitButton) return;
    submitButton.disabled = isSubmitting;
    submitButton.setAttribute('aria-busy', String(isSubmitting));
    submitButton.textContent = text;
  };

  setSubmitting(true, 'Memproses pesanan...');
  try {
    // Selesaikan perubahan keranjang yang masih diantrikan sebelum membuat order.
    // Setelah create_order berhasil, backend sudah menghapus cart sehingga tidak
    // boleh menunggu/mengirim sinkronisasi kosong lagi.
    await flushCartSync();

    const res = await fetch('backend/api/create_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: userId,
        invoice_number: orderId,
        shipping_address: order.shippingCity,
        payment_method: order.payMethod,
        delivery_at: date,
        customer_note: note,
        items: order.items
      })
    });

    const data = await res.json();
    if (data.status === 'success') {
      const dateStr = new Date(date).toLocaleString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
      });

      const itemLines = order.items
        .map(i => `  ${i.name} x${i.qty} : ${fmt(i.price * i.qty)}`)
        .join('\n');

      let msg = `*Pesanan Baru — The Moments*\n`;
      msg += `No. Pesanan: *${orderId}*\n`;
      msg += `━━━━━━━━━━━━━━━━━━━━━━\n\n`;
      msg += `*Saya ingin memesan:*\n\n`;
      msg += `${itemLines}\n\n`;
      msg += `*Total: ${fmt(order.total)}*\n`;
      msg += `━━━━━━━━━━━━━━━━━━━━━━\n\n`;
      msg += `*Nama:* ${fName}\n`;
      msg += `*Telepon:* ${phone}\n`;
      if (addr) msg += `*Alamat:* ${addr}\n`;
      msg += `*Jadwal:* ${dateStr}\n`;
      if (note) msg += `*Catatan:* ${note}\n`;
      msg += `\nTerima kasih!`;

      const persistedInvoice = data.invoice_number || orderId;
      msg = msg.replace(orderId, persistedInvoice);
      const waUrl = `https://wa.me/6281250157562?text=${encodeURIComponent(msg)}`;

      state.cart = [];
      updateCartUI();
      fetchOrders();
      fetchProducts();

      $('invNumber').textContent = persistedInvoice;
      $('invName').textContent = fName;
      $('invPhone').textContent = phone;
      $('invPayment').textContent = 'Dikonfirmasi via WhatsApp';
      $('invType').textContent = addr ? 'Antar ke Alamat' : 'Dikonfirmasi via WhatsApp';
      $('invDate').textContent = dateStr;
      $('invTotal').textContent = fmt(data.total_amount || order.total);

      $('waRedirectBtn').onclick = function () { window.open(waUrl, '_blank'); };

      goHome(false);
      openModal('orderSuccessModal');
      toast('Pesanan berhasil dibuat!', 'green');
    } else {
      toast('Gagal membuat pesanan: ' + data.message, 'rose');
    }
  } catch (err) {
    console.error(err);
    toast('Kesalahan jaringan', 'rose');
  } finally {
    setSubmitting(false);
  }
}

async function fetchOrders() {
  if (!state.user) {
    state.orders = [];
    return;
  }
  try {
    const url = state.user && state.user.role === 'admin'
      ? 'backend/api/get_orders.php'
      : `backend/api/get_orders.php?user_id=${state.user ? state.user.id : ''}`;
    const res = await fetch(url);
    if (res.status === 401 || res.status === 403) {
      logout();
      toast('Sesi Anda telah berakhir, silakan login kembali.', 'rose');
      return;
    }
    const json = await res.json();
    if (json.status === 'success') {
      state.orders = json.data;
      if (state.user && state.user.role === 'admin') {
        renderAdminOverview();
        renderAdminOrders();
      } else if (state.user) {
        // Refresh dashboard panels jika sedang aktif
        if ($('panelOverview') && $('panelOverview').style.display !== 'none') renderUserOverview();
        if ($('panelOrders') && $('panelOrders').style.display !== 'none') renderOrdersPanel();
      }
    }
  } catch (error) {
    console.error('Gagal mengambil API orders:', error);
  }
}

// ── Wishlist localStorage helpers ──
function cartItemFromApi(item) {
  let itemData = {};
  try { itemData = item.item_data ? JSON.parse(item.item_data) : {}; } catch (e) { console.warn('Data keranjang tidak valid:', e); }

  if (itemData.type === 'custom_bouquet') {
    return {
      id: `custom-${item.cart_id}`,
      name: itemData.name || 'Buket Custom',
      price: Number(item.unit_price),
      emoji: '',
      qty: Number(item.quantity),
      bg: itemData.bg || '#FFF0F0',
      components: itemData.components || {}
    };
  }

  const product = state.products.find(p => Number(p.id) === Number(item.product_id));
  return product
    ? { ...product, qty: Number(item.quantity), price: Number(item.unit_price) }
    : {
      id: Number(item.product_id), name: item.name, slug: item.slug,
      emoji: '', price: Number(item.unit_price), qty: Number(item.quantity),
      cat: item.category_slug || 'produk', bg: '#FAF9F6', tags: []
    };
}

async function fetchCart() {
  if (!state.user || state.user.role !== 'user') {
    state.cart = [];
    updateCartUI();
    return;
  }

  try {
    const res = await fetch(`backend/api/get_cart.php?user_id=${state.user.id}`);
    if (res.status === 401 || res.status === 403) {
      logout();
      toast('Sesi Anda telah berakhir, silakan login kembali.', 'rose');
      return;
    }
    const json = await res.json();
    if (json.status === 'success') {
      state.cart = json.data.map(cartItemFromApi);
      updateCartUI();
    }
  } catch (error) {
    console.error('Gagal mengambil keranjang:', error);
  }
}

function syncCart() {
  if (!state.user || state.user.role !== 'user') return Promise.resolve();

  const items = state.cart.map(item => ({
    id: item.id,
    qty: item.qty,
    components: item.components || null
  }));

  cartSyncQueue = cartSyncQueue.catch(() => { }).then(async () => {
    const res = await fetch('backend/api/sync_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: state.user.id, items })
    });
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message || 'Gagal menyimpan keranjang.');
  }).catch(error => console.error('Gagal menyimpan keranjang:', error));

  return cartSyncQueue;
}

async function flushCartSync() {
  // Jika pengguna langsung checkout setelah mengubah qty, kirim hanya nilai
  // terakhir yang masih menunggu debounce. Bila antrean sudah kosong, fungsi
  // ini langsung selesai tanpa membuat request tambahan.
  if (cartSyncDebounceTimer !== null) {
    clearTimeout(cartSyncDebounceTimer);
    cartSyncDebounceTimer = null;
    syncCart();
  }
  await cartSyncQueue;
}

function _wishKey() {
  return state.user ? `floratica_wish_${state.user.id}` : null;
}
function _saveWishlistLocal() {
  const key = _wishKey();
  if (key) localStorage.setItem(key, JSON.stringify(state.wishlist));
}
function _loadWishlistLocal() {
  const key = _wishKey();
  if (!key) return [];
  try { return JSON.parse(localStorage.getItem(key) || '[]').map(Number); }
  catch (e) { return []; }
}

async function fetchWishlist() {
  if (!state.user || state.user.role === 'admin') {
    state.wishlist = [];
    renderProducts();
    return;
  }

  try {
    const res = await fetch(`backend/api/get_wishlist.php?user_id=${state.user.id}`);
    if (res.status === 401 || res.status === 403) {
      logout();
      toast('Sesi Anda telah berakhir, silakan login kembali.', 'rose');
      return;
    }
    const json = await res.json();
    if (json.status === 'success' && Array.isArray(json.data)) {
      state.wishlist = json.data.map(Number);
    }
  } catch (e) {
    console.warn('Wishlist DB tidak tersedia');
  }

  renderProducts();
  if ($('uStatWish')) $('uStatWish').textContent = state.wishlist.length;
}

async function syncStock(id, deductQty) {
  try {
    await fetch('backend/api/update_stock.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, deduct_qty: deductQty })
    });
  } catch (e) { console.error('Stock sync error:', e); }
}

const PASSWORD_EYE_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
const PASSWORD_EYE_OFF_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="m3 3 18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.4 5.4A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a18.2 18.2 0 0 1-3.1 4.2"></path><path d="M6.2 6.2C3.7 8 2 12 2 12s3.5 7 10 7a10 10 0 0 0 3.2-.5"></path></svg>';

function resetPasswordVisibility(id) {
  const input = $(id);
  if (!input) return;
  input.type = 'password';
  const toggle = input.closest('.pass-box')?.querySelector('.pass-toggle');
  if (!toggle) return;
  toggle.innerHTML = PASSWORD_EYE_ICON;
  toggle.setAttribute('aria-pressed', 'false');
  toggle.setAttribute('aria-label', 'Tampilkan password');
}

// Saat pengguna berpindah ke tab/aplikasi lain, jangan biarkan password
// yang sebelumnya ditampilkan tetap terbuka saat halaman dikunjungi lagi.
document.addEventListener('visibilitychange', () => {
  if (!document.hidden) return;
  resetPasswordVisibility('loginPass');
  resetPasswordVisibility('regPass');
});

function togglePass(id, el) {
  const input = $(id);
  if (!input) return;
  const isVisible = input.type === 'password';
  input.type = isVisible ? 'text' : 'password';
  // Kedua SVG adalah konstanta lokal, bukan data pengguna, sehingga aman diganti utuh.
  el.innerHTML = isVisible ? PASSWORD_EYE_OFF_ICON : PASSWORD_EYE_ICON;
  el.setAttribute('aria-pressed', String(isVisible));
  el.setAttribute('aria-label', isVisible ? 'Sembunyikan password' : 'Tampilkan password');
}

function renderBranches() {
  $('branchesList').innerHTML = BRANCHES.map((b, i) => `
    <div class="branch-card ${i === 0 ? 'active' : ''}" id="bc${b.id}" onclick="selectBranch(${b.id})">
      <h4>${b.name}</h4>
      <p>${b.addr}</p>
      <div class="phone">${b.phone}</div>
      <div class="hours">${b.hours}</div>
    </div>`).join('');
  selectBranch(1);
}

function selectBranch(id) {
  document.querySelectorAll('.branch-card').forEach(c => c.classList.remove('active'));
  const bc = $(`bc${id}`);
  if (bc) { bc.classList.add('active'); bc.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
  const b = BRANCHES.find(x => x.id === id);
  if (!b) return;

  const embedIdStr = b.embedId ? encodeURIComponent(b.embedId) : '0x0%3A0x0';
  const placeParam = `1s${embedIdStr}!2s${encodeURIComponent(b.name + ', ' + b.addr)}`;
  const mapSrc = `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2000!2d${b.lng}!3d${b.lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!${placeParam}!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid`;
  $('mapIframe').src = mapSrc;
  const info = $('mapInfo');
  info.classList.add('show');
  info.innerHTML = `<h5>${b.name}</h5><p>${b.addr}</p><p style="margin-top:4px;color:var(--sage);font-weight:500">${b.phone}</p><p style="margin-top:2px;color:var(--gold);font-size:11px">${b.hours}</p>`;
}

async function fetchCategories() {
  try {
    const res = await fetch('backend/api/categories.php?v=' + Date.now());
    const json = await res.json();
    state.categories = json.status === 'success' && Array.isArray(json.data) ? json.data : [];
    renderCategoryFilters();
  } catch (e) {
    console.error('Fetch categories error:', e);
    state.categories = [];
    renderCategoryFilters();
  }
}

function renderCategoryFilters() {
  const categoriesHtml = (isFull) => {
    const filter = isFull ? (state.fullFilter || 'semua') : state.currentFilter;
    const func = isFull ? 'filterProdFull' : 'filterProd';

    const visibleCats = state.categories.filter(c => c.slug !== 'satuan' && c.slug !== 'bunga-satuan');

    return `<button class="fbtn ${filter === 'semua' ? 'on' : ''}" onclick="${func}('semua',this)">Semua</button>` +
      visibleCats.map(c => {
        const slug = /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(String(c.slug)) ? c.slug : 'semua';
        return `<button class="fbtn ${filter === slug ? 'on' : ''}" onclick="${func}('${slug}',this)">${esc(c.name)}</button>`;
      }).join('');
  };

  if ($('filterBar')) $('filterBar').innerHTML = categoriesHtml(false);
  if ($('fullFilterBar')) $('fullFilterBar').innerHTML = categoriesHtml(true);
}

async function fetchProducts() {
  try {
    const res = await fetch('backend/api/products.php');
    const json = await res.json();

    if (json.status === 'success' && Array.isArray(json.data)) {
      state.products = json.data
        .map(p => {
          const imgUrl = p.image_url || '';
          const normalPrice = Number(p.price);
          const promoPrice = p.promo_price === null || p.promo_price === '' ? null : Number(p.promo_price);
          return {
            id: Number(p.id),
            db_cat: Number(p.category_id),
            name: p.name,
            slug: p.slug,
            cat: p.category_slug || 'produk',
            emoji: '',
            image_url: imgUrl,
            img: imgUrl,
            price: promoPrice !== null && promoPrice >= 0 ? promoPrice : normalPrice,
            basePrice: normalPrice,
            stock: Number(p.stock),
            oldPrice: promoPrice !== null && promoPrice < normalPrice ? normalPrice : null,
            badge: p.badge || null,
            desc: p.description || '',
            tags: Array.isArray(p.tags) ? p.tags : [],
            rating: Number(p.rating || 0),
            reviews: Number(p.review_count || 0),
            bg: '#FFF0F0'
          };
        });
    } else state.products = [];
  } catch (error) {
    console.error('Gagal mengambil API:', error);
    state.products = [];
  } finally {
    renderProducts();
    if (state.user && state.user.role === 'admin') renderAdminProducts();
  }
}


async function fetchUsers() {
  // Endpoint users hanya boleh dipanggil oleh admin. Memanggilnya untuk
  // pengunjung/pelanggan menghasilkan 401 yang sebelumnya memicu logout dan
  // menghapus sesi CSRF sebelum formulir pendaftaran dikirim.
  if (!state.user || state.user.role !== 'admin') {
    state.users = [];
    return;
  }

  try {
    const res = await fetch('backend/api/users.php');
    if (res.status === 401 || res.status === 403) {
      logout();
      toast('Sesi Anda telah berakhir, silakan login kembali.', 'rose');
      return;
    }
    const json = await res.json();
    if (json.status === 'success') {
      state.users = json.data;
    }
  } catch (error) {
    console.error('Gagal mengambil API users:', error);
  }
}

/**
 * Inisialisasi animasi reveal berbasis ScrollTrigger.
 * @param {Element} [scope=document] - Batasi pencarian .reveal ke dalam scope ini
 *                                     untuk mencegah duplikasi ScrollTrigger.
 */
function initRevealAnimations(scope = document) {
  ScrollTrigger.getAll().forEach(st => {
    if (scope.contains(st.trigger)) st.kill();
  });

  const revealEls = scope.querySelectorAll('.reveal');

  revealEls.forEach((el, i) => {
    gsap.set(el, { opacity: 0, y: 24, willChange: 'opacity, transform' });

    gsap.to(el, {
      opacity: 1,
      y: 0,
      duration: 0.65,
      ease: 'power2.out',
      delay: Math.min(i * 0.04, 0.3),
      scrollTrigger: {
        trigger: el,
        start: 'top 88%',
        toggleActions: 'play none none none',
        once: true,
        onEnter: () => {
          gsap.set(el, { willChange: 'auto', clearProps: 'willChange' });
        }
      }
    });
  });
}

function animateHeroEntrance() {
  const heroText = document.querySelector('.hero-text');
  if (!heroText) return;

  heroText.style.animation = 'none';
  const children = heroText.querySelectorAll('.sec-tag, h1, p, .hero-btns');
  children.forEach(el => { el.style.animation = 'none'; });

  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
  tl.fromTo(heroText,
    { opacity: 0, y: 30 },
    { opacity: 1, y: 0, duration: 0.8 }
  )
    .fromTo(children,
      { opacity: 0, y: 20 },
      { opacity: 1, y: 0, duration: 0.6, stagger: 0.12, clearProps: 'transform' },
      '-=0.5'
    );
}

async function init() {
  history.scrollRestoration = 'manual';

  document.querySelectorAll('.screen').forEach(s => {
    if (s.id === 'homeScreen') {
      gsap.set(s, { display: 'block', opacity: 1, y: 0 });
      s.classList.add('active');
    } else {
      gsap.set(s, { display: 'none', opacity: 0, y: 0 });
      s.classList.remove('active');
    }
  });

  renderBranches();
  updateCartUI();
  updateNavAuth();
  animateHeroEntrance();

  initRevealAnimations($('homeScreen') || document);

  const savedUser = localStorage.getItem('floratica_user');
  if (savedUser) {
    try {
      const parsed = JSON.parse(savedUser);
      if (parsed && parsed.role === 'pelanggan') {
        parsed.role = 'user';
      }
      state.user = parsed;
      updateNavAuth();
    } catch (e) {
      localStorage.removeItem('floratica_user');
    }
  }

  await fetchCategories();
  await fetchProducts();
  await fetchCart();
  await fetchUsers();
  await fetchOrders();
  await fetchWishlist(); // restore wishlist dari DB saat halaman dimuat ulang
  await fetchTestimonials();
  initDragScroll();
}

function initDragScroll() {
  const slider = $('prodGrid');
  if (!slider) return;

  let isDown = false;
  let startX;
  let scrollLeft;
  let moved = false;

  slider.addEventListener('mousedown', (e) => {
    isDown = true;
    moved = false;
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
    slider.style.cursor = 'grabbing';
    slider.style.scrollSnapType = 'none';
  });

  slider.addEventListener('dragstart', (e) => {
    e.preventDefault();
  });

  slider.addEventListener('mouseleave', () => {
    isDown = false;
    slider.style.cursor = 'grab';
    slider.style.scrollSnapType = 'x mandatory';
  });

  slider.addEventListener('mouseup', () => {
    isDown = false;
    slider.style.cursor = 'grab';
    slider.style.scrollSnapType = 'x mandatory';
  });

  slider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 1.5;
    if (Math.abs(walk) > 5) {
      moved = true;
    }
    slider.scrollLeft = scrollLeft - walk;
  });

  slider.addEventListener('click', (e) => {
    if (moved) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  slider.style.cursor = 'grab';
}

async function fetchTestimonials() {
  const grid = $('testimonialsGrid');
  if (!grid) return;
  try {
    const res = await fetch('backend/api/reviews.php?limit=3');
    const json = await res.json();
    if (json.status !== 'success') throw new Error();
    grid.innerHTML = json.data.length ? json.data.map(review => {
      const name = review.user_name || 'Pelanggan';
      const initials = name.split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase();
      const rating = Math.max(1, Math.min(5, Number(review.rating) || 0));
      const stars = Array.from({ length: 5 }, (_, index) => `<svg class="testi-star ${index < rating ? 'is-filled' : ''}" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9-6.2-3.3-6.2 3.3 1.2-6.9-5-4.9 6.9-1L12 2Z"></path></svg>`).join('');
      const date = review.created_at ? new Date(review.created_at).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '';
      const product = review.product_name ? `<div class="testi-product">${esc(review.product_name)}</div>` : '';
      return `<div class="testi-card"><div class="testi-stars" role="img" aria-label="Rating ${rating} dari 5">${stars}</div><p class="testi-txt">&ldquo;${esc(review.comment || 'Ulasan tanpa komentar.')}&rdquo;</p><div class="testi-auth"><div class="testi-av" style="font-size:14px;font-weight:700;color:var(--sage);background:var(--sage3);display:flex;align-items:center;justify-content:center">${esc(initials)}</div><div><div class="testi-name">${esc(name)}</div><div class="testi-loc">${esc(date)}</div>${product}</div></div></div>`;
    }).join('') : '<p class="testi-empty">Belum ada ulasan pelanggan.</p>';
  } catch (error) {
    grid.innerHTML = '<p style="text-align:center;color:var(--muted)">Ulasan belum dapat dimuat.</p>';
  }
}

init();

window.addEventListener('pageshow', function () {
  history.scrollRestoration = 'manual';
  window.scrollTo({ top: 0, behavior: 'instant' });
});

function subscribeNewsletter(btn) {
  const input = btn.previousElementSibling;
  const email = input.value.trim();
  if (!validateEmail(email)) return toast('Format email tidak valid (contoh: email@kamu.com)!', 'rose');
  input.value = '';
  toast('Berhasil! Cek email kamu ya!', 'green');
}
