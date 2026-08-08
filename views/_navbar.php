  <nav class="navbar">
    <a class="nav-logo" onclick="goHome()">The <span>Moments</span></a>
    <ul class="nav-links" id="navLinks">
      <li><a onclick="scrollSection('heroSec')" class="nav-link-item">Home</a></li>
      <li><a onclick="scrollSection('katalog')" class="nav-link-item">Shop</a></li>
      <li><a onclick="scrollSection('cabang')" class="nav-link-item">Cabang</a></li>
    </ul>
    <div class="nav-right">
      <div id="navGuest">
        <button class="btn-login-nav" onclick="openAuthModal()">Masuk</button>
      </div>
      <div id="navUser" style="display:none;align-items:center;gap:10px">
        <button class="cart-nav-btn" onclick="toggleCart()" id="cartNavBtn" style="display:none">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:2px"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg> <span id="cartBadge" class="badge">0</span>
        </button>
        <div class="nav-user-chip" onclick="openDashboard()">
          <div class="nav-avatar" id="navAvatar">U</div>
          <span id="navUserName">User</span>
        </div>
      </div>
      <button class="hamburger" onclick="toggleMob()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <div class="mob-nav" id="mobNav">
    <a onclick="scrollSection('katalog');toggleMob()">Katalog</a>
    <a onclick="scrollSection('cabang');toggleMob()">Cabang</a>
    <button id="mobLoginBtn" onclick="openAuthModal();toggleMob()">Masuk</button>
    <button id="mobDashBtn" style="display:none" onclick="openDashboard();toggleMob()">Dashboard</button>
  </div>
