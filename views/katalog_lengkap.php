  <div class="screen" id="fullCatalogScreen">
    <div class="section" style="padding-top:100px">
      <div class="sec-header">
        <div>
          <div class="sec-tag">Katalog Lengkap</div>
          <h2 class="sec-title">Koleksi <em>Floratica</em></h2>
          <p class="sec-sub">Temukan berbagai pilihan bunga terbaik untuk setiap momen.</p>
        </div>
        <button class="btn-ghost" onclick="goHome()" style="padding:10px 20px;border-radius:50px">← Kembali ke Beranda</button>
      </div>

      <div class="filter-bar" id="fullFilterBar">
        <button class="fbtn on" onclick="filterProdFull('semua',this)">Semua</button>
        <button class="fbtn" onclick="filterProdFull('mawar',this)">Mawar</button>
        <button class="fbtn" onclick="filterProdFull('tulip',this)">Tulip</button>
        <button class="fbtn" onclick="filterProdFull('buket',this)">Buket</button>
        <button class="fbtn" onclick="filterProdFull('eksotis',this)">Eksotis</button>
        <button class="fbtn" onclick="filterProdFull('tanaman',this)">Tanaman</button>
      </div>

      <div class="prod-grid" id="fullProdGrid"></div>

      <div style="margin-top:60px;text-align:center;padding:40px;background:var(--cream2);border-radius:24px">
        <h3>Belum menemukan yang dicari?</h3>
        <p style="margin:8px 0 20px;color:var(--muted)">Hubungi Florist kami untuk rangkaian custom yang lebih personal.</p>
        <button class="btn-primary" onclick="window.open('https://wa.me/6281250157562', '_blank')"> Chat via WhatsApp</button>
      </div>
    </div>
  </div>
