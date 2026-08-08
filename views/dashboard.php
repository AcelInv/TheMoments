  <div class="screen" id="dashScreen">
    <div class="dashboard">
      <div class="cart-overlay" id="dashOverlay" onclick="toggleDashSidebar()"></div>
      <div class="dash-layout">

        <!-- Sidebar -->
        <aside class="dash-sidebar" id="dashSidebar">
          <div class="dash-user-card">
            <div class="dash-avatar" id="dashAvatar">U</div>
            <div class="dash-uname" id="dashUname">User</div>
            <div class="dash-uemail" id="dashUemail">user@email.com</div>
            <span class="dash-role" id="dashRole">User</span>
          </div>
          <!-- Navigasi Pengguna -->
          <div id="userNav">
            <button class="dash-nav-btn on" onclick="showPanel('overview')"><span class="ic"></span> Ringkasan</button>
            <button class="dash-nav-btn" onclick="showPanel('orders')"><span class="ic"></span> Riwayat Pesanan</button>
            <button class="dash-nav-btn" onclick="showPanel('wishlist')"><span class="ic"></span> Wishlist</button>
            <button class="dash-nav-btn" onclick="showPanel('profile')"><span class="ic"></span> Profil Saya</button>
          </div>
          <!-- Navigasi Admin -->
          <div id="adminNav" style="display:none">
            <button class="dash-nav-btn on" onclick="showPanel('adminOverview')"><span class="ic"></span> Dashboard</button>
            <button class="dash-nav-btn" onclick="showPanel('adminOrders')"><span class="ic"></span> Kelola Pesanan</button>
            <button class="dash-nav-btn" onclick="showPanel('adminProducts')"><span class="ic"></span> Kelola Produk</button>
            <button class="dash-nav-btn" onclick="showPanel('adminUsers')"><span class="ic"></span> Kelola Pengguna</button>
            <button class="dash-nav-btn" onclick="showPanel('adminReports')"><span class="ic"></span> Laporan</button>
            <button class="dash-nav-btn" onclick="showPanel('profile')"><span class="ic"></span> Profil</button>
          </div>
          <div class="dash-nav-footer">
            <button class="dash-nav-btn" onclick="goHome()"><span class="ic"></span> Ke Toko</button>
            <button class="dash-nav-btn dash-logout-btn" onclick="logout()"><span class="ic"></span> Keluar</button>
          </div>
        </aside>

        <!-- Panel Utama -->
        <main class="dash-main">
          <!-- Header Mobile -->
          <div class="dash-header-mob">
            <button class="dash-menu-btn" onclick="toggleDashSidebar()">
              <span>☰</span> Menu Dashboard
            </button>
            <div class="dash-logo-mob">Flora<span>tica</span></div>
          </div>

          <!-- Panel: Ringkasan Pengguna -->
          <?php include __DIR__ . '/dashboard/panel_overview.php'; ?>

          <!-- Panel: Riwayat Pesanan Pengguna -->
          <?php include __DIR__ . '/dashboard/panel_orders.php'; ?>

          <!-- Panel: Wishlist -->
          <?php include __DIR__ . '/dashboard/panel_wishlist.php'; ?>

          <!-- Panel: Profil -->
          <?php include __DIR__ . '/dashboard/panel_profil.php'; ?>

          <!-- Panel: Admin Ringkasan -->
          <?php include __DIR__ . '/dashboard/panel_admin_overview.php'; ?>

          <!-- Panel: Admin Kelola Pesanan -->
          <?php include __DIR__ . '/dashboard/panel_admin_orders.php'; ?>

          <!-- Panel: Admin Kelola Produk -->
          <?php include __DIR__ . '/dashboard/panel_admin_products.php'; ?>

          <!-- Panel: Admin Kelola Pengguna -->
          <?php include __DIR__ . '/dashboard/panel_admin_users.php'; ?>

          <!-- Panel: Admin Laporan -->
          <?php include __DIR__ . '/dashboard/panel_admin_reports.php'; ?>

        </main>
      </div>
    </div>
  </div><!-- end dashScreen -->
